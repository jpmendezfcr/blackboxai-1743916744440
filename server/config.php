<?php
// Habilitar reporte de errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'avi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de rutas y directorios
define('BASE_PATH', dirname(__FILE__));
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('PROFILE_PHOTOS_DIR', UPLOAD_DIR . '/profile_photos');

// Asegurar que existan los directorios necesarios
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(PROFILE_PHOTOS_DIR)) {
    mkdir(PROFILE_PHOTOS_DIR, 0755, true);
}

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Conexión a la base de datos usando PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // En producción, loguear el error y mostrar un mensaje genérico
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

// Configuración de CORS para desarrollo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Función helper para respuestas JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Función helper para validar sesión
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(['error' => 'No autorizado'], 401);
    }
    return $_SESSION['user_id'];
}

// Función helper para sanitizar inputs
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función helper para validar imagen
function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Parámetros inválidos.');
    }

    if ($file['size'] > $maxSize) {
        throw new RuntimeException('El archivo excede el tamaño permitido.');
    }

    if (!in_array($file['type'], $allowedTypes)) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }

    return true;
}