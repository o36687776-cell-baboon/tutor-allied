<?php
// Role Handler - Process role switching logic
// This file should be included at the very beginning of pages that need role switching

// Dynamic base path detection for different deployment environments
if (!defined('BASE_PATH')) {
    // Get the directory of the current script
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    // Get the parent directory (deployment subdirectory)
    $base_path = dirname($script_dir);

    // If at root level, use empty string; otherwise use the subdirectory path
    if ($base_path === '/' || $base_path === '\\' || $base_path === '.') {
        $base_path = '';
    }

    define('BASE_PATH', $base_path);
}

if (!isset($_SESSION)) {
    session_start();
}

$current_role = $_SESSION['user_role'] ?? 'guest';
$current_mode = $_GET['mode'] ?? $current_role;

// Set session role based on URL parameter (only for authenticated users)
if (isset($_GET['mode']) && isset($_SESSION['user_id'])) {
    if (in_array($_GET['mode'], ['student', 'tutor'])) {
        $_SESSION['user_role'] = $_GET['mode'];
        $current_role = $_GET['mode'];

        // Redirect to appropriate dashboard
        if ($current_role === 'student') {
            header('Location: ' . BASE_PATH . '/learnershub/');
            exit;
        } elseif ($current_role === 'tutor') {
            header('Location: ' . BASE_PATH . '/tutor/');
            exit;
        }
    } elseif ($_GET['mode'] === 'admin') {
        // Prevent switching to admin mode
        header('Location: ' . BASE_PATH . '/?error=admin_access_denied');
        exit;
    }
}
?>