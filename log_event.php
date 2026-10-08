<?php
// Log event endpoint
include 'logger.php';

$type = $_GET['type'] ?? '';
$action = $_GET['action'] ?? '';
$user = $_GET['user'] ?? '';
$lesson = $_GET['lesson'] ?? '';

if ($type && $action) {
    logEvent($type, ['user' => $user, 'action' => $action, 'lesson' => $lesson]);
    echo json_encode(['status' => 'logged']);
} else {
    echo json_encode(['status' => 'error']);
}
?>