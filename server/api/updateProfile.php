<?php
require_once '../config.php';
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];
$response = ['success' => false];

try {
    // Validar y sanitizar los datos recibidos
    $data = [];
    
    if (isset($_POST['full_name'])) {
        $data['full_name'] = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    }
    
    if (isset($_POST['phone'])) {
        $data['phone'] = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    }
    
    if (isset($_POST['language'])) {
        $data['language'] = filter_var($_POST['language'], FILTER_SANITIZE_STRING);
    }
    
    if (isset($_POST['country'])) {
        $data['country'] = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
    }

    // Manejar la subida de la foto de perfil
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($_FILES['profile_photo']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validar el tipo de archivo
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Use JPG o PNG.');
        }

        // Generar un nombre único para el archivo
        $newFileName = uniqid('profile_') . '.' . $extension;
        $uploadPath = UPLOAD_DIR . '/profile_photos/' . $newFileName;

        // Mover el archivo subido
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
            $data['profile_photo'] = '/uploads/profile_photos/' . $newFileName;
        } else {
            throw new Exception('Error al guardar la imagen.');
        }
    }

    // Preparar la consulta SQL para actualizar el perfil
    if (!empty($data)) {
        $sql = "UPDATE users SET ";
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            // Obtener los datos actualizados del usuario
            $stmt = $pdo->prepare("SELECT id, username, email, full_name, phone, profile_photo, language, country FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response = [
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'user' => $user
            ];
        } else {
            throw new Exception('Error al actualizar el perfil');
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);