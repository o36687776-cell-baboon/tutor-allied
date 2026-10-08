<?php
/**
 * Pi Network Authentication Page
 * Handles Pi Network authentication only
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

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

// Start session early for authentication logic
$session_path = __DIR__ . '/../sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}
session_save_path($session_path);

// Set secure cookie for HTTPS with Pi Browser compatibility
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // More compatible than 'Strict' for Pi Browser
]);
session_start();

error_log("Pi Auth page loaded - Request method: " . $_SERVER['REQUEST_METHOD']);

// Start session and include necessary files first
$page_title = 'Pi Network Authentication - AI-ACADEMY';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../pi_config.php';
require_once __DIR__ . '/../logger.php';

// Check if user is already authenticated (before any output)
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    header('Location: ' . BASE_PATH . '/');
    exit;
}

// Handle demo mode activation
if (isset($_GET['demo']) && $_GET['demo'] === 'true') {
    // Set demo session
    $_SESSION['demo_mode'] = true;
    $_SESSION['user_id'] = 'demo_' . session_id();
    $_SESSION['pi_username'] = 'Demo User';
    $_SESSION['user_role'] = 'student'; // Default to student role
    $_SESSION['demo_expires'] = time() + (24 * 60 * 60); // 24 hours

    // Redirect to learners hub
    header('Location: ' . BASE_PATH . '/learnershub/');
    exit;
}

// Handle Pi Network authentication callback
if (isset($_GET['access_token'])) {
    $access_token = $_GET['access_token'];

    // Log authentication attempt
    error_log("Pi Network authentication callback received - Access Token: " . substr($access_token, 0, 10) . "...");

    // Authenticate with Pi Network using sandbox endpoints
    $auth_result = PiNetwork::authenticateUser($access_token);

    if ($auth_result['success']) {
        // Store user information in session
        $_SESSION['pi_authenticated'] = true;
        $_SESSION['pi_user_id'] = $auth_result['data']['uid'];
        $_SESSION['pi_username'] = $auth_result['data']['username'];
        $_SESSION['pi_access_token'] = $auth_result['data']['access_token'];
        $_SESSION['user_id'] = $auth_result['data']['db_user_id'];

        // Log successful authentication
        error_log("Pi Network authentication successful - User: " . $_SESSION['pi_username'] . " (ID: " . $_SESSION['user_id'] . ")");

        // Check if this is a new user
        $is_new_user = $auth_result['data']['is_new_user'] ?? false;

        if ($is_new_user) {
            // New user - set default role and redirect to dashboard
            $_SESSION['user_role'] = 'student';
            error_log("New Pi user registered: " . $_SESSION['pi_username']);
        } else {
            // Existing user - get role from database
            $user = getUserById($_SESSION['user_id']);
            $_SESSION['user_role'] = $user ? $user['user_role'] : 'student';
            error_log("Existing Pi user logged in: " . $_SESSION['pi_username']);
        }

        // Special handling for admin users
        if ($_SESSION['user_role'] === 'admin') {
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_username'] = $_SESSION['pi_username'];
            $_SESSION['admin_role'] = 'admin';
            $_SESSION['admin_login_time'] = time();

            // Log admin login
            require_once __DIR__ . '/../logger.php';
            logEvent('admin_login', ['username' => $_SESSION['pi_username'], 'ip' => $_SERVER['REMOTE_ADDR']]);
        }

        // Update last login timestamp
        updateUser($_SESSION['user_id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Determine redirect destination
        $redirect_url = $_GET['redirect'] ?? BASE_PATH . '/learnershub/';

        // For demo purposes, always redirect to learnershub unless admin
        if ($_SESSION['user_role'] === 'admin') {
            $redirect_url = BASE_PATH . '/admin/';
        } elseif ($_SESSION['user_role'] === 'tutor') {
            $redirect_url = BASE_PATH . '/tutor/';
        } else {
            $redirect_url = BASE_PATH . '/learnershub/';
        }

        error_log("Redirecting authenticated user to: " . $redirect_url);

        ob_end_clean();
        header('Location: ' . $redirect_url);
        exit;

    } else {
        // Authentication failed
        $pi_error = 'Pi Network authentication failed: ' . ($auth_result['error'] ?? 'Unknown error. Please try again.');
        error_log("Pi Network authentication failed: " . $pi_error);
    }
}

// Include header after all redirects are done
include '../header.php';

?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">
                        <i class="fab fa-pied-piper fa-2x me-2"></i>
                        Welcome to AI-ACADEMY
                    </h3>
                    <p class="mb-0 mt-2">Log in with your Pi Network account</p>
                </div>
                <div class="card-body p-5 text-center">

                    <?php if (isset($pi_error)): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $pi_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="pi-auth-section">
                        <div class="mb-4">
                            <i class="fab fa-pied-piper fa-5x text-primary mb-4"></i>
                            <h4>Log in to AI-ACADEMY</h4>
                            <p class="text-muted fs-5">Use your Pi Network account to access all features</p>
                        </div>

                        <div class="mb-4">
                            <button id="piLoginBtn" class="btn btn-primary btn-lg px-5 py-3" style="background: linear-gradient(45deg, #2196f3, #21cbf3); border: none; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);">
                                <i class="fab fa-pied-piper me-2"></i>
                                Log in with Pi
                            </button>
                        </div>

                        <div class="mb-4">
                            <button id="verifyWalletBtn" class="btn btn-outline-warning btn-lg px-5 py-3" disabled>
                                <i class="fas fa-shield-alt me-2"></i>
                                Verify Pi Wallet
                            </button>
                        </div>

                        <div class="mb-4">
                            <button id="demoBtn" class="btn btn-outline-success btn-lg px-5 py-3">
                                <i class="fas fa-play-circle me-2"></i>
                                Try Demo Mode
                            </button>
                        </div>

                        <div class="text-center">
                            <div class="auth-status-badges">
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-shield-alt me-1"></i>Sandbox Mode
                                </span>
                                <span class="badge bg-info">
                                    <i class="fas fa-lock me-1"></i>Secure Auth
                                </span>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Demo mode available for testing all features
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p>
                            <a href="<?php echo BASE_PATH; ?>/" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.pi-auth-section {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    border-radius: 10px;
    border: 1px solid #2196f3;
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-primary {
    background: linear-gradient(45deg, #2196f3, #21cbf3);
    border: none;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
}

.alert {
    border-radius: 10px;
    border: none;
}

.fab.fa-pied-piper {
    color: #f4a000;
}

.auth-status-badges {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 10px;
}

.badge {
    font-size: 0.75rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

/* Success animation */
.btn-success {
    animation: successPulse 0.6s ease-in-out;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<script>
// Pi Network Authentication using Pi SDK
document.addEventListener('DOMContentLoaded', function() {
    const piLoginBtn = document.getElementById('piLoginBtn');

    if (piLoginBtn) {
        piLoginBtn.addEventListener('click', function() {
            // Check if Pi SDK is available and properly initialized
            if (typeof Pi === 'undefined') {
                alert('Pi Network authentication is only available in the Pi Browser. Please download and use the Pi Browser to connect your Pi wallet.');
                // Redirect to Pi Browser download
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
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
            this.disabled = true;

            // Authenticate the user, and get permission to request payments from them
            const scopes = ['payments', 'username'];

            // Read more about this callback in the SDK reference:
            function onIncompletePaymentFound(payment) {
                console.log('Incomplete payment found:', payment);
                // Handle incomplete payments if needed
                // For now, just log it
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
                console.log('Pi Network authentication successful:', auth);
                const accessToken = auth.accessToken;

                // Show success state briefly
                piLoginBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
                piLoginBtn.classList.remove('btn-primary');
                piLoginBtn.classList.add('btn-success');

                // Enable wallet verification button after successful login
                const verifyBtn = document.getElementById('verifyWalletBtn');
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-shield-alt me-2"></i>Verify Pi Wallet';
                }

                // Redirect to auth page with access token for backend processing
                setTimeout(function() {
                    window.location.href = '<?php echo BASE_PATH; ?>/auth/?access_token=' + accessToken;
                }, 1000);

            }).catch(function(error) {
                console.error('Pi Network authentication failed:', error);

                // Reset button
                piLoginBtn.innerHTML = originalText;
                piLoginBtn.disabled = false;
                piLoginBtn.classList.remove('btn-success');
                piLoginBtn.classList.add('btn-primary');

                // Show user-friendly error message
                let errorMessage = 'Pi Network authentication failed. ';
                if (error && error.message) {
                    if (error.message.includes('timeout')) {
                        errorMessage = 'Authentication timed out. Please check your internet connection and try again.';
                    } else {
                        errorMessage += error.message;
                    }
                } else {
                    errorMessage += 'Please ensure you are using the Pi Browser and try again.';
                }
                alert(errorMessage);
            });
        });
    }
});

