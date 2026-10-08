<?php
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

// Start session first - Fix session path for XAMPP
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

// Homepage for AI-ACADEMY LMS
// Landing page with hero section, features, and navigation
$page_title = 'Welcome to AI-ACADEMY';
include 'header.php';

// Check for admin logout notification
$showAdminLogout = isset($_GET['admin_logout']);
?>

<!-- Admin Logout Notification -->
<?php if ($showAdminLogout): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-sign-out-alt me-2"></i>
    <strong>Admin Session Ended</strong> - You have been successfully logged out of the admin panel.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Hero Section -->
<div class="hero-section text-center py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-purple), var(--secondary-blue)); color: var(--text-light); min-height: 70vh;">
    <!-- Background Graphics -->
    <div class="hero-bg-graphics">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-3 fw-bold mb-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Welcome to AI-ACADEMY</h1>
                 <p class="lead mb-4 fs-4" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">Learn AI to Build Pi Ecosystem Apps - Master Blockchain Development with Expert Tutors</p>


                <div class="hero-actions">
                    <button onclick="handleJoinNow()" class="btn btn-light btn-lg me-3 px-4 py-3 fw-bold">
                        <i class="fab fa-pied-piper me-2"></i>Join Now
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/features/" class="btn btn-outline-light btn-lg px-4 py-3 fw-bold">Explore Features</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <a href="<?php echo BASE_PATH; ?>/rating-badge/" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">AI for Pi Apps</h5>
                    <p class="card-text">Learn AI techniques specifically for building Pi Network applications and services.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo BASE_PATH; ?>/bootstrap/" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="fas fa-code fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Pi App Development</h5>
                    <p class="card-text">Build decentralized applications that run on the Pi Network ecosystem.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo BASE_PATH; ?>/SDK.php" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="fas fa-link fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Pi Blockchain Integration</h5>
                    <p class="card-text">Master Pi Network blockchain technology and smart contract development.</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <h2 class="text-primary">10,000+</h2>
                <p>Students Enrolled</p>
            </div>
            <div class="col-md-3">
                <h2 class="text-primary">500+</h2>
                <p>Expert Tutors</p>
            </div>
            <div class="col-md-3">
                <h2 class="text-primary">1,000+</h2>
                <p>Courses Available</p>
            </div>
            <div class="col-md-3">
                <h2 class="text-primary">95%</h2>
                <p>Success Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div class="container my-5">
    <h2 class="text-center mb-4">What Our Students Say</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p>"AI-ACADEMY transformed my career in AI development!"</p>
                    <footer class="blockquote-footer">John Doe, AI Engineer</footer>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p>"The blockchain courses are top-notch and practical."</p>
                    <footer class="blockquote-footer">Jane Smith, Blockchain Developer</footer>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p>"Excellent platform for learning software development."</p>
                    <footer class="blockquote-footer">Mike Johnson, Full-Stack Developer</footer>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="text-center py-5" style="background-color: var(--primary-purple); color: var(--text-light);">
    <div class="container">
        <h2>Ready to Build AI Apps for Pi Network?</h2>
        <p>Join the growing community of developers creating the future of decentralized AI applications.</p>
        <a href="<?php echo BASE_PATH; ?>/auth/" class="btn btn-light btn-lg">
            <i class="fab fa-pied-piper me-2"></i>Start Learning Pi AI Development
        </a>
    </div>
</div>

<style>
.hero-bg-graphics {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.floating-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 60px;
    height: 60px;
    top: 20%;
    right: 15%;
    animation-delay: 2s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    bottom: 15%;
    left: 20%;
    animation-delay: 4s;
}

.shape-4 {
    width: 70px;
    height: 70px;
    bottom: 10%;
    right: 10%;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

</style>

<script>
// Handle Join Now button - prompt authentication
function handleJoinNow() {
    // Check if Pi SDK is available
    if (typeof Pi === 'undefined') {
        alert('Pi Network authentication requires the Pi Browser. Please download and install the Pi Browser to continue.');
        window.open('https://minepi.com/browser', '_blank');
        return;
    }

    // Check if user is already authenticated
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
        // User is already logged in, redirect to learners hub
        window.location.href = '<?php echo BASE_PATH; ?>/learnershub/';
        return;
    <?php endif; ?>

    // Show loading state
    const joinBtn = event.target.closest('button');
    const originalText = joinBtn.innerHTML;
    joinBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
    joinBtn.disabled = true;

    // Pi Network Authentication
    const scopes = ['payments', 'username'];

    function onIncompletePaymentFound(payment) {
        console.log('Incomplete payment found:', payment);
    }

    // Add timeout to prevent hanging
    const authPromise = Pi.authenticate(scopes, onIncompletePaymentFound);
    const timeoutPromise = new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Authentication timeout - please check your internet connection and try again')), 90000)
    );

    Promise.race([authPromise, timeoutPromise]).then(function(auth) {
        console.log('Pi Authentication successful:', auth);

        // Show success state briefly
        joinBtn.innerHTML = '<i class="fas fa-check me-1"></i>Success!';
        joinBtn.classList.remove('btn-light');
        joinBtn.classList.add('btn-success');

        // Redirect to auth page with access token after brief delay
        const accessToken = auth.accessToken;
        setTimeout(function() {
            window.location.href = '<?php echo BASE_PATH; ?>/auth/?access_token=' + accessToken;
        }, 1000);

    }).catch(function(error) {
        console.error('Pi Authentication failed:', error);

        // Reset button
        joinBtn.innerHTML = originalText;
        joinBtn.disabled = false;
        joinBtn.classList.remove('btn-success');
        joinBtn.classList.add('btn-light');

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
}
</script>

<?php include 'footer.php'; ?>