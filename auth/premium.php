<?php
/**
 * Premium Subscription Page
 * Two-tier premium system with Pi Network payments
 */

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

// Premium page is accessible to guests - authentication checked per action

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

$page_title = 'Premium Subscription';
include 'header.php';

// Get premium tiers
$tiers = getPremiumTiers();
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
$is_authenticated = isset($_SESSION['user_id']) || $is_demo_mode;
$user_id = $is_authenticated ? $_SESSION['user_id'] : null;
$user_tier = $is_authenticated ? getUserTier($user_id) : 'free';
$wallet_verified = $is_authenticated ? isWalletVerified($user_id) : false;

// Handle subscription success
$subscription_success = isset($_GET['subscription']) && $_GET['subscription'] === 'success';

// Handle tier selection from URL
$selected_tier = $_GET['tier'] ?? null;
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="text-center mb-5">
                <h1 class="display-4">Choose Your Premium Plan</h1>
                <p class="lead">
                    <?php if ($is_authenticated): ?>
                        Unlock advanced features and maximize your learning potential with Pi-powered premium access
                    <?php else: ?>
                        Join thousands of learners accessing premium AI education with Pi Network payments
                    <?php endif; ?>
                </p>
                <?php if (!$is_authenticated): ?>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Already have an account?</strong>
                        <a href="<?php echo BASE_PATH; ?>/auth/?redirect=<?php echo BASE_PATH; ?>/premium.php" class="alert-link">Sign in</a> to manage your subscription.
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($subscription_success): ?>
                <div class="alert alert-success text-center mb-4">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <h5>Subscription Successful!</h5>
                    <p>Welcome to Premium! Your subscription is now active.</p>
                    <a href="<?php echo BASE_PATH; ?>/" class="btn btn-success btn-lg">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            <?php elseif (!$wallet_verified): ?>
                <div class="alert alert-warning text-center mb-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h5>Wallet Verification Required</h5>
                    <p>To access premium features, you need to verify your Pi wallet first.</p>
                    <a href="<?php echo BASE_PATH; ?>/verify-wallet.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-shield-alt me-2"></i>Verify Wallet (10 π)
                    </a>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Free Tier -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?php echo $user_tier === 'free' ? 'border-primary' : ''; ?>">
                        <div class="card-header text-center">
                            <h3 class="card-title">Free</h3>
                            <div class="h1 text-primary">0 π</div>
                            <small class="text-muted">Forever</small>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Basic learning access</li>
                                <li><i class="fas fa-check text-success me-2"></i>Community reviews & ratings</li>
                                <li><i class="fas fa-check text-success me-2"></i>Blog reading</li>
                                <li><i class="fas fa-check text-success me-2"></i>Quick contact support</li>
                                <li><i class="fas fa-check text-success me-2"></i>Pi Network information</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Certification requests</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Verified rating badge</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Monetization features</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <?php if ($user_tier === 'free'): ?>
                                <span class="badge bg-primary">Current Plan</span>
                            <?php else: ?>
                                <button class="btn btn-outline-primary btn-sm" onclick="downgradeToFree()">
                                    <i class="fas fa-arrow-down me-1"></i>Downgrade
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Premium Basic -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?php echo $user_tier === 'premium_basic' ? 'border-success' : ''; ?>">
                        <div class="card-header text-center bg-warning text-dark">
                            <h3 class="card-title">Premium Basic</h3>
                            <div class="h1 text-dark"><?php echo $tiers['premium_basic']['price']; ?> π</div>
                            <small class="text-muted">per month</small>
                        </div>
                        <div class="card-body">
                            <h5 class="text-dark mb-3">Everything in Free, plus:</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Certification requests</li>
                                <li><i class="fas fa-check text-success me-2"></i>Personalized learning path</li>
                                <li><i class="fas fa-check text-success me-2"></i>Basic deployment assistance</li>
                                <li><i class="fas fa-check text-success me-2"></i>Verified badge (Basic)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Priority support</li>
                                <li><i class="fas fa-check text-success me-2"></i>Advanced analytics</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Personalized app development</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Full deployment management</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <?php if ($user_tier === 'premium_basic'): ?>
                                <span class="badge bg-success">Current Plan</span>
                            <?php else: ?>
                                <button class="btn btn-warning btn-lg" onclick="subscribe('premium_basic')">
                                    Subscribe Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Premium Pro -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?php echo $user_tier === 'premium_pro' ? 'border-success' : ''; ?>">
                        <div class="card-header text-center bg-danger text-white">
                            <h3 class="card-title">Premium Pro</h3>
                            <div class="h1 text-dark"><?php echo $tiers['premium_pro']['price']; ?> π</div>
                            <small class="text-white">per month</small>
                        </div>
                        <div class="card-body">
                            <h5 class="text-dark mb-3">Everything in Basic, plus:</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Personalized app development</li>
                                <li><i class="fas fa-check text-success me-2"></i>Full deployment management</li>
                                <li><i class="fas fa-check text-success me-2"></i>Verified badge (Pro)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Monetization access</li>
                                <li><i class="fas fa-check text-success me-2"></i>Financial activities</li>
                                <li><i class="fas fa-check text-success me-2"></i>White-label solutions</li>
                                <li><i class="fas fa-check text-success me-2"></i>API access</li>
                                <li><i class="fas fa-check text-success me-2"></i>Custom integrations</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <?php if ($user_tier === 'premium_pro'): ?>
                                <span class="badge bg-danger">Current Plan</span>
                            <?php else: ?>
                                <button class="btn btn-danger btn-lg" onclick="subscribe('premium_pro')">
                                    Subscribe Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Comparison Table -->
            <div class="card mt-5">
                <div class="card-header">
                    <h4 class="mb-0">Detailed Feature Comparison</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>Free</th>
                                    <th>Premium Basic</th>
                                    <th>Premium Pro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Learning Access</td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Community Reviews & Ratings</td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Certification Requests</td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Verified Rating Badge</td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-check text-success"></i> Basic</td>
                                    <td><i class="fas fa-check text-success"></i> Pro</td>
                                </tr>
                                <tr>
                                    <td>Personalized App Development</td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Monetization Features</td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>API Access</td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-times text-muted"></i></td>
                                    <td><i class="fas fa-check text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Premium Subscription Handler