// Verify Wallet Button
document.getElementById('verifyWalletBtn')?.addEventListener('click', function() {
    // Check if user is authenticated first
    <?php if (!isset($_SESSION['user_id'])): ?>
        alert('Please log in with Pi first before verifying your wallet.');
        document.getElementById('piLoginBtn')?.click();
        return;
    <?php else: ?>
        // Enable wallet verification button after login
        document.getElementById('verifyWalletBtn').disabled = false;
    <?php endif; ?>

    // Check if Pi SDK is available
    if (typeof Pi === 'undefined') {
        alert('Pi Network verification requires the Pi Browser. Please download and use the Pi Browser to verify your wallet.');
        window.open('https://minepi.com/browser', '_blank');
        return;
    }

    // Show loading state
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
    btn.disabled = true;

    // Create payment for wallet verification
    const paymentData = {
        amount: <?php echo WALLET_VERIFICATION_FEE; ?>,
        memo: '<?php echo VERIFICATION_MEMO; ?>',
        metadata: {
            user_id: '<?php echo $_SESSION['user_id'] ?? ''; ?>',
            type: 'wallet_verification'
        }
    };

    Pi.createPayment(paymentData, {
        onReadyForServerApproval: function(paymentId) {
            console.log('Payment ready for approval:', paymentId);

            // Send payment ID to server for approval
            fetch('<?php echo BASE_PATH; ?>/api/payments/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    user_id: '<?php echo $_SESSION['user_id'] ?? ''; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Payment approved by server');
                } else {
                    throw new Error(data.error || 'Server approval failed');
                }
            })
            .catch(error => {
                console.error('Server approval error:', error);
                alert('Payment approval failed. Please try again.');
                resetButton();
            });
        },
        onReadyForServerCompletion: function(paymentId, txid) {
            console.log('Payment ready for completion:', paymentId, txid);

            // Complete the payment
            fetch('<?php echo BASE_PATH; ?>/api/payments/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    txid: txid,
                    user_id: '<?php echo $_SESSION['user_id'] ?? ''; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Verified!';
                    btn.classList.remove('btn-outline-warning');
                    btn.classList.add('btn-success');

                    // Redirect after success
                    setTimeout(function() {
                        window.location.href = '<?php echo BASE_PATH; ?>/learnershub/';
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Payment completion failed');
                }
            })
            .catch(error => {
                console.error('Payment completion error:', error);
                alert('Wallet verification failed. Please try again.');
                resetButton();
            });
        },
        onCancel: function(paymentId) {
            console.log('Payment cancelled:', paymentId);
            alert('Wallet verification was cancelled.');
            resetButton();
        },
        onError: function(error, payment) {
            console.error('Payment error:', error, payment);
            alert('Wallet verification failed: ' + (error.message || 'Unknown error'));
            resetButton();
        }
    });

    function resetButton() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-warning');
    }
});

// Demo Mode Button
document.getElementById('demoBtn').addEventListener('click', function() {
    if (confirm('🎮 Welcome to Demo Mode!\n\nYou can explore all AI-ACADEMY features without authentication.\n\n• Access all dashboards and content\n• Try video classrooms\n• Test premium features\n• Experience full functionality\n\nDemo mode expires in 24 hours.\n\nReady to start exploring?')) {
        // Use form submission for better mobile compatibility
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '<?php echo BASE_PATH; ?>/auth/';

        const demoInput = document.createElement('input');
        demoInput.type = 'hidden';
        demoInput.name = 'demo';
        demoInput.value = 'true';

        form.appendChild(demoInput);
        document.body.appendChild(form);
        form.submit();
    }
});
</script>

<?php include '../footer.php'; ?>