<?php
require_once '../config.php';

// Habilitar CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Si es una solicitud OPTIONS, terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar token de autenticación
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Validar token y obtener user_id
try {
    $payload = validateJWT($token);
    if (!$payload) {
        throw new Exception('Token inválido');
    }
    $userId = $payload->user_id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// Obtener el tipo de acción
$action = $_GET['action'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($action === 'events') {
            getEvents($userId);
        } elseif ($action === 'tags') {
            getTags($userId);
        }
        break;
    case 'POST':
        if ($action === 'event') {
            createEvent($userId);
        } elseif ($action === 'tag') {
            createTag($userId);
        }
        break;
    case 'PUT':
        if ($action === 'event') {
            updateEvent($userId);
        }
        break;
    case 'DELETE':
        if ($action === 'event') {
            deleteEvent($userId);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

function getEvents($userId) {
    global $pdo;
    
    try {
        // Obtener parámetros de filtro
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $view = $_GET['view'] ?? 'month'; // month, week, day
        
        $query = "
            SELECT e.*, GROUP_CONCAT(t.name) as tags
            FROM events e
            LEFT JOIN event_tags et ON e.id = et.event_id
            LEFT JOIN tags t ON et.tag_id = t.id
            WHERE e.user_id = :user_id
        ";
        
        $params = [':user_id' => $userId];
        
        if ($startDate && $endDate) {
            $query .= " AND e.start_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }
        
        $query .= " GROUP BY e.id ORDER BY e.start_date ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener recordatorios para cada evento
        foreach ($events as &$event) {
            $stmt = $pdo->prepare("
                SELECT * FROM reminders 
                WHERE event_id = :event_id AND status = 'pending'
            ");
            $stmt->execute([':event_id' => $event['id']]);
            $event['reminders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir tags de string a array
            $event['tags'] = $event['tags'] ? explode(',', $event['tags']) : [];
        }
        
        echo json_encode(['success' => true, 'events' => $events]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener eventos']);
    }
}

function createEvent($userId) {
    global $pdo;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (!isset($data['title']) || !isset($data['start_date']) || !isset($data['end_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // Insertar evento
        $stmt = $pdo->prepare("
            INSERT INTO events (
                user_id, title, description, start_date, end_date,
                location, event_type, priority, color
            ) VALUES (
                :user_id, :title, :description, :start_date, :end_date,
                :location, :event_type, :priority, :color
            )
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':location' => $data['location'] ?? null,
            ':event_type' => $data['event_type'] ?? 'appointment',
            ':priority' => $data['priority'] ?? 'medium',
            ':color' => $data['color'] ?? '#3498db'
        ]);
        
        $eventId = $pdo->lastInsertId();
        
        // Insertar recordatorios si existen
        if (isset($data['reminders']) && is_array($data['reminders'])) {
            $stmt = $pdo->prepare("
                INSERT INTO reminders (
                    event_id, reminder_time, reminder_type
                ) VALUES (
                    :event_id, :reminder_time, :reminder_type
                )
            ");
            
            foreach ($data['reminders'] as $reminder) {
                $stmt->execute([
                    ':event_id' => $eventId,
                    ':reminder_time' => $reminder['reminder_time'],
                    ':reminder_type' => $reminder['reminder_type'] ?? 'push'
                ]);
            }
        }
        
        // Insertar tags si existen
        if (isset($data['tags']) && is_array($data['tags'])) {
            $stmt = $pdo->prepare("
                INSERT INTO event_tags (event_id, tag_id)
                VALUES (:event_id, :tag_id)
            ");
            
            foreach ($data['tags'] as $tagId) {
                $stmt->execute([
                    ':event_id' => $eventId,
                    ':tag_id' => $tagId
                ]);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Evento creado exitosamente',
            'event_id' => $eventId
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el evento']);
    }
}

function updateEvent($userId) {
    global $pdo;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $eventId = $_GET['id'] ?? null;
        
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de evento no proporcionado']);
            return;
        }
        
        // Verificar que el evento pertenece al usuario
        $stmt = $pdo->prepare("SELECT id FROM events WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $eventId, ':user_id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para modificar este evento']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // Actualizar evento
        $stmt = $pdo->prepare("
            UPDATE events SET
                title = :title,
                description = :description,
                start_date = :start_date,
                end_date = :end_date,
                location = :location,
                event_type = :event_type,
                priority = :priority,
                color = :color,
                status = :status
            WHERE id = :id AND user_id = :user_id
        ");
        
        $stmt->execute([
            ':id' => $eventId,
            ':user_id' => $userId,
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':location' => $data['location'] ?? null,
            ':event_type' => $data['event_type'] ?? 'appointment',
            ':priority' => $data['priority'] ?? 'medium',
            ':color' => $data['color'] ?? '#3498db',
            ':status' => $data['status'] ?? 'pending'
        ]);
        
        // Actualizar recordatorios
        if (isset($data['reminders'])) {
            // Eliminar recordatorios existentes
            $stmt = $pdo->prepare("DELETE FROM reminders WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $eventId]);
            
            // Insertar nuevos recordatorios
            $stmt = $pdo->prepare("
                INSERT INTO reminders (
                    event_id, reminder_time, reminder_type
                ) VALUES (
                    :event_id, :reminder_time, :reminder_type
                )
            ");
            
            foreach ($data['reminders'] as $reminder) {
                $stmt->execute([
                    ':event_id' => $eventId,
                    ':reminder_time' => $reminder['reminder_time'],
                    ':reminder_type' => $reminder['reminder_type'] ?? 'push'
                ]);
            }
        }
        
        // Actualizar tags
        if (isset($data['tags'])) {
            // Eliminar tags existentes
            $stmt = $pdo->prepare("DELETE FROM event_tags WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $eventId]);
            
            // Insertar nuevos tags
            if (!empty($data['tags'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO event_tags (event_id, tag_id)
                    VALUES (:event_id, :tag_id)
                ");
                
                foreach ($data['tags'] as $tagId) {
                    $stmt->execute([
                        ':event_id' => $eventId,
                        ':tag_id' => $tagId
                    ]);
                }
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Evento actualizado exitosamente'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el evento']);
    }
}

function deleteEvent($userId) {
    global $pdo;
    
    try {
        $eventId = $_GET['id'] ?? null;
        
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de evento no proporcionado']);
            return;
        }
        
        // Verificar que el evento pertenece al usuario
        $stmt = $pdo->prepare("SELECT id FROM events WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $eventId, ':user_id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para eliminar este evento']);
            return;
        }
        
        // Eliminar evento (las eliminaciones en cascada se manejarán automáticamente)
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $eventId, ':user_id' => $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Evento eliminado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el evento']);
    }
}

function getTags($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM tags WHERE user_id = :user_id ORDER BY name");
        $stmt->execute([':user_id' => $userId]);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'tags' => $tags]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener las etiquetas']);
    }
}

function createTag($userId) {
    global $pdo;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre de etiqueta requerido']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO tags (name, color, user_id)
            VALUES (:name, :color, :user_id)
        ");
        
        $stmt->execute([
            ':name' => $data['name'],
            ':color' => $data['color'] ?? '#95a5a6',
            ':user_id' => $userId
        ]);
        
        $tagId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Etiqueta creada exitosamente',
            'tag_id' => $tagId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear la etiqueta']);
    }
}
?>