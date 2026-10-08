<?php
// API for engagement tracking in AI-ACADEMY
header('Content-Type: application/json');
session_start();
include '../db.php';

// Public API - No authentication required

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $points = (int)($_POST['points'] ?? 0);

    if ($action && $points > 0) {
        // Mock engagement logging - no database
        // $stmt = $pdo->prepare("INSERT INTO engagement_logs (user_id, action_type, points) VALUES (?, ?, ?)");
        // $stmt->execute([$_SESSION['user_id'], $action, $points]);

        // Update user score
        // $stmt = $pdo->prepare("UPDATE users SET engagement_score = engagement_score + ? WHERE id = ?");
        // $stmt->execute([$points, $_SESSION['user_id']]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Invalid data']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>