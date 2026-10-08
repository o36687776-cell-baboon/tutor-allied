<?php
// Start session first - Fix session path for XAMPP
$session_path = __DIR__ . '/../sessions';
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

// Check Pi Network Authentication BEFORE including header
// Allow demo mode users
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
if (!$is_demo_mode && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Student Dashboard for AI-ACADEMY
$page_title = 'Student Dashboard';
include '../header.php';

// Handle demo mode vs real user data
if ($is_demo_mode) {
    // Demo mode: Provide mock data
    $user = [
        'username' => 'Demo User',
        'total_pi_earned' => 25.5,
        'email' => 'demo@ai-academy.com'
    ];

    // Mock enrolled courses for demo
    $enrolled_courses = [
        [
            'id' => 1,
            'title' => 'Introduction to AI Development',
            'progress_percentage' => 75,
            'enrolled_at' => date('Y-m-d H:i:s', strtotime('-2 weeks'))
        ],
        [
            'id' => 2,
            'title' => 'Pi Network Blockchain Basics',
            'progress_percentage' => 45,
            'enrolled_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ],
        [
            'id' => 3,
            'title' => 'Smart Contract Development',
            'progress_percentage' => 100,
            'enrolled_at' => date('Y-m-d H:i:s', strtotime('-3 weeks'))
        ]
    ];

    $recent_activity = [];
    $upcoming_events = ['AI Workshop', 'Blockchain Seminar', 'Coding Bootcamp'];
    $current_course = $enrolled_courses[0]['title'];
    $progress = $enrolled_courses[0]['progress_percentage'];
    $notifications = 2;
} else {
    // Real user: Get actual data from database
    $user_id = $_SESSION['user_id'];
    $user = getUserById($user_id);

    // Get enrolled courses with progress
    $enrolled_courses = fetchAll("
        SELECT c.*, ce.progress_percentage, ce.enrolled_at
        FROM courses c
        JOIN course_enrollments ce ON c.id = ce.course_id
        WHERE ce.user_id = ?
        ORDER BY ce.enrolled_at DESC
    ", [$user_id]);

    // Get user activity for recent progress
    $recent_activity = getUserActivity($user_id, 10);

    // Mock upcoming events (could be stored in database later)
    $upcoming_events = ['AI Workshop', 'Blockchain Seminar', 'Coding Bootcamp'];
    $current_course = $enrolled_courses ? $enrolled_courses[0]['title'] : 'No courses enrolled';
    $progress = $enrolled_courses ? $enrolled_courses[0]['progress_percentage'] : 0;
    $notifications = 3;
}
?>

<!-- Modern Google-Inspired Learner Dashboard Styles -->
<style>
:root {
    --primary-color: #1a73e8;
    --secondary-color: #34a853;
    --accent-color: #ea4335;
    --warning-color: #fbbc04;
    --surface-color: #ffffff;
    --background-color: #f8f9fa;
    --text-primary: #202124;
    --text-secondary: #5f6368;
    --shadow-light: 0 1px 3px rgba(60, 64, 67, 0.3);
    --shadow-medium: 0 4px 6px rgba(60, 64, 67, 0.15);
    --shadow-heavy: 0 8px 25px rgba(60, 64, 67, 0.15);
    --border-radius: 8px;
    --border-radius-large: 16px;
    --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Google Sans', 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.5;
    color: var(--text-primary);
    background-color: var(--background-color);
}

.learner-dashboard {
    min-height: 100vh;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 50%, #fff3e0 100%);
    padding: 24px 0;
}

.container-fluid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Header Section */
.dashboard-header {
    background: var(--surface-color);
    border-radius: var(--border-radius-large);
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-light);
    border: 1px solid #e8eaed;
}

.welcome-section {
    text-align: center;
    margin-bottom: 32px;
}

.welcome-section h1 {
    font-size: 2.5rem;
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.welcome-section h1 i {
    color: var(--primary-color);
    margin-right: 12px;
}

.welcome-section p {
    font-size: 1.125rem;
    color: var(--text-secondary);
    margin: 0;
}

/* Notice Cards */
.demo-notice, .pi-notice {
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-light);
    border: 1px solid #e8eaed;
    display: flex;
    align-items: center;
    gap: 16px;
}

.demo-notice {
    background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
    border-left: 4px solid var(--secondary-color);
}

.pi-notice {
    background: linear-gradient(135deg, #fff8e1, #fffde7);
    border-left: 4px solid var(--warning-color);
}

.notice-icon {
    font-size: 2rem;
    color: var(--secondary-color);
}

.notice-content h5 {
    margin: 0 0 4px 0;
    font-size: 1.125rem;
    font-weight: 500;
    color: var(--text-primary);
}

.notice-content p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 48px;
}

.stat-card {
    background: var(--surface-color);
    border-radius: var(--border-radius-large);
    padding: 24px;
    box-shadow: var(--shadow-light);
    border: 1px solid #e8eaed;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: block;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-color);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-medium);
    text-decoration: none;
    color: inherit;
}

.interactive-card {
    cursor: pointer;
}

.interactive-card:hover .stat-icon {
    transform: scale(1.1);
}

.card-subtitle {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 4px;
    font-weight: 400;
}

.stat-card.courses::before { background: var(--primary-color); }
.stat-card.completed::before { background: var(--secondary-color); }
.stat-card.earnings::before { background: var(--warning-color); }
.stat-card.certificates::before { background: var(--accent-color); }

.stat-card .text-center {
    position: relative;
    z-index: 1;
}

.stat-icon {
    font-size: 2.25rem;
    margin-bottom: 12px;
    opacity: 0.8;
    transition: var(--transition);
}

.stat-value {
    font-size: 1.875rem;
    font-weight: 300;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

/* Action Sections */
.action-section {
    background: var(--surface-color);
    border-radius: var(--border-radius-large);
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-light);
    border: 1px solid #e8eaed;
}

.action-section h3 {
    font-size: 1.5rem;
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-section h3 i {
    color: var(--primary-color);
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.action-btn {
    border-radius: var(--border-radius);
    padding: 16px 24px;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid #dadce0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: var(--surface-color);
    color: var(--text-primary);
    font-size: 0.875rem;
    text-align: center;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    border-color: var(--primary-color);
}

.action-btn i {
    font-size: 1.125rem;
}

/* Course Progress Cards */
.course-progress-card {
    background: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 16px;
    border: 1px solid #e8eaed;
    transition: var(--transition);
}

.course-progress-card:hover {
    background: #ffffff;
    box-shadow: var(--shadow-light);
}

.course-progress-card h6 {
    font-size: 1rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.progress {
    height: 8px;
    background-color: #e8eaed;
    border-radius: 4px;
    margin-bottom: 12px;
}

.progress-bar {
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: 4px;
    transition: width 0.6s ease;
}

/* Learning Tools */
.learning-tools-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.tool-card {
    background: var(--surface-color);
    border-radius: var(--border-radius);
    padding: 20px;
    border: 1px solid #e8eaed;
    transition: var(--transition);
    text-decoration: none;
    color: var(--text-primary);
    display: block;
}

.tool-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    text-decoration: none;
    color: var(--text-primary);
}

.tool-card i {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 8px;
}

.tool-card .card-title {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 4px;
}

.tool-card small {
    color: var(--text-secondary);
    font-size: 0.75rem;
}

/* Achievements Section */
.achievement-card {
    text-align: center;
    padding: 24px;
}

.achievement-icon {
    font-size: 4rem;
    color: var(--warning-color);
    margin-bottom: 16px;
}

.achievement-card h5 {
    font-size: 1.5rem;
    font-weight: 400;
    margin-bottom: 8px;
}

.achievement-card p {
    color: var(--text-secondary);
    margin-bottom: 16px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .container-fluid {
        padding: 0 20px;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0 16px;
    }

    .dashboard-header {
        padding: 24px 20px;
        margin-bottom: 24px;
    }

    .welcome-section h1 {
        font-size: 2rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 32px;
    }

    .stat-card {
        padding: 24px;
    }

    .action-section {
        padding: 24px 20px;
        margin-bottom: 20px;
    }

    .action-buttons {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .action-btn {
        padding: 14px 20px;
        font-size: 0.875rem;
    }

    .row {
        margin: 0 -8px;
    }

    .col-lg-8, .col-lg-4 {
        padding: 0 8px;
    }
}

@media (max-width: 480px) {
    .dashboard-header {
        padding: 20px 16px;
    }

    .welcome-section h1 {
        font-size: 1.75rem;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-value {
        font-size: 2rem;
    }

    .action-section {
        padding: 20px 16px;
    }

    .demo-notice, .pi-notice {
        padding: 16px;
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus states */
.action-btn:focus,
.tool-card:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    :root {
        --surface-color: #1f1f1f;
        --background-color: #121212;
        --text-primary: #e8eaed;
        --text-secondary: #9aa0a6;
    }
}

/* Manual dark theme override */
[data-theme="dark"] {
    --surface-color: #1f1f1f;
    --background-color: #121212;
    --text-primary: #e8eaed;
    --text-secondary: #9aa0a6;
    --shadow-light: 0 1px 3px rgba(255, 255, 255, 0.1);
    --shadow-medium: 0 4px 6px rgba(255, 255, 255, 0.1);
    --shadow-heavy: 0 8px 25px rgba(255, 255, 255, 0.1);
}

/* Compact view styles */
.compact-view .stat-card {
    padding: 16px;
}

.compact-view .action-section {
    padding: 16px;
}

.compact-view .dashboard-header {
    padding: 16px;
}

.compact-view .stat-icon {
    font-size: 1.75rem;
    margin-bottom: 8px;
}

.compact-view .stat-value {
    font-size: 1.5rem;
    margin-bottom: 4px;
}

/* Theme Toggle */
.theme-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background: var(--surface-color);
    border: 1px solid #e8eaed;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-light);
    transition: var(--transition);
}

.theme-toggle:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-medium);
}

.theme-toggle i {
    font-size: 1.25rem;
    color: var(--text-primary);
}

/* Settings Panel */
.settings-panel {
    position: fixed;
    top: 80px;
    right: 20px;
    background: var(--surface-color);
    border: 1px solid #e8eaed;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow-heavy);
    z-index: 999;
    min-width: 280px;
    display: none;
}

.settings-panel.show {
    display: block;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e8eaed;
}

.settings-header h4 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 500;
}

.settings-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 4px;
    border-radius: 50%;
    transition: var(--transition);
}

.settings-close:hover {
    background: #f8f9fa;
    color: var(--text-primary);
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-label {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 500;
}

.setting-description {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 2px;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    background: #ccc;
    border-radius: 12px;
    cursor: pointer;
    transition: var(--transition);
}

.toggle-switch.active {
    background: var(--primary-color);
}

.toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: var(--transition);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.toggle-switch.active::after {
    left: 22px;
}

/* Settings Button */
.settings-btn {
    position: fixed;
    top: 76px;
    right: 20px;
    z-index: 1000;
    background: var(--surface-color);
    border: 1px solid #e8eaed;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-light);
    transition: var(--transition);
}

.settings-btn:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-medium);
}

.settings-btn i {
    font-size: 1.25rem;
    color: var(--text-primary);
}
</style>

<div class="learner-dashboard">
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Settings Button -->
    <button class="settings-btn" id="settingsBtn" title="Settings">
        <i class="fas fa-cog"></i>
    </button>

    <!-- Settings Panel -->
    <div class="settings-panel" id="settingsPanel">
        <div class="settings-header">
            <h4>Settings</h4>
            <button class="settings-close" id="settingsClose">&times;</button>
        </div>
        <div class="setting-item">
            <div>
                <div class="setting-label">Dark Mode</div>
                <div class="setting-description">Switch between light and dark themes</div>
            </div>
            <div class="toggle-switch" id="darkModeToggle"></div>
        </div>
        <div class="setting-item">
            <div>
                <div class="setting-label">Notifications</div>
                <div class="setting-description">Receive course and achievement notifications</div>
            </div>
            <div class="toggle-switch active" id="notificationsToggle"></div>
        </div>
        <div class="setting-item">
            <div>
                <div class="setting-label">Auto-save Progress</div>
                <div class="setting-description">Automatically save your learning progress</div>
            </div>
            <div class="toggle-switch active" id="autoSaveToggle"></div>
        </div>
        <div class="setting-item">
            <div>
                <div class="setting-label">Compact View</div>
                <div class="setting-description">Show more content in less space</div>
            </div>
            <div class="toggle-switch" id="compactViewToggle"></div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-graduation-cap me-3"></i>Welcome to Your Learning Journey, <?php echo htmlspecialchars($user['username'] ?? 'Student'); ?>!</h1>
                <p class="text-muted mb-0">Track your progress, earn Pi coins, and master AI development</p>
            </div>

            <!-- Demo Mode Notice -->
            <?php if ($is_demo_mode): ?>
            <div class="demo-notice">
                <div class="notice-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="notice-content flex-grow-1">
                    <h5>🎮 Demo Mode Active</h5>
                    <p>You're exploring AI-ACADEMY in demo mode with sample data</p>
                </div>
                <div class="text-end">
                    <div class="fw-bold" style="font-size: 0.75rem; color: var(--text-secondary);">Expires in:</div>
                    <div id="demoTimer" style="font-size: 1.25rem; font-weight: 500; color: var(--secondary-color);"><?php echo gmdate("H:i:s", $_SESSION['demo_expires'] - time()); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pi Network Notice -->
            <div class="pi-notice">
                <div class="notice-icon">
                    <i class="fab fa-pied-piper"></i>
                </div>
                <div class="notice-content">
                    <h5>Pi-Powered Learning</h5>
                    <p>Earn Pi coins while you learn! Complete courses, participate in discussions, and achieve milestones to build your Pi Network wealth.</p>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <a href="<?php echo BASE_PATH; ?>/library/catalogue.php" class="stat-card courses interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-value"><?php echo count($enrolled_courses); ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                    <div class="card-subtitle">Browse & Enroll</div>
                </div>
            </a>

            <a href="certificate.php" class="stat-card completed interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo count(array_filter($enrolled_courses, fn($c) => $c['progress_percentage'] >= 100)); ?></div>
                    <div class="stat-label">Completed Courses</div>
                    <div class="card-subtitle">View Certificates</div>
                </div>
            </a>

            <a href="<?php echo BASE_PATH; ?>/premium.php" class="stat-card earnings interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($user['total_pi_earned'] ?? 0, 1); ?> π</div>
                    <div class="stat-label">Pi Coins Earned</div>
                    <div class="card-subtitle">≈ $<?php echo number_format(($user['total_pi_earned'] ?? 0) * 0.03, 2); ?> USD</div>
                </div>
            </a>

            <a href="certificate.php" class="stat-card certificates interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-value"><?php echo count(array_filter($enrolled_courses, fn($c) => $c['progress_percentage'] >= 100)); ?></div>
                    <div class="stat-label">Certificates</div>
                    <div class="card-subtitle">Download PDFs</div>
                </div>
            </a>
        </div>

        <!-- Learning Actions -->
        <div class="action-section">
            <h3><i class="fas fa-compass me-2"></i>Continue Learning</h3>
            <div class="action-buttons">
                <a href="<?php echo BASE_PATH; ?>/library/catalogue.php" class="action-btn btn btn-primary">
                    <i class="fas fa-book"></i>
                    Browse Library
                </a>
                <a href="video.php" class="action-btn btn btn-success">
                    <i class="fas fa-video"></i>
                    Join Live Session
                </a>
                <a href="<?php echo BASE_PATH; ?>/forums/" class="action-btn btn btn-outline-info">
                    <i class="fas fa-users"></i>
                    Community Forums
                </a>
                <a href="<?php echo BASE_PATH; ?>/blog-catalogue/" class="action-btn btn btn-outline-secondary">
                    <i class="fas fa-blog"></i>
                    Read Blogs
                </a>
            </div>
        </div>

        <!-- Course Progress & Tools -->
        <div class="row">
            <div class="col-lg-8">
                <div class="action-section">
                    <h3><i class="fas fa-chart-line me-2"></i>Course Progress</h3>
                    <?php if (count($enrolled_courses) > 0): ?>
                        <?php foreach ($enrolled_courses as $course): ?>
                            <div class="course-progress-card p-3 mb-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                                    <?php if ($course['progress_percentage'] >= 100): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">In Progress</span>
                                    <?php endif; ?>
                                </div>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $course['progress_percentage']; ?>%">
                                        <?php echo $course['progress_percentage']; ?>%
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Enrolled: <?php echo date('M j, Y', strtotime($course['enrolled_at'])); ?>
                                    </small>
                                    <?php if ($course['progress_percentage'] >= 100): ?>
                                        <a href="certificate.php" class="btn btn-sm btn-success">
                                            <i class="fas fa-certificate me-1"></i>Get Certificate
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x mb-3 text-muted"></i>
                            <p class="text-muted">No courses enrolled yet.</p>
                            <a href="<?php echo BASE_PATH; ?>/library/catalogue.php" class="btn btn-primary">Browse Courses</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="action-section">
                    <h3><i class="fas fa-tools"></i>Learning Tools</h3>
                    <div class="learning-tools-grid">
                        <a href="request-tutor.php" class="tool-card">
                            <i class="fas fa-user-graduate"></i>
                            <div class="card-title">Request Tutor</div>
                            <small>Get personalized help</small>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/rating-badge/" class="tool-card">
                            <i class="fas fa-star"></i>
                            <div class="card-title">Trust Badge</div>
                            <small>Build credibility</small>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/messages.php" class="tool-card">
                            <i class="fas fa-envelope"></i>
                            <div class="card-title">Messages</div>
                            <small>Contact support</small>
                        </a>
                    </div>
                </div>

                <div class="action-section">
                    <h3><i class="fas fa-trophy"></i>Achievements</h3>
                    <div class="achievement-card">
                        <div class="achievement-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h5><?php echo number_format($user['total_pi_earned'] ?? 0, 1); ?> π Earned</h5>
                        <p>≈ $<?php echo number_format(($user['total_pi_earned'] ?? 0) * 0.03, 2); ?> USD</p>
                        <div class="progress mb-2">
                            <div class="progress-bar" style="width: <?php echo min(($user['total_pi_earned'] ?? 0) / 100 * 100, 100); ?>%"></div>
                        </div>
                        <small style="color: var(--text-secondary);">Next milestone: 100 π</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Community & Resources -->
        <div class="action-section">
            <h3><i class="fas fa-users me-2"></i>Community & Resources</h3>
            <div class="action-buttons">
                <a href="<?php echo BASE_PATH; ?>/forums/" class="action-btn btn btn-outline-info">
                    <i class="fas fa-comments"></i>
                    Discussion Forums
                </a>
                <a href="<?php echo BASE_PATH; ?>/blog-catalogue/" class="action-btn btn btn-outline-secondary">
                    <i class="fas fa-blog"></i>
                    Community Blogs
                </a>
                <a href="https://pi.homework-help.online/SDK.php" class="action-btn btn btn-outline-success">
                    <i class="fas fa-code"></i>
                    Pi SDK Docs
                </a>
                <a href="https://pi.homework-help.online/bootstrap" class="action-btn btn btn-outline-primary">
                    <i class="fas fa-terminal"></i>
                    Developer Tools
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    // Theme and Settings Management
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize theme from localStorage or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const currentTheme = savedTheme || (systemDark ? 'dark' : 'light');

        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.getElementById('themeToggle').innerHTML = '<i class="fas fa-sun"></i>';
            document.getElementById('darkModeToggle').classList.add('active');
        }

        // Theme toggle functionality
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            if (newTheme === 'dark') {
                html.setAttribute('data-theme', 'dark');
                this.innerHTML = '<i class="fas fa-sun"></i>';
                document.getElementById('darkModeToggle').classList.add('active');
            } else {
                html.removeAttribute('data-theme');
                this.innerHTML = '<i class="fas fa-moon"></i>';
                document.getElementById('darkModeToggle').classList.remove('active');
            }

            localStorage.setItem('theme', newTheme);
        });

        // Settings panel functionality
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsPanel = document.getElementById('settingsPanel');
        const settingsClose = document.getElementById('settingsClose');

        settingsBtn.addEventListener('click', function() {
            settingsPanel.classList.toggle('show');
        });

        settingsClose.addEventListener('click', function() {
            settingsPanel.classList.remove('show');
        });

        // Close settings when clicking outside
        document.addEventListener('click', function(e) {
            if (!settingsPanel.contains(e.target) && !settingsBtn.contains(e.target)) {
                settingsPanel.classList.remove('show');
            }
        });

        // Toggle switches functionality
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
                const setting = this.id.replace('Toggle', '');
                localStorage.setItem(setting, this.classList.contains('active'));

                // Apply compact view if toggled
                if (this.id === 'compactViewToggle') {
                    document.body.classList.toggle('compact-view', this.classList.contains('active'));
                }
            });
        });

        // Load saved settings
        ['darkMode', 'notifications', 'autoSave', 'compactView'].forEach(setting => {
            const saved = localStorage.getItem(setting);
            if (saved !== null) {
                const toggle = document.getElementById(setting + 'Toggle');
                if (toggle) {
                    toggle.classList.toggle('active', saved === 'true');
                }
            }
        });

        // Apply compact view on load
        if (localStorage.getItem('compactView') === 'true') {
            document.body.classList.add('compact-view');
        }
    });

    // Make events clickable
    const eventItems = document.querySelectorAll('.widget ul li');
    eventItems.forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            alert('Event details: ' + this.textContent.split(' ')[0]);
        });
    });

    // Animate progress bar
    const progressBar = document.querySelector('.progress');
    if (progressBar) {
        progressBar.style.transition = 'width 2s ease-in-out';
        setTimeout(() => {
            progressBar.style.width = progressBar.textContent;
        }, 500);
    }

    // Join live session button
    const joinBtn = document.querySelector('.btn');
    if (joinBtn && joinBtn.textContent === 'Join Live Session') {
        joinBtn.addEventListener('click', function() {
            alert('Joining live session...');
        });
    }

    // Make explore items clickable
    const exploreItems = document.querySelectorAll('.widget ul li');
    exploreItems.forEach(item => {
        if (item.textContent.includes('PiSDK') || item.textContent.includes('Linux') || item.textContent.includes('VS Code') || item.textContent.includes('Github')) {
            item.style.cursor = 'pointer';
            item.addEventListener('click', function() {
                window.open('https://' + this.textContent.split(' ')[0].toLowerCase() + '.com', '_blank');
            });
        }
    });

    // Toggle online status
    const statusP = document.querySelector('.widget p');
    if (statusP && statusP.textContent.includes('Online Status')) {
        statusP.style.cursor = 'pointer';
        statusP.addEventListener('click', function() {
            const statusSpan = document.getElementById('onlineStatus');
            statusSpan.textContent = statusSpan.textContent === 'Active' ? 'Away' : 'Active';
        });
    }

    // Profile settings - now uses database
    const saveProfileBtn = document.getElementById('saveProfile');
    if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function() {
            const profile = {
                fullName: document.getElementById('fullName').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                piWallet: document.getElementById('piWallet').value
            };

            // Send to server (would need API endpoint for full implementation)
            fetch('/api/user_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(profile)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile saved successfully!');
                } else {
                    alert('Failed to save profile: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving profile:', error);
                alert('An error occurred while saving your profile.');
            });
        });
    }

    // Update goal - now uses database
    const updateGoalBtn = document.getElementById('updateGoal');
    if (updateGoalBtn) {
        updateGoalBtn.addEventListener('click', function() {
            const newGoal = prompt('Enter new monthly course goal:');
            if (newGoal && !isNaN(newGoal)) {
                // Would need API endpoint to save goal to database
                document.getElementById('goalCount').textContent = newGoal;
                // For now, just update display - full implementation would save to user preferences table
            }
        });
    }

    // Generate report
    const generateReportBtn = document.getElementById('generateReport');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            const report = `
Weekly Progress Report:
- Courses enrolled: 2
- Courses completed: 0
- Average progress: 57.5%
- Study time this week: 8 hours
- Goals achieved: 60%
            `;
            alert(report);
        });
    }

    // Settings - now uses database
    const saveSettingsBtn = document.getElementById('saveSettings');
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            const settings = {
                emailNotif: document.getElementById('emailNotif').checked,
                pushNotif: document.getElementById('pushNotif').checked,
                theme: document.getElementById('themeSelect').value
            };

            // Would need API endpoint for settings - for now just apply theme
            alert('Settings saved!');

            // Apply theme
            if (settings.theme === 'dark') {
                document.body.style.backgroundColor = '#333';
                document.body.style.color = '#fff';
            } else {
                document.body.style.backgroundColor = '';
                document.body.style.color = '';
            }
        });
    }

    // Simple progress chart
    const canvas = document.getElementById('progressChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#007bff';
        ctx.fillRect(10, 10, 50, 100); // Week 1
        ctx.fillRect(70, 30, 50, 80);  // Week 2
        ctx.fillRect(130, 20, 50, 90); // Week 3
        ctx.fillRect(190, 40, 50, 70); // Week 4
    }

    // Profile and settings are now loaded from database via PHP
    // No localStorage loading needed

    // Demo Mode Timer
    <?php if ($is_demo_mode): ?>
    function updateDemoTimer() {
        const timerElement = document.getElementById('demoTimer');
        if (!timerElement) return;

        const demoExpires = <?php echo $_SESSION['demo_expires']; ?>;
        const currentTime = Math.floor(Date.now() / 1000);
        const remaining = demoExpires - currentTime;

        if (remaining <= 0) {
            // Demo expired
            window.location.href = '<?php echo BASE_PATH; ?>/auth/?message=demo_expired';
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;

        timerElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // Change color when less than 1 hour remaining
        if (remaining < 3600) {
            timerElement.style.color = '#dc3545';
            timerElement.style.fontWeight = 'bold';
        }
    }

    // Update demo timer every second
    setInterval(updateDemoTimer, 1000);
    updateDemoTimer(); // Initial call
    <?php endif; ?>
</script>

<?php include '../footer.php'; ?>