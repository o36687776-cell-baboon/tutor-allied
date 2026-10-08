<?php
/**
 * Test Feature Access API
 * Tests if user has access to specific features
 */

header('Content-Type: application/json');
include '../feature-guard.php';

$feature = $_GET['feature'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if (!$feature) {
    echo json_encode([
        'success' => false,
        'error' => 'Feature parameter required'
    ]);
    exit;
}

$has_access = checkFeatureAccess($feature, $user_id);
$wallet_verified = isWalletVerified($user_id);
$user_tier = getUserTier($user_id);

$response = [
    'success' => true,
    'feature' => $feature,
    'access' => $has_access,
    'user_tier' => $user_tier,
    'wallet_verified' => $wallet_verified
];

if (!$has_access) {
    $response['reason'] = !$wallet_verified ? 'Wallet not verified' : 'Insufficient subscription tier';
}

echo json_encode($response);
?>