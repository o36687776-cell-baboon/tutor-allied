<?php
// Shared header for AI-ACADEMY LMS
// Simple navigation for all users

// Start session if not already started - Fix session path for XAMPP
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}
session_save_path($session_path);

if (!isset($_SESSION)) {
    session_start();
}

// Include role handler for session management and redirects
include 'role_handler.php';

// Admin access restriction: If admin is authenticated, restrict to admin pages only
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    $current_path = $_SERVER['REQUEST_URI'];
    if (strpos($current_path, '/admin/') === false && strpos($current_path, '/auth/admin') === false && strpos($current_path, '/logout.php') === false) {
        header('Location: /admin/');
        exit;
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - AI-ACADEMY' : 'AI-ACADEMY - Learn AI, Software Dev & Blockchain'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/global.css" rel="stylesheet">
    <?php if (isset($extra_css)) echo "<link href='assets/$extra_css' rel='stylesheet'>"; ?>
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">AI-ACADEMY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/legal/privacy.php">Privacy</a></li>
                    <li class="nav-item"><a class="nav-link" href="/learnershub/">Learners Hub</a></li>
                    <li class="nav-item"><a class="nav-link" href="/tutor/">Tutor</a></li>
                    <li class="nav-item"><a class="nav-link" href="/library/catalogue.php">Library</a></li>
                    <li class="nav-item"><a class="nav-link" href="/forums/">Forums</a></li>
                    <li class="nav-item"><a class="nav-link" href="/blog-catalogue/">Blogs</a></li>
                    <li class="nav-item"><a class="nav-link" href="/rating-badge/">Ratings</a></li>
                    <li class="nav-item"><a class="nav-link" href="/messages.php">Messages</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-crown me-1"></i>Premium
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/premium.php">View Plans</a></li>
                            <li><a class="dropdown-item" href="/verify-wallet.php">Verify Wallet</a></li>
                            <?php if (isset($_SESSION['wallet_verified']) && $_SESSION['wallet_verified']): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-success" href="#"><i class="fas fa-check me-2"></i>Wallet Verified</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <!-- Role Switcher Dropdown (Only for authenticated users) -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php
                            $current_role = $_SESSION['user_role'] ?? 'learner';
                            echo ucfirst($current_role);
                            ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#roleSwitcherModal">
                                <i class="fas fa-exchange-alt me-2"></i>Switch Mode
                            </a></li>
                            <li><a class="dropdown-item" href="/pi-logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <!-- Separate Admin Access Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-shield-alt me-1"></i>Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/auth/admin_login.php">
                                <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                            </a></li>
                            <li><a class="dropdown-item" href="/admin_setup.php">
                                <i class="fas fa-cog me-2"></i>Admin Setup
                            </a></li>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/admin/logs.php"><i class="fas fa-history me-2"></i>Activity Logs</a></li>
                            <li><a class="dropdown-item" href="/admin_setup.php"><i class="fas fa-key me-2"></i>Access Codes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/admin_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Session Warning -->
    <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
    <div class="alert alert-warning alert-dismissible fade show m-0 border-0 rounded-0" role="alert">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Admin Mode Active</strong> - You have elevated privileges.
                    Session expires in: <span id="sessionTimer"><?php echo gmdate("H:i:s", $session_timeout - (time() - $_SESSION['admin_login_time'])); ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Role Switcher -->
    <?php include 'role_switcher.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4">