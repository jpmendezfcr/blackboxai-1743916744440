<?php
require_once '../config.php';

// Habilitar CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Si es una solicitud OPTIONS, terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Obtener la acción solicitada
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'forgot_password':
        handleForgotPassword();
        break;
    case 'reset_password':
        handleResetPassword();
        break;
    case 'verify_reset_token':
        handleVerifyResetToken();
        break;
    case 'check':
        handleCheckAuth();
        break;
    case 'change_password':
        handleChangePassword();
        break;
    default:
        sendJsonResponse(['error' => 'Acción no válida'], 400);
}

function handleLogin() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        sendJsonResponse(['error' => 'Email y contraseña son requeridos'], 400);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email, password_hash, full_name, profile_photo FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Generar token JWT
            $token = generateJWT($user['id']);
            
            // Actualizar último login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Devolver datos del usuario (excluyendo password_hash)
            unset($user['password_hash']);
            sendJsonResponse([
                'success' => true,
                'user' => $user,
                'token' => $token
            ]);
        } else {
            sendJsonResponse(['error' => 'Credenciales inválidas'], 401);
        }
    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al procesar la solicitud'], 500);
    }
}

function handleRegister() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $required = ['email', 'password', 'full_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendJsonResponse(['error' => "El campo $field es requerido"], 400);
            return;
        }
    }

    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = $data['password'];
    $fullName = filter_var($data['full_name'], FILTER_SANITIZE_STRING);

    try {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJsonResponse(['error' => 'El email ya está registrado'], 400);
            return;
        }

        // Insertar nuevo usuario
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, full_name, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$email, $passwordHash, $fullName]);
        
        sendJsonResponse(['success' => true, 'message' => 'Usuario registrado exitosamente']);
    } catch (PDOException $e) {
        error_log("Error en registro: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al procesar el registro'], 500);
    }
}

function handleLogout() {
    // En una implementación con JWT, el cliente debe eliminar el token
    sendJsonResponse(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
}

function handleForgotPassword() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (!$email) {
        sendJsonResponse(['error' => 'Email es requerido'], 400);
        return;
    }

    try {
        // Verificar si el usuario existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            sendJsonResponse(['error' => 'Email no encontrado'], 404);
            return;
        }

        // Generar token de recuperación
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("
            INSERT INTO password_resets (email, token, expiry)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$email, $token, $expiry]);

        // Aquí se enviaría el email con el token
        // Por ahora solo simulamos el envío
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Instrucciones enviadas al email'
        ]);
    } catch (PDOException $e) {
        error_log("Error en forgot password: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al procesar la solicitud'], 500);
    }
}

function handleResetPassword() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';
    $newPassword = $data['new_password'] ?? '';

    if (!$token || !$newPassword) {
        sendJsonResponse(['error' => 'Token y nueva contraseña son requeridos'], 400);
        return;
    }

    try {
        // Verificar token válido y no expirado
        $stmt = $pdo->prepare("
            SELECT email FROM password_resets 
            WHERE token = ? AND expiry > NOW() AND used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            sendJsonResponse(['error' => 'Token inválido o expirado'], 400);
            return;
        }

        // Actualizar contraseña
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$passwordHash, $reset['email']]);

        // Marcar token como usado
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    } catch (PDOException $e) {
        error_log("Error en reset password: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al restablecer la contraseña'], 500);
    }
}

function handleVerifyResetToken() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendJsonResponse(['error' => 'Token es requerido'], 400);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id FROM password_resets 
            WHERE token = ? AND expiry > NOW() AND used = 0
        ");
        $stmt->execute([$token]);
        
        sendJsonResponse([
            'success' => true,
            'valid' => (bool)$stmt->fetch()
        ]);
    } catch (PDOException $e) {
        error_log("Error en verify token: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al verificar el token'], 500);
    }
}

function handleCheckAuth() {
    $token = getBearerToken();
    if (!$token) {
        sendJsonResponse(['error' => 'No autorizado'], 401);
        return;
    }

    try {
        $payload = validateJWT($token);
        if ($payload) {
            sendJsonResponse(['success' => true, 'user_id' => $payload->user_id]);
        } else {
            sendJsonResponse(['error' => 'Token inválido'], 401);
        }
    } catch (Exception $e) {
        sendJsonResponse(['error' => 'Error al verificar autenticación'], 401);
    }
}

function handleChangePassword() {
    global $pdo;
    
    $token = getBearerToken();
    if (!$token) {
        sendJsonResponse(['error' => 'No autorizado'], 401);
        return;
    }

    $payload = validateJWT($token);
    if (!$payload) {
        sendJsonResponse(['error' => 'Token inválido'], 401);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';

    if (!$currentPassword || !$newPassword) {
        sendJsonResponse(['error' => 'Contraseña actual y nueva son requeridas'], 400);
        return;
    }

    try {
        // Verificar contraseña actual
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$payload->user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            sendJsonResponse(['error' => 'Contraseña actual incorrecta'], 400);
            return;
        }

        // Actualizar contraseña
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $payload->user_id]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    } catch (PDOException $e) {
        error_log("Error en change password: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error al cambiar la contraseña'], 500);
    }
}

// Funciones auxiliares

function generateJWT($userId) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $userId,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 horas
    ]);

    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $secret = 'your-256-bit-secret';
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

function validateJWT($token) {
    $tokenParts = explode('.', $token);
    if (count($tokenParts) != 3) {
        return false;
    }

    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];

    $secret = 'your-256-bit-secret';
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    if ($base64Signature === $signatureProvided) {
        $payload = json_decode($payload);
        if ($payload->exp > time()) {
            return $payload;
        }
    }
    return false;
}

function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

?>