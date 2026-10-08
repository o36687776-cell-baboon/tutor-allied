<?php
/**
 * Unified Logout Script
 * Handles logout for Pi Network users and Admin
 */

session_start();

// Determine user type and handle logout accordingly
$user_type = 'guest';

if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) {
    $user_type = 'admin';
} elseif (isset($_SESSION['user_id'])) {
    $user_type = 'pi'; // All users are Pi Network users now
}

// Handle logout based on user type
switch ($user_type) {
    case 'admin':
        // Admin logout
        include 'logger.php';
        logEvent('admin_logout', ['username' => $_SESSION['admin_username'] ?? 'unknown', 'ip' => $_SERVER['REMOTE_ADDR']]);

        // Clear admin session variables
        unset($_SESSION['admin_authenticated']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_login_time']);

        $message = 'Admin logout successful.';
        $redirect = '/';
        break;

    case 'pi':
        // Pi Network user logout
        // Note: Pi Network doesn't have a logout endpoint, so we clear local session
        // In a real implementation, you might redirect to Pi's logout if available

        // Clear Pi-related session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['auth_type']);
        unset($_SESSION['pi_authenticated']);
        unset($_SESSION['pi_user_id']);
        unset($_SESSION['pi_username']);
        unset($_SESSION['pi_access_token']);
        unset($_SESSION['username']);
        unset($_SESSION['wallet_verified']);

        $message = 'Pi Network logout successful.';
        $redirect = '/';
        break;

    default:
        // Guest user
        $message = 'You are not logged in.';
        $redirect = '/';
        break;
}

// Destroy the session completely
session_destroy();

// Redirect with message
header('Location: ' . $redirect . '?logout=success&message=' . urlencode($message));
exit;
?>