function subscribe(tier) {
    <?php if (!$is_authenticated): ?>
        // Redirect to login for guests
        const loginUrl = '<?php echo BASE_PATH; ?>/auth/?redirect=<?php echo BASE_PATH; ?>/premium.php&tier=' + tier;
        window.location.href = loginUrl;
        return;
    <?php endif; ?>

    <?php if (!$wallet_verified): ?>
        alert('Please verify your Pi wallet first to access premium features.');
        window.location.href = '<?php echo BASE_PATH; ?>/verify-wallet.php';
        return;
    <?php endif; ?>

    const tierData = <?php echo json_encode($tiers); ?>;
    const selectedTier = tierData[tier];

    if (confirm(`Subscribe to ${selectedTier.name} for ${selectedTier.price} π per month?`)) {
        const paymentData = {
            amount: selectedTier.price,
            memo: `${selectedTier.name} Subscription`,
            metadata: {
                user_id: <?php echo $user_id; ?>,
                type: tier
            }
        };

        const paymentCallbacks = {
            onReadyForServerApproval: function(paymentId) {
                console.log("onReadyForServerApproval", paymentId);
                // Send payment ID to server for approval
                fetch('<?php echo BASE_PATH; ?>/api/payments/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ paymentId: paymentId })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Approval failed:', data.error);
                        alert('Payment approval failed. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Approval request failed:', error);
                    alert('An error occurred during payment approval.');
                });
            },
            onReadyForServerCompletion: function(paymentId, txid) {
                console.log("onReadyForServerCompletion", paymentId, txid);
                // Send payment completion to server
                fetch('<?php echo BASE_PATH; ?>/api/payments/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ paymentId: paymentId, txid: txid })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to premium page with success
                        window.location.href = '<?php echo BASE_PATH; ?>/premium.php?subscription=success&tier=' + tier;
                    } else {
                        console.error('Completion failed:', data.error);
                        alert('Payment completion failed. Please contact support.');
                    }
                })
                .catch(error => {
                    console.error('Completion request failed:', error);
                    alert('An error occurred during payment completion.');
                });
            },
            onCancel: function(paymentId) {
                console.log("onCancel", paymentId);
                alert('Payment was cancelled.');
            },
            onError: function(error, payment) {
                console.error("onError", error, payment);
                alert('Payment error: ' + error.message);
            }
        };

        // Create payment using Pi SDK v2.0
        if (typeof Pi !== 'undefined' && Pi.createPayment) {
            Pi.createPayment(paymentData, paymentCallbacks)
                .then(function(payment) {
                    console.log('Pi SDK v2.0 Payment created:', payment);
                })
                .catch(function(error) {
                    console.error('Pi SDK v2.0 Payment creation failed:', error);
                    alert('Failed to create payment: ' + error.message);
                });
        } else {
            alert('Pi Network SDK v2.0 not loaded. Please make sure you are using the Pi Browser.');
        }
    }
}

// Downgrade to free tier
function downgradeToFree() {
    if (confirm('Are you sure you want to downgrade to the Free plan? You will lose access to premium features.')) {
        // For now, just show a message - in production this would make an API call
        alert('Downgrade functionality would be implemented with an API call to update subscription tier.');
    }
}
</script>

<?php include 'footer.php'; ?>