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

// Check authentication and role BEFORE including header
// Allow demo mode users
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
if (!$is_demo_mode && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Check if user has tutor role or can switch to tutor mode
$user_role = $_SESSION['user_role'] ?? 'student';
if ($user_role !== 'tutor' && $user_role !== 'admin' && !$is_demo_mode) {
    // Allow role switching for authenticated users
    if (isset($_GET['switch_role']) && $_GET['switch_role'] === 'tutor') {
        $_SESSION['user_role'] = 'tutor';
        $user_role = 'tutor';
    } else {
        // Redirect to role switcher or learner dashboard
        header('Location: ' . BASE_PATH . '/learnershub/?message=Access restricted to tutors only. Use role switcher to become a tutor.');
        exit;
    }
}

// For demo mode, set role to tutor
if ($is_demo_mode) {
    $user_role = 'tutor';
    $_SESSION['user_role'] = 'tutor';
}

// Tutor Dashboard for AI-ACADEMY
$page_title = 'Tutor Dashboard';
include '../header.php';

// Handle demo mode vs real tutor data
if ($is_demo_mode) {
    // Demo mode: Provide mock data
    $user = [
        'username' => 'Demo Tutor',
        'total_pi_earned' => 150.75,
        'email' => 'demo-tutor@ai-academy.com'
    ];

    $publications = 5;
    $students = 23;
    $earnings = 150.75;
    $pending_tasks = 3;
} else {
    // Real tutor: Get actual data from database
    $user_id = $_SESSION['user_id'];
    $user = getUserById($user_id);

    // Get tutor's publications count
    $publications_result = fetchOne("SELECT COUNT(*) as count FROM blogs WHERE user_id = ?", [$user_id]);
    $publications = $publications_result ? $publications_result['count'] : 0;

    // Get students count (users who have enrolled in tutor's courses)
    $students_result = fetchOne("
        SELECT COUNT(DISTINCT ce.user_id) as count
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.id
        WHERE c.instructor_id = ?
    ", [$user_id]);
    $students = $students_result ? $students_result['count'] : 0;

    // Get earnings from database
    $earnings = ($user && isset($user['total_pi_earned'])) ? $user['total_pi_earned'] : 0;

    // Get pending tasks (could be forum questions, messages, etc.)
    $pending_tasks_result = fetchOne("
        SELECT COUNT(*) as count FROM messages
        WHERE receiver_id = ? AND read_at IS NULL
    ", [$user_id]);
    $pending_tasks = $pending_tasks_result ? $pending_tasks_result['count'] : 0;
}
?>

<!-- Modern Google-Inspired Tutor Dashboard Styles -->
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

.tutor-dashboard {
    min-height: 100vh;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 50%, #fff3e5f5 100%);
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

.stat-card.publications::before { background: var(--primary-color); }
.stat-card.students::before { background: var(--secondary-color); }
.stat-card.earnings::before { background: var(--warning-color); }
.stat-card.tasks::before { background: var(--accent-color); }

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

<div class="tutor-dashboard">
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-chalkboard-teacher me-3"></i>Welcome back, <?php echo htmlspecialchars(($user && isset($user['username'])) ? $user['username'] : 'Demo Tutor'); ?>!</h1>
                <p class="text-muted mb-0">Manage your courses, track earnings, and engage with students</p>
            </div>

            <!-- Demo Mode Notice -->
            <?php if ($is_demo_mode): ?>
            <div class="demo-notice">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-play-circle fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-1">🎮 Demo Mode Active</h5>
                            <p class="mb-0 opacity-90">You're exploring AI-ACADEMY in demo mode with sample data</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">Expires in:</div>
                        <div id="demoTimer" class="h5 mb-0"><?php echo gmdate("H:i:s", $_SESSION['demo_expires'] - time()); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pi Network Notice -->
            <div class="pi-notice">
                <div class="d-flex align-items-center">
                    <i class="fas fa-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">Pi Network Integration</h5>
                        <p class="mb-0">All earnings and payments are processed in Pi coins. Join the Pi Network ecosystem to receive your earnings seamlessly.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <a href="publications/list.php" class="stat-card publications interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-value"><?php echo $publications; ?></div>
                    <div class="stat-label">Publications</div>
                    <div class="card-subtitle">View & Manage</div>
                </div>
            </a>

            <a href="courses/list.php" class="stat-card students interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $students; ?></div>
                    <div class="stat-label">Students</div>
                    <div class="card-subtitle">Enrolled Students</div>
                </div>
            </a>

            <a href="earnings/overview.php" class="stat-card earnings interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value"><?php echo $earnings; ?> π</div>
                    <div class="stat-label">Pi Coin Earnings</div>
                    <div class="card-subtitle">≈ $<?php echo round($earnings * 0.03, 2); ?> USD</div>
                </div>
            </a>

            <a href="tasks/list.php" class="stat-card tasks interactive-card">
                <div class="text-center">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-value"><?php echo $pending_tasks; ?></div>
                    <div class="stat-label">Pending Tasks</div>
                    <div class="card-subtitle">View Tasks</div>
                </div>
            </a>
        </div>

        <!-- Course Management Actions -->
        <div class="action-section">
            <h3><i class="fas fa-cogs me-2"></i>Course Management</h3>
            <div class="action-buttons">
                <a href="courses/create.php" class="action-btn btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Create Course
                </a>
                <a href="courses/list.php" class="action-btn btn btn-outline-primary">
                    <i class="fas fa-list"></i>
                    My Courses
                </a>
                <a href="publications/create.php" class="action-btn btn btn-outline-secondary">
                    <i class="fas fa-blog"></i>
                    Create Blog
                </a>
                <a href="publications/list.php" class="action-btn btn btn-outline-secondary">
                    <i class="fas fa-book-open"></i>
                    My Publications
                </a>
            </div>
        </div>

        <!-- Teaching & Earnings Actions -->
        <div class="action-section">
            <h3><i class="fas fa-graduation-cap me-2"></i>Teaching & Earnings</h3>
            <div class="action-buttons">
                <a href="sessions.php" class="action-btn btn btn-outline-primary">
                    <i class="fas fa-calendar-check"></i>
                    Manage Sessions
                </a>
                <a href="tasks/list.php" class="action-btn btn btn-outline-info">
                    <i class="fas fa-tasks"></i>
                    View Tasks
                </a>
                <a href="earnings/overview.php" class="action-btn btn btn-outline-warning">
                    <i class="fas fa-coins"></i>
                    Check Earnings
                </a>
                <a href="video.php" class="action-btn btn btn-success">
                    <i class="fas fa-video"></i>
                    Start Live Session
                </a>
            </div>
        </div>


        <!-- Communication & Tools -->
        <div class="row">
            <div class="col-lg-6">
                <div class="action-section">
                    <h3><i class="fas fa-comments me-2"></i>Communication</h3>
                    <div class="d-grid gap-3">
                        <a href="<?php echo BASE_PATH; ?>/messages.php" class="action-btn btn btn-info">
                            <i class="fas fa-envelope me-2"></i>
                            Messages
                            <small class="d-block opacity-75">Communicate with students and admin</small>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/forums/" class="action-btn btn btn-outline-info">
                            <i class="fas fa-users me-2"></i>
                            Community Forums
                            <small class="d-block opacity-75">Engage with the community</small>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="action-section">
                    <h3><i class="fas fa-certificate me-2"></i>Professional Tools</h3>
                    <div class="d-grid gap-3">
                        <a href="<?php echo BASE_PATH; ?>/rating-badge/" class="action-btn btn btn-warning">
                            <i class="fas fa-star me-2"></i>
                            Trust Badge
                            <small class="d-block opacity-75">Build credibility with ratings</small>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/admin/" class="action-btn btn btn-outline-danger">
                            <i class="fas fa-cog me-2"></i>
                            Admin Panel
                            <small class="d-block opacity-75">System administration</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="action-section">
            <h3><i class="fas fa-history me-2"></i>Recent Activity</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="activity-item p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                        <div>Publication "AI Ethics" approved</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="activity-item p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-tasks text-info me-2"></i>
                            <small class="text-muted">5 hours ago</small>
                        </div>
                        <div>Task completed: Help with Python</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="activity-item p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-user-plus text-primary me-2"></i>
                            <small class="text-muted">1 day ago</small>
                        </div>
                        <div>New student enrolled</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>