<?php
// API for notifications in AI-ACADEMY
header('Content-Type: application/json');
session_start();

// Public API - No authentication required

// Mock notifications count
$notifications = [
    'count' => rand(0, 10),
    'messages' => [
        'New message from tutor',
        'Task completed',
        'Badge earned'
    ]
];

echo json_encode($notifications);
?>