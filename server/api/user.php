<?php
require_once '../config.php';

// Verificar el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener perfil del usuario
        getProfile();
        break;
        
    default:
        sendJsonResponse(['error' => 'Método no permitido'], 405);
        break;
}

function getProfile() {
    global $pdo;
    
    // Verificar autenticación
    $userId = checkAuth();
    
    try {
        // Consultar información del usuario
        $stmt = $pdo->prepare("
            SELECT 
                id,
                username,
                email,
                full_name,
                phone,
                profile_photo,
                language,
                country,
                created_at,
                updated_at
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Si existe una foto de perfil, construir la URL completa
            if ($user['profile_photo']) {
                $user['profile_photo'] = '/uploads/profile_photos/' . basename($user['profile_photo']);
            }
            
            // Remover datos sensibles
            unset($user['password_hash']);
            
            sendJsonResponse([
                'success' => true,
                'user' => $user
            ]);
        } else {
            sendJsonResponse([
                'error' => 'Usuario no encontrado'
            ], 404);
        }
        
    } catch (PDOException $e) {
        error_log("Error en getProfile: " . $e->getMessage());
        sendJsonResponse([
            'error' => 'Error al obtener el perfil'
        ], 500);
    }
}

// Función para validar los datos del perfil
function validateProfileData($data) {
    $errors = [];
    
    // Validar nombre completo
    if (isset($data['full_name']) && strlen($data['full_name']) > 255) {
        $errors[] = 'El nombre completo no puede exceder los 255 caracteres';
    }
    
    // Validar teléfono
    if (isset($data['phone'])) {
        if (!preg_match('/^[+]?[\d\s-]{8,20}$/', $data['phone'])) {
            $errors[] = 'Formato de teléfono inválido';
        }
    }
    
    // Validar idioma
    if (isset($data['language'])) {
        $validLanguages = ['es', 'en', 'pt', 'fr'];
        if (!in_array($data['language'], $validLanguages)) {
            $errors[] = 'Idioma no válido';
        }
    }
    
    // Validar país
    if (isset($data['country'])) {
        if (!preg_match('/^[A-Z]{2}$/', $data['country'])) {
            $errors[] = 'Código de país inválido';
        }
    }
    
    return $errors;
}

// Función para procesar la imagen del perfil
function processProfileImage($file) {
    try {
        validateImage($file);
        
        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $filepath = PROFILE_PHOTOS_DIR . '/' . $filename;
        
        // Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new RuntimeException('Error al guardar la imagen.');
        }
        
        return $filename;
        
    } catch (RuntimeException $e) {
        error_log("Error procesando imagen: " . $e->getMessage());
        throw $e;
    }
}