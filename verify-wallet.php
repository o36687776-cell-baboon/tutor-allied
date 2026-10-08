<?php
/**
 * Pi Wallet Verification Page
 * Handles the 10 Pi payment for wallet verification
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

// Check user authentication BEFORE including header
// Allow demo mode users
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
if (!$is_demo_mode && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
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

$page_title = 'Verify Pi Wallet';
include 'header.php';

$user_id = $_SESSION['user_id'];
$verification_fee = WALLET_VERIFICATION_FEE;

// Handle payment completion
if (isset($_GET['payment_id'])) {
    $pi_payment_id = $_GET['payment_id'];

    // Find payment in database
    $payment = fetchOne("SELECT * FROM payments WHERE pi_payment_id = ? AND user_id = ?", [$pi_payment_id, $user_id]);

    if ($payment && $payment['status'] === 'completed') {
        // Payment already completed via webhook
        $success_message = "Wallet verification successful! You now have access to premium features.";
    } elseif ($payment && $payment['status'] === 'pending') {
        // Check payment status with Pi Network
        $payment_result = PiNetwork::verifyPayment($pi_payment_id);

        if ($payment_result['success'] && isset($payment_result['data']['status']) && $payment_result['data']['status'] === 'completed') {
            // Update payment status and verify wallet
            updatePaymentStatus($payment['id'], 'completed', $pi_payment_id);
            verifyWallet($user_id);
            logUserActivity($user_id, 'wallet_verified', 100, 'Pi wallet verified via payment');

            $success_message = "Wallet verification successful! You now have access to premium features.";
        } else {
            $error_message = "Payment verification failed. Please try again.";
        }
    } else {
        $error_message = "Payment not found. Please try again.";
    }
}

// Check if already verified
$user = getUserById($user_id);
if ($user && $user['wallet_verified']) {
    $already_verified = true;
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Pi Wallet Verification
                    </h3>
                </div>
                <div class="card-body">

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                        </div>
                        <div class="text-center">
                            <a href="<?php echo BASE_PATH; ?>/" class="btn btn-success btn-lg">Continue to Dashboard</a>
                        </div>
                    <?php elseif (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php elseif (isset($already_verified) && $already_verified): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Your Pi wallet is already verified! You have full access to premium features.
                        </div>
                        <div class="text-center">
                            <a href="<?php echo BASE_PATH; ?>/" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        </div>
                    <?php else: ?>

                        <div class="text-center mb-4">
                            <i class="fas fa-wallet fa-4x text-warning mb-3"></i>
                            <h4>Verify Your Pi Wallet</h4>
                            <p class="text-muted">Complete verification to unlock premium features and monetization capabilities</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h5 class="card-title text-warning">What You Get:</h5>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i> Access to premium courses</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Certification requests</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Verified rating badge</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Monetization features</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Priority support</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Advanced analytics</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">Verification Fee:</h5>
                                        <div class="text-center">
                                            <div class="display-4 text-primary fw-bold"><?php echo $verification_fee; ?> π</div>
                                            <small class="text-muted">One-time payment</small>
                                        </div>
                                        <hr>
                                        <p class="small text-muted">
                                            This fee helps maintain platform security and ensures only genuine Pi Network users can access premium features.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>How it works:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Click "Verify Wallet" to initiate payment</li>
                                <li>Complete the payment in your Pi Browser</li>
                                <li>Return here for instant verification</li>
                                <li>Enjoy full access to premium features!</li>
                            </ol>
                        </div>

                        <div class="text-center">
                            <button id="verifyWalletBtn" class="btn btn-warning btn-lg px-5">
                                <i class="fas fa-credit-card me-2"></i>
                                Verify Wallet (<?php echo $verification_fee; ?> π)
                            </button>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pi Wallet Verification using Pi SDK - Enhanced Sandbox Flow
document.getElementById('verifyWalletBtn')?.addEventListener('click', function() {
    // Check if Pi SDK is available (only in Pi Browser)
    if (typeof Pi === 'undefined') {
        alert('Pi Network authentication is only available in the Pi Browser. Please download and use the Pi Browser to verify your wallet.');
        return;
    }

    // Show loading state
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Payment...';
    btn.disabled = true;

    const paymentData = {
        amount: <?php echo $verification_fee; ?>,
        memo: '<?php echo VERIFICATION_MEMO; ?>',
        metadata: {
            user_id: <?php echo $user_id; ?>,
            type: 'verification',
            timestamp: Date.now(),
            sandbox: true
        }
    };

    const paymentCallbacks = {
        onReadyForServerApproval: function(paymentId) {
            console.log("🔄 Payment ready for server approval:", paymentId);

            // Update UI
            btn.innerHTML = '<i class="fas fa-clock me-2"></i>Approving Payment...';

            // Send payment ID to server for approval
            fetch('<?php echo BASE_PATH; ?>/api/payments/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    paymentId: paymentId,
                    user_id: <?php echo $user_id; ?>,
                    type: 'verification'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("✅ Payment approved by server");
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Payment Approved - Complete in Pi Browser';
                } else {
                    console.error('❌ Approval failed:', data.error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Payment approval failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('❌ Approval request failed:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Network error during payment approval. Please check your connection and try again.');
            });
        },

        onReadyForServerCompletion: function(paymentId, txid) {
            console.log("🔄 Payment ready for server completion:", paymentId, txid);

            // Update UI
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Completing Payment...';

            // Send payment completion to server
            fetch('<?php echo BASE_PATH; ?>/api/payments/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    paymentId: paymentId,
                    txid: txid,
                    user_id: <?php echo $user_id; ?>,
                    type: 'verification'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("✅ Payment completed successfully");
                    // Show success message and redirect
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verification Complete!';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_PATH; ?>/verify-wallet.php?payment_id=' + paymentId;
                    }, 1500);
                } else {
                    console.error('❌ Completion failed:', data.error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Payment completion failed: ' + (data.error || 'Please contact support'));
                }
            })
            .catch(error => {
                console.error('❌ Completion request failed:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Network error during payment completion. Please check your connection and try again.');
            });
        },

        onCancel: function(paymentId) {
            console.log("❌ Payment cancelled by user:", paymentId);
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Payment was cancelled. You can try again when ready.');
        },

        onError: function(error, payment) {
            console.error("❌ Payment error:", error, payment);
            btn.innerHTML = originalText;
            btn.disabled = false;

            let errorMessage = 'Payment error occurred.';
            if (error && error.message) {
                errorMessage += ' Details: ' + error.message;
            }
            alert(errorMessage + ' Please try again or contact support if the problem persists.');
        }
    };

    // Create payment using Pi SDK
    console.log("🚀 Creating Pi Network payment for wallet verification...");
    Pi.createPayment(paymentData, paymentCallbacks)
        .then(function(payment) {
            console.log("✅ Payment created successfully:", payment);
            // UI updates are handled in callbacks
        })
        .catch(function(error) {
            console.error('❌ Payment creation failed:', error);
            btn.innerHTML = originalText;
            btn.disabled = false;

            let errorMessage = 'Failed to create payment.';
            if (error && error.message) {
                errorMessage += ' Details: ' + error.message;
            }
            alert(errorMessage + ' Please try again or contact support if the problem persists.');
        });
});

// Add visual feedback for sandbox environment
document.addEventListener('DOMContentLoaded', function() {
    // Add sandbox indicator
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader) {
        const sandboxBadge = document.createElement('span');
        sandboxBadge.className = 'badge bg-info ms-2';
        sandboxBadge.innerHTML = '<i class="fas fa-flask me-1"></i>Sandbox';
        cardHeader.appendChild(sandboxBadge);
    }

    // Add Pi Network info
    const infoAlert = document.querySelector('.alert-info');
    if (infoAlert) {
        const piInfo = document.createElement('div');
        piInfo.className = 'mt-3 pt-3 border-top';
        piInfo.innerHTML = `
            <strong>Pi Network Sandbox Environment:</strong>
            <ul class="mb-0 mt-2">
                <li>This is a test environment - no real Pi coins are used</li>
                <li>Payments are processed through Pi Network's sandbox API</li>
                <li>Verification is instant and grants access to premium features</li>
                <li>All transactions are logged for development purposes</li>
            </ul>
        `;
        infoAlert.appendChild(piInfo);
    }
});
</script>

<?php include 'footer.php'; ?>