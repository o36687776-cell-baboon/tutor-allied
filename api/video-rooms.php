<?php
/**
 * Video Rooms API
 * Handles room creation, management, and participant operations
 */

require_once '../db.php';
require_once '../pi_config.php';

// Set JSON response header
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get user from session
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGet() {
    global $user_id;

    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            getUserRooms();
            break;
        case 'participants':
            getRoomParticipants();
            break;
        case 'details':
            getRoomDetails();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePost() {
    global $user_id;

    $action = $_POST['action'] ?? $_GET['action'] ?? null;

    switch ($action) {
        case 'create':
            createRoom();
            break;
        case 'join':
            joinRoom();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePut() {
    global $user_id;

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? null;

    switch ($action) {
        case 'update':
            updateRoom();
            break;
        case 'start':
            startRoom();
            break;
        case 'end':
            endRoom();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDelete() {
    global $user_id;

    $room_id = $_GET['room_id'] ?? null;

    if (!$room_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Room ID required']);
        return;
    }

    // Check if user owns the room
    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND tutor_id = ?", [$room_id, $user_id]);

    if (!$room) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }

    // Delete room (cascade will handle related records)
    executeUpdate("DELETE FROM video_rooms WHERE id = ?", [$room['id']]);

    echo json_encode(['success' => true, 'message' => 'Room deleted']);
}

function createRoom() {
    global $user_id;

    $title = $_POST['title'] ?? 'Untitled Room';
    $max_participants = (int)($_POST['max_participants'] ?? 50);
    $start_at = $_POST['start_at'] ?? null;
    $description = $_POST['description'] ?? '';

    // Generate unique room UUID
    $uuid = generateUUID();

    // Insert room
    $sql = "INSERT INTO video_rooms (uuid, title, tutor_id, max_participants, start_at, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'scheduled', NOW())";

    $params = [$uuid, $title, $user_id, $max_participants, $start_at];

    if (executeUpdate($sql, $params)) {
        $room_id = getLastInsertId();

        echo json_encode([
            'success' => true,
            'room' => [
                'id' => $room_id,
                'uuid' => $uuid,
                'title' => $title,
                'tutor_id' => $user_id,
                'max_participants' => $max_participants,
                'start_at' => $start_at,
                'status' => 'scheduled'
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create room']);
    }
}

function joinRoom() {
    global $user_id;

    $room_uuid = $_POST['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    // Check if room exists and is active
    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND status = 'active'", [$room_uuid]);

    if (!$room) {
        http_response_code(404);
        echo json_encode(['error' => 'Room not found or not active']);
        return;
    }

    // Check if room is full
    $participant_count = fetchOne("SELECT COUNT(*) as count FROM room_participants WHERE room_id = ? AND left_at IS NULL", [$room['id']])['count'];

    if ($participant_count >= $room['max_participants']) {
        http_response_code(403);
        echo json_encode(['error' => 'Room is full']);
        return;
    }

    // Add participant (ON DUPLICATE KEY will handle rejoining)
    executeUpdate(
        "INSERT INTO room_participants (room_id, user_id, joined_at) VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE joined_at = NOW(), left_at = NULL",
        [$room['id'], $user_id]
    );

    echo json_encode([
        'success' => true,
        'room' => [
            'uuid' => $room['uuid'],
            'title' => $room['title'],
            'tutor_id' => $room['tutor_id'],
            'max_participants' => $room['max_participants']
        ]
    ]);
}

function getUserRooms() {
    global $user_id;

    // Get rooms where user is tutor
    $tutor_rooms = fetchAll(
        "SELECT * FROM video_rooms WHERE tutor_id = ? ORDER BY created_at DESC",
        [$user_id]
    );

    // Get rooms where user is participant
    $participant_rooms = fetchAll(
        "SELECT vr.* FROM video_rooms vr
         JOIN room_participants rp ON vr.id = rp.room_id
         WHERE rp.user_id = ? AND vr.tutor_id != ?
         ORDER BY vr.created_at DESC",
        [$user_id, $user_id]
    );

    echo json_encode([
        'tutor_rooms' => $tutor_rooms,
        'participant_rooms' => $participant_rooms
    ]);
}

function getRoomParticipants() {
    $room_uuid = $_GET['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    $room = fetchOne("SELECT id FROM video_rooms WHERE uuid = ?", [$room_uuid]);

    if (!$room) {
        http_response_code(404);
        echo json_encode(['error' => 'Room not found']);
        return;
    }

    $participants = fetchAll(
        "SELECT u.username, rp.joined_at, rp.left_at, rp.is_muted, rp.is_video_enabled
         FROM room_participants rp
         JOIN users u ON rp.user_id = u.id
         WHERE rp.room_id = ? AND rp.left_at IS NULL
         ORDER BY rp.joined_at ASC",
        [$room['id']]
    );

    echo json_encode(['participants' => $participants]);
}

function getRoomDetails() {
    $room_uuid = $_GET['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ?", [$room_uuid]);

    if (!$room) {
        http_response_code(404);
        echo json_encode(['error' => 'Room not found']);
        return;
    }

    echo json_encode(['room' => $room]);
}

function updateRoom() {
    global $user_id;

    $input = json_decode(file_get_contents('php://input'), true);
    $room_uuid = $input['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    // Check ownership
    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND tutor_id = ?", [$room_uuid, $user_id]);

    if (!$room) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }

    // Update fields
    $updates = [];
    $params = [];

    if (isset($input['title'])) {
        $updates[] = "title = ?";
        $params[] = $input['title'];
    }

    if (isset($input['max_participants'])) {
        $updates[] = "max_participants = ?";
        $params[] = (int)$input['max_participants'];
    }

    if (isset($input['start_at'])) {
        $updates[] = "start_at = ?";
        $params[] = $input['start_at'];
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid updates provided']);
        return;
    }

    $params[] = $room['id'];
    $sql = "UPDATE video_rooms SET " . implode(', ', $updates) . " WHERE id = ?";

    if (executeUpdate($sql, $params)) {
        echo json_encode(['success' => true, 'message' => 'Room updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update room']);
    }
}

function startRoom() {
    global $user_id;

    $input = json_decode(file_get_contents('php://input'), true);
    $room_uuid = $input['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    // Check ownership
    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND tutor_id = ?", [$room_uuid, $user_id]);

    if (!$room) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }

    // Update status to active
    executeUpdate("UPDATE video_rooms SET status = 'active' WHERE id = ?", [$room['id']]);

    echo json_encode(['success' => true, 'message' => 'Room started']);
}

function endRoom() {
    global $user_id;

    $input = json_decode(file_get_contents('php://input'), true);
    $room_uuid = $input['room_uuid'] ?? null;

    if (!$room_uuid) {
        http_response_code(400);
        echo json_encode(['error' => 'Room UUID required']);
        return;
    }

    // Check ownership
    $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND tutor_id = ?", [$room_uuid, $user_id]);

    if (!$room) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }

    // Update status to ended
    executeUpdate("UPDATE video_rooms SET status = 'ended', end_at = NOW() WHERE id = ?", [$room['id']]);

    echo json_encode(['success' => true, 'message' => 'Room ended']);
}

function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>