<?php
/**
 * Messages API
 * Handles message operations via AJAX
 */

header('Content-Type: application/json');
include '../pi_config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'send':
        sendMessage();
        break;

    case 'get_conversation':
        getConversation();
        break;

    case 'mark_read':
        markAsRead();
        break;

    case 'delete_conversation':
        deleteConversation();
        break;

    case 'get_users':
        getUsers();
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function sendMessage() {
    global $current_user_id;

    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$receiver_id || empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    // Insert message
    $result = executeUpdate(
        "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)",
        [$current_user_id, $receiver_id, $subject, $content]
    );

    if ($result) {
        echo json_encode(['success' => true, 'message_id' => getLastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
}

function getConversation() {
    global $current_user_id;

    $other_user_id = (int)($_GET['user_id'] ?? 0);

    if (!$other_user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        return;
    }

    // Get messages between current user and other user
    $messages = fetchAll(
        "SELECT m.*, u.username as sender_name
         FROM messages m
         JOIN users u ON m.sender_id = u.id
         WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
         ORDER BY m.created_at ASC",
        [$current_user_id, $other_user_id, $other_user_id, $current_user_id]
    );

    // Mark messages as read
    executeUpdate(
        "UPDATE messages SET read_at = NOW() WHERE receiver_id = ? AND sender_id = ? AND read_at IS NULL",
        [$current_user_id, $other_user_id]
    );

    echo json_encode(['success' => true, 'messages' => $messages]);
}

function markAsRead() {
    global $current_user_id;

    $other_user_id = (int)($_POST['user_id'] ?? 0);

    if (!$other_user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        return;
    }

    $result = executeUpdate(
        "UPDATE messages SET read_at = NOW() WHERE receiver_id = ? AND sender_id = ? AND read_at IS NULL",
        [$current_user_id, $other_user_id]
    );

    echo json_encode(['success' => true, 'updated' => $result]);
}

function deleteConversation() {
    global $current_user_id;

    $other_user_id = (int)($_POST['user_id'] ?? 0);

    if (!$other_user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        return;
    }

    // Delete messages between users (only for current user - they can only delete their own messages)
    $result = executeUpdate(
        "DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)",
        [$current_user_id, $other_user_id, $other_user_id, $current_user_id]
    );

    echo json_encode(['success' => true, 'deleted' => $result]);
}

function getUsers() {
    // Get list of users for messaging (excluding current user)
    global $current_user_id;

    $users = fetchAll(
        "SELECT id, username FROM users WHERE id != ? AND user_role IN ('admin', 'tutor') ORDER BY username",
        [$current_user_id]
    );

    echo json_encode(['success' => true, 'users' => $users]);
}
?>