<?php
/**
 * Feature Access Control System
 * Guards premium features and handles access verification
 */

include 'pi_config.php';

/**
 * Check if user has access to a specific feature
 * @param string $feature The feature to check
 * @param int $user_id User ID (optional, uses session if not provided)
 * @return bool True if access granted, false otherwise
 */
function checkFeatureAccess($feature, $user_id = null) {
    if ($user_id === null) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $user_id = $_SESSION['user_id'];
    }

    // Get user's current tier
    $user_tier = getUserTier($user_id);

    // Get feature access matrix
    $feature_matrix = getFeatureMatrix();

    // Check if feature exists and user has access
    if (!isset($feature_matrix[$feature])) {
        return false; // Feature not defined
    }

    return in_array($user_tier, $feature_matrix[$feature]);
}

/**
 * Guard a premium feature - shows verification prompt if needed
 * @param string $feature The feature being accessed
 * @param string $redirect_url Where to redirect after verification (optional)
 */
function guardFeature($feature, $redirect_url = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check wallet verification first
    if (!isWalletVerified($user_id)) {
        showWalletVerificationPrompt($feature, $redirect_url);
        exit;
    }

    // Check feature access
    if (!checkFeatureAccess($feature, $user_id)) {
        showPremiumUpgradePrompt($feature, $redirect_url);
        exit;
    }
}

/**
 * Show wallet verification prompt
 */
function showWalletVerificationPrompt($feature, $redirect_url = null) {
    $feature_name = ucfirst(str_replace('_', ' ', $feature));
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Wallet Verification Required - AI-ACADEMY</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="assets/css/global.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-6">
                    <div class="card shadow-lg">
                        <div class="card-header bg-warning text-dark text-center">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <h4 class="mb-0">Wallet Verification Required</h4>
                        </div>
                        <div class="card-body text-center">
                            <p class="lead">To access <strong><?php echo $feature_name; ?></strong>, you need to verify your Pi wallet.</p>

                            <div class="alert alert-info">
                                <strong>Verification Fee:</strong> <?php echo WALLET_VERIFICATION_FEE; ?> π (one-time payment)
                            </div>

                            <div class="mb-4">
                                <h5>What you'll get:</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Access to premium features</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Verified user status</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Enhanced security</li>
                                </ul>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="/verify-wallet.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>" class="btn btn-warning btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Verify Wallet Now
                                </a>
                                <a href="/" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Show premium upgrade prompt
 */
function showPremiumUpgradePrompt($feature, $redirect_url = null) {
    $feature_name = ucfirst(str_replace('_', ' ', $feature));
    $tiers = getPremiumTiers();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Premium Feature - AI-ACADEMY</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="assets/css/global.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-8">
                    <div class="card shadow-lg">
                        <div class="card-header bg-danger text-white text-center">
                            <i class="fas fa-crown fa-2x mb-2"></i>
                            <h4 class="mb-0">Premium Feature Required</h4>
                        </div>
                        <div class="card-body text-center">
                            <p class="lead"><strong><?php echo $feature_name; ?></strong> is a premium feature.</p>
                            <p>Upgrade to one of our premium plans to unlock this feature and many more!</p>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h5 class="text-warning">Premium Basic</h5>
                                            <div class="h4 text-warning"><?php echo $tiers['premium_basic']['price']; ?> π/month</div>
                                            <p class="small">Perfect for individual learners</p>
                                            <a href="/premium.php" class="btn btn-warning">Choose Basic</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-danger">
                                        <div class="card-body">
                                            <h5 class="text-danger">Premium Pro</h5>
                                            <div class="h4 text-danger"><?php echo $tiers['premium_pro']['price']; ?> π/month</div>
                                            <p class="small">Full access for professionals</p>
                                            <a href="/premium.php" class="btn btn-danger">Choose Pro</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="/" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Get user's current subscription tier
 * In production, this would query the database
 */
function getUserTier($user_id) {
    // For now, return 'free' as default
    // In production, query: SELECT subscription_tier FROM users WHERE id = $user_id
    return 'free';
}

/**
 * Check if user's wallet is verified
 * In production, this would query the database
 */
function isWalletVerified($user_id) {
    // For now, check session
    // In production, query: SELECT wallet_verified FROM users WHERE id = $user_id
    return isset($_SESSION['wallet_verified']) && $_SESSION['wallet_verified'];
}

/**
 * Get feature access matrix
 */
function getFeatureMatrix() {
    global $FEATURE_ACCESS_MATRIX;
    return $FEATURE_ACCESS_MATRIX;
}

/**
 * Get premium tiers configuration
 */
function getPremiumTiers() {
    global $PREMIUM_TIERS;
    return $PREMIUM_TIERS;
}
?>