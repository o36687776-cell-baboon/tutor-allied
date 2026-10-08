<?php
/**
 * Pi Network Webhook Handler
 * Handles payment confirmations and other Pi Network webhooks
 */

header('Content-Type: application/json');

// Include configuration
require_once '../db.php';
require_once '../pi_config.php';

// Get webhook data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Log webhook for debugging
error_log('Pi Network Webhook: ' . json_encode($data));

// Verify webhook signature if configured
// Note: In production, implement webhook signature verification
// if (!verifyWebhookSignature($input, $_SERVER['HTTP_X_PI_SIGNATURE'] ?? '')) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Invalid signature']);
//     exit;
// }

// Handle different webhook types
switch ($data['type'] ?? '') {
    case 'payment.completed':
        handlePaymentCompleted($data['data']);
        break;

    case 'payment.cancelled':
        handlePaymentCancelled($data['data']);
        break;

    default:
        error_log('Unknown webhook type: ' . ($data['type'] ?? 'none'));
        break;
}

echo json_encode(['status' => 'ok']);

function handlePaymentCompleted($payment_data) {
    $pi_payment_id = $payment_data['payment_id'] ?? '';

    if (empty($pi_payment_id)) {
        error_log('Payment completed webhook missing payment_id');
        return;
    }

    // Find payment in database
    $payment = fetchOne("SELECT * FROM payments WHERE pi_payment_id = ?", [$pi_payment_id]);

    if (!$payment) {
        error_log('Payment not found for Pi payment ID: ' . $pi_payment_id);
        return;
    }

    // Update payment status
    updatePaymentStatus($payment['id'], 'completed', $pi_payment_id);

    // Handle specific payment types
    $description = strtolower($payment['description']);

    if (strpos($description, 'verification') !== false) {
        // Wallet verification payment
        verifyWallet($payment['user_id']);
        logUserActivity($payment['user_id'], 'wallet_verified', 100, 'Pi wallet verified with payment');
    } elseif (strpos($description, 'premium') !== false) {
        // Premium subscription payment
        $tier = determineTierFromAmount($payment['amount']);
        if ($tier) {
            $expires = date('Y-m-d H:i:s', strtotime('+30 days')); // 30 days subscription
            updateUserTier($payment['user_id'], $tier, $expires);
            logUserActivity($payment['user_id'], 'premium_subscribed', 50, "Subscribed to $tier tier");
        }
    }

    // Log successful payment
    logUserActivity($payment['user_id'], 'payment_completed', 10, 'Payment of ' . $payment['amount'] . ' Pi completed');
}

function handlePaymentCancelled($payment_data) {
    $pi_payment_id = $payment_data['payment_id'] ?? '';

    if (empty($pi_payment_id)) {
        error_log('Payment cancelled webhook missing payment_id');
        return;
    }

    // Find and update payment status
    $payment = fetchOne("SELECT * FROM payments WHERE pi_payment_id = ?", [$pi_payment_id]);

    if ($payment) {
        updatePaymentStatus($payment['id'], 'cancelled', $pi_payment_id);
        logUserActivity($payment['user_id'], 'payment_cancelled', 0, 'Payment cancelled');
    }
}

function determineTierFromAmount($amount) {
    if ($amount >= 150) return 'premium_pro';
    if ($amount >= 50) return 'premium_basic';
    return null;
}

function verifyWebhookSignature($payload, $signature) {
    // Implement webhook signature verification
    // This should use your webhook secret configured in Pi Network
    $expected_signature = hash_hmac('sha256', $payload, PI_WEBHOOK_SECRET ?? '');

    return hash_equals($expected_signature, $signature);
}
?>