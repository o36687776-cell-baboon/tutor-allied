<?php
// Shared header for AI-ACADEMY LMS
// Simple navigation for all users

// Load production configuration FIRST
require_once __DIR__ . '/production_config.php';

// Fallback definition in case production_config.php fails to load
if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', getenv('APP_ENV') === 'production' || isset($_SERVER['HTTPS']));
}

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

// Start session if not already started - Fix session path for XAMPP
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}

// Only set session save path if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($session_path);
    session_start();
} elseif (!isset($_SESSION)) {
    // If session is already active but $_SESSION is not set, start it anyway
    session_start();
}

// Include secure session management
include_once __DIR__ . '/pi_config.php';
startSecureSession();

// Include role handler for session management and redirects
include 'role_handler.php';

// Admin access restriction: If admin is authenticated, restrict to admin pages only
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    $current_path = $_SERVER['REQUEST_URI'];
    if (strpos($current_path, BASE_PATH . '/admin/') === false && strpos($current_path, BASE_PATH . '/auth/admin') === false && strpos($current_path, BASE_PATH . '/logout.php') === false) {
        header('Location: ' . BASE_PATH . '/admin/');
        exit;
    }
}

// Demo mode handling
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;

// Check if demo mode has expired
if ($is_demo_mode && isset($_SESSION['demo_expires']) && time() > $_SESSION['demo_expires']) {
    // Demo expired, clear session
    session_destroy();
    session_start();
    $is_demo_mode = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#6f42c1">
    <meta name="msapplication-TileColor" content="#6f42c1">
    <title><?php echo isset($page_title) ? $page_title . ' - AI-ACADEMY' : 'AI-ACADEMY - Learn AI, Software Dev & Blockchain'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/global.css" rel="stylesheet">
    <?php if (isset($extra_css)) echo "<link href='assets/$extra_css' rel='stylesheet'>"; ?>

    <!-- Pi Network SDK -->
    <script src="https://sdk.minepi.com/pi-sdk.js"></script>
    <script>
        // Initialize Pi SDK with proper error handling
        try {
            Pi.init({
                version: "2.0",
                sandbox: <?php echo IS_PRODUCTION ? 'false' : 'true'; ?>
            });
            console.log('Pi SDK initialized successfully');
        } catch (error) {
            console.error('Pi SDK initialization failed:', error);
        }
    </script>
</head>
<body>
    <!-- Mobile-Optimized Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_PATH; ?>/index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                <span class="d-inline d-md-none">AI-ACADEMY</span>
                <span class="d-none d-md-inline">AI-ACADEMY</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn d-lg-none" type="button" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>/about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>/legal/privacy.php">
                            <i class="fas fa-shield-alt me-1"></i>Privacy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/learnershub/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/learnershub/'; ?>">
                            <i class="fas fa-graduation-cap me-1"></i>Learn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/tutor/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/tutor/'; ?>">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Teach
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/library/catalogue.php' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/library/catalogue.php'; ?>">
                            <i class="fas fa-book me-1"></i>Library
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/forums/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/forums/'; ?>">
                            <i class="fas fa-comments me-1"></i>Forums
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/rating-badge/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/rating-badge/'; ?>">
                            <i class="fas fa-star me-1"></i>Ratings
                        </a>
                    </li>
                    <li class="nav-item dropdown d-lg-none">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-h me-1"></i>More
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/forums/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/forums/'; ?>">
                                <i class="fas fa-comments me-2"></i>Forums
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/blog-catalogue/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/blog-catalogue/'; ?>">
                                <i class="fas fa-blog me-2"></i>Blogs
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/rating-badge/' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/rating-badge/'; ?>">
                                <i class="fas fa-star me-2"></i>Ratings
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? BASE_PATH . '/messages.php' : BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/messages.php'; ?>">
                                <i class="fas fa-envelope me-2"></i>Messages
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-crown me-1"></i>Premium
                        </a>
                        <div class="dropdown-menu p-0" style="min-width: 600px; max-width: 90vw;">
                            <div class="p-3">
                                <h6 class="dropdown-header text-center mb-3">
                                    <i class="fas fa-crown text-warning me-2"></i>
                                    Choose Your Premium Plan
                                </h6>

                                <!-- Premium Tiers Cards -->
                                <div class="row g-2 mb-3">
                                    <!-- Free Tier -->
                                    <div class="col-md-4">
                                        <div class="card h-100 border-secondary">
                                            <div class="card-header text-center bg-light">
                                                <h6 class="card-title mb-1">Free</h6>
                                                <div class="h4 text-secondary mb-0">0 π</div>
                                                <small class="text-muted">Forever</small>
                                            </div>
                                            <div class="card-body p-2">
                                                <ul class="list-unstyled small mb-2">
                                                    <li><i class="fas fa-check text-success me-1"></i>Basic learning</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Community reviews</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Blog reading</li>
                                                    <li><i class="fas fa-times text-muted me-1"></i>Certifications</li>
                                                </ul>
                                            </div>
                                            <div class="card-footer p-2 text-center">
                                                <span class="badge bg-secondary">Current</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Premium Basic -->
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-header text-center bg-warning text-dark">
                                                <h6 class="card-title mb-1">Premium Basic</h6>
                                                <div class="h4 text-warning mb-0">50 π</div>
                                                <small class="text-muted">per month</small>
                                            </div>
                                            <div class="card-body p-2">
                                                <ul class="list-unstyled small mb-2">
                                                    <li><i class="fas fa-check text-success me-1"></i>Everything in Free</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Certifications</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Personalized learning</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Priority support</li>
                                                </ul>
                                            </div>
                                            <div class="card-footer p-2 text-center">
                                                <button class="btn btn-warning btn-sm w-100" onclick="selectPremiumTier('premium_basic')">
                                                    <i class="fas fa-star me-1"></i>Choose Basic
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Premium Pro -->
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-header text-center bg-danger text-white">
                                                <h6 class="card-title mb-1">Premium Pro</h6>
                                                <div class="h4 text-danger mb-0">150 π</div>
                                                <small class="text-muted">per month</small>
                                            </div>
                                            <div class="card-body p-2">
                                                <ul class="list-unstyled small mb-2">
                                                    <li><i class="fas fa-check text-success me-1"></i>Everything in Basic</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>App development</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>Monetization</li>
                                                    <li><i class="fas fa-check text-success me-1"></i>API access</li>
                                                </ul>
                                            </div>
                                            <div class="card-footer p-2 text-center">
                                                <button class="btn btn-danger btn-sm w-100" onclick="selectPremiumTier('premium_pro')">
                                                    <i class="fas fa-crown me-1"></i>Choose Pro
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?php echo BASE_PATH; ?>/premium.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-info-circle me-1"></i>View Details
                                    </a>
                                    <div>
                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
                                            <a href="<?php echo BASE_PATH; ?>/verify-wallet.php" class="btn btn-warning btn-sm me-2">
                                                <i class="fas fa-shield-alt me-1"></i>Verify Wallet
                                            </a>
                                            <?php if (isset($_SESSION['wallet_verified']) && $_SESSION['wallet_verified']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Wallet Verified
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_PATH; ?>/auth/" class="btn btn-primary btn-sm">
                                                <i class="fas fa-sign-in-alt me-1"></i>Login to Subscribe
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <!-- Right Side Navigation -->
                <ul class="navbar-nav">
                    <!-- User Status / Authentication -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Authenticated/Demo User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['pi_username'] ?? 'User'); ?></span>
                                <span class="d-sm-none">Profile</span>
                                <?php if ($is_demo_mode): ?>
                                    <span class="badge bg-warning text-dark ms-1">DEMO</span>
                                <?php elseif (isset($_SESSION['wallet_verified']) && $_SESSION['wallet_verified']): ?>
                                    <span class="badge bg-success ms-1">
                                        <i class="fas fa-shield-alt me-1"></i>Verified
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (!$is_demo_mode): ?>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#roleSwitcherModal">
                                    <i class="fas fa-exchange-alt me-2"></i>Switch Mode
                                </a></li>
                                <?php if (!isset($_SESSION['wallet_verified']) || !$_SESSION['wallet_verified']): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/verify-wallet.php">
                                    <i class="fas fa-shield-alt me-2"></i>Verify Wallet
                                </a></li>
                                <?php endif; ?>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo $is_demo_mode ? 'Exit Demo' : 'Logout'; ?>
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest User Menu - Pi Authentication -->
                        <li class="nav-item">
                            <button class="nav-link btn btn-outline-light btn-sm px-3 me-2" id="navAuthBtn">
                                <i class="fab fa-pied-piper me-1"></i>
                                <span class="d-none d-sm-inline">Authenticate</span>
                                <span class="d-sm-none">Auth</span>
                            </button>
                        </li>
                    <?php endif; ?>


                    <!-- Admin Dashboard (Only when authenticated) -->
                    <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            <span class="d-none d-md-inline">Dashboard</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/logs.php">
                                <i class="fas fa-history me-2"></i>Activity Logs
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin_setup.php">
                                <i class="fas fa-key me-2"></i>Access Codes
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
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

    <!-- Demo Mode Notification -->
    <?php if ($is_demo_mode): ?>
    <div class="alert alert-success alert-dismissible fade show m-0 border-0 rounded-0" role="alert">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-play-circle me-2"></i>
                    <strong>🎮 Demo Mode Active</strong> - You're exploring AI-ACADEMY in demo mode!
                    <span class="ms-3">
                        <i class="fas fa-clock me-1"></i>
                        Expires: <span id="demoTimer"><?php echo gmdate("H:i:s", $_SESSION['demo_expires'] - time()); ?></span>
                    </span>
                </div>
                <div>
                    <a href="<?php echo BASE_PATH; ?>/auth/" class="btn btn-outline-success btn-sm me-2">
                        <i class="fab fa-pied-piper me-1"></i>Connect Pi Wallet
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Role Switcher -->
    <?php include 'role_switcher.php'; ?>

    <!-- Mobile Menu Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navbarCollapse = document.querySelector('.navbar-collapse');

        if (mobileMenuBtn && navbarCollapse) {
            mobileMenuBtn.addEventListener('click', function() {
                navbarCollapse.classList.toggle('show');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuBtn.contains(e.target) && !navbarCollapse.contains(e.target)) {
                    navbarCollapse.classList.remove('show');
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });

            // Close mobile menu when clicking on a nav link
            navbarCollapse.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) { // Only on mobile/tablet
                        navbarCollapse.classList.remove('show');
                        const icon = mobileMenuBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                });
            });
        }
    });

    // Pi Network Authentication from Navigation
    document.addEventListener('DOMContentLoaded', function() {
        const navAuthBtn = document.getElementById('navAuthBtn');

        if (navAuthBtn) {
            navAuthBtn.addEventListener('click', function() {
                // Check if Pi SDK is available and properly initialized
                if (typeof Pi === 'undefined') {
                    alert('Pi Network authentication requires the Pi Browser. Please download and install the Pi Browser to continue.');
                    window.open('https://minepi.com/browser', '_blank');
                    return;
                }

                // Additional check for Pi SDK initialization
                if (!Pi || typeof Pi.authenticate !== 'function') {
                    alert('Pi SDK is not properly loaded. Please refresh the page and try again.');
                    return;
                }

                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Authenticating...';
                this.disabled = true;

                // Pi Network Authentication
                const scopes = ['payments', 'username'];

                function onIncompletePaymentFound(payment) {
                    console.log('Incomplete payment found:', payment);
                }

                // Test Pi SDK connectivity before authentication
                try {
                    // Simple test to ensure Pi SDK is responsive
                    if (Pi && typeof Pi.authenticate === 'function') {
                        console.log('Pi SDK connectivity test passed');
                    } else {
                        throw new Error('Pi SDK connectivity test failed');
                    }
                } catch (testError) {
                    console.error('Pi SDK connectivity test error:', testError);
                    throw new Error('Pi Network connection test failed. Please check your internet connection and try again.');
                }

                // Check if Pi SDK is properly initialized with additional validation
                if (!Pi || typeof Pi.authenticate !== 'function') {
                    throw new Error('Pi SDK not properly initialized. Please refresh the page and try again.');
                }

                // Verify Pi SDK version and sandbox mode
                console.log('Pi SDK version check:', Pi.version || 'unknown');
                console.log('Sandbox mode:', <?php echo IS_PRODUCTION ? 'false' : 'true'; ?>);

                // Add timeout to prevent hanging (increased to 90 seconds for Pi Network)
                const authPromise = Pi.authenticate(scopes, onIncompletePaymentFound);
                const timeoutPromise = new Promise((_, reject) =>
                    setTimeout(() => reject(new Error('Authentication timeout - please check your internet connection and try again')), 90000)
                );

                Promise.race([authPromise, timeoutPromise]).then(function(auth) {
                    console.log('Pi Authentication successful:', auth);

                    // Show success state briefly
                    navAuthBtn.innerHTML = '<i class="fas fa-check me-1"></i>Success!';
                    navAuthBtn.classList.remove('btn-outline-light');
                    navAuthBtn.classList.add('btn-success');

                    // Redirect to auth page with access token after brief delay
                    const accessToken = auth.accessToken;
                    setTimeout(function() {
                        window.location.href = '<?php echo BASE_PATH; ?>/auth/?access_token=' + accessToken;
                    }, 1000);

                }).catch(function(error) {
                    console.error('Pi Authentication failed:', error);

                    // Reset button
                    navAuthBtn.innerHTML = originalText;
                    navAuthBtn.disabled = false;
                    navAuthBtn.classList.remove('btn-success');
                    navAuthBtn.classList.add('btn-outline-light');

                    let errorMessage = 'Authentication failed. ';
                    if (error && error.message) {
                        if (error.message.includes('timeout')) {
                            errorMessage = 'Authentication timed out. Please check your internet connection and try again.';
                        } else {
                            errorMessage += error.message;
                        }
                    } else {
                        errorMessage += 'Please try again or contact support.';
                    }
                    alert(errorMessage);
                });
            });
        }
    });

    // Premium Tier Selection Function
    function selectPremiumTier(tier) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            // Redirect to authentication if not logged in
            alert('Please authenticate with Pi Network first.');
            document.getElementById('navAuthBtn')?.click();
            return;
        <?php endif; ?>

        <?php if (!isset($_SESSION['wallet_verified']) || !$_SESSION['wallet_verified']): ?>
            // Redirect to wallet verification if not verified
            alert('You need to verify your Pi wallet first to access premium features.');
            window.location.href = '<?php echo BASE_PATH; ?>/verify-wallet.php';
            return;
        <?php endif; ?>

        // Redirect to premium page with selected tier
        window.location.href = '<?php echo BASE_PATH; ?>/premium.php?tier=' + tier;
    }
    </script>

    <!-- Main Content -->
    <div class="container mt-4">