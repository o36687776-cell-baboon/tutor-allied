<?php
/**
 * Pi Network Configuration and Settings
 * Handles login, payments, premium features, and wallet verification
 */

// Include database connection
require_once __DIR__ . '/db.php';

// Pi Network SANDBOX Configuration Only
// This application operates exclusively in Pi Network sandbox mode
// No production configuration is maintained
define('PI_SANDBOX_MODE', true); // Application uses sandbox only
define('PI_APP_API_KEY', 'fszajm0vucvmdsw7wwgzspt3uxxcavav1knp1j2xhieby8zk6bh3ynrfc5t1x7z4');

// Sandbox-specific configuration
define('PI_SANDBOX_WEBHOOK_SECRET', 'sandbox_webhook_secret_2024');
define('PI_SANDBOX_ENVIRONMENT', 'testnet');

// Pi Network Sandbox Endpoints (Always)
define('PI_API_BASE_URL', 'https://api.testnet.minepi.com');
define('PI_AUTH_ENDPOINT', PI_API_BASE_URL . '/v2/auth');
define('PI_PAYMENT_ENDPOINT', PI_API_BASE_URL . '/v2/payments');
define('PI_USER_ENDPOINT', PI_API_BASE_URL . '/v2/me');

// Wallet Verification Settings
define('WALLET_VERIFICATION_FEE', 10.0); // 10 Pi coins for verification
define('VERIFICATION_CURRENCY', 'PI');
define('VERIFICATION_MEMO', 'AI-ACADEMY Wallet Verification');

// Premium Tiers Configuration
$PREMIUM_TIERS = [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'features' => [
            'basic_learning',
            'community_reviews',
            'blog_reading',
            'quick_contact',
            'pi_network_info',
            'about_us',
            'privacy_policy'
        ],
        'limits' => [
            'courses_per_month' => 5,
            'forum_posts_per_day' => 3,
            'storage_mb' => 100
        ]
    ],
    'premium_basic' => [
        'name' => 'Premium Basic',
        'price' => 50, // Pi coins per month
        'features' => [
            'certification_requests',
            'personalized_learning_path',
            'basic_deployment_assistance',
            'verified_badge_basic',
            'priority_support',
            'advanced_analytics'
        ],
        'limits' => [
            'courses_per_month' => 20,
            'forum_posts_per_day' => 10,
            'storage_mb' => 1000,
            'certifications_per_month' => 2
        ]
    ],
    'premium_pro' => [
        'name' => 'Premium Pro',
        'price' => 150, // Pi coins per month
        'features' => [
            'certification_requests',
            'personalized_app_development',
            'full_deployment_management',
            'verified_badge_pro',
            'monetization_access',
            'financial_activities',
            'white_label_solutions',
            'api_access',
            'custom_integrations'
        ],
        'limits' => [
            'courses_per_month' => -1, // Unlimited
            'forum_posts_per_day' => -1, // Unlimited
            'storage_mb' => 10000,
            'certifications_per_month' => -1, // Unlimited
            'api_calls_per_hour' => 1000
        ]
    ]
];

// Feature Access Control Matrix
$FEATURE_ACCESS_MATRIX = [
    // Free Features (No verification required)
    'basic_learning' => ['free', 'premium_basic', 'premium_pro'],
    'community_reviews' => ['free', 'premium_basic', 'premium_pro'],
    'blog_reading' => ['free', 'premium_basic', 'premium_pro'],
    'quick_contact' => ['free', 'premium_basic', 'premium_pro'],
    'pi_network_info' => ['free', 'premium_basic', 'premium_pro'],
    'about_us' => ['free', 'premium_basic', 'premium_pro'],
    'privacy_policy' => ['free', 'premium_basic', 'premium_pro'],

    // Premium Features (Require wallet verification)
    'certification_requests' => ['premium_basic', 'premium_pro'],
    'personalized_learning_path' => ['premium_basic', 'premium_pro'],
    'basic_deployment_assistance' => ['premium_basic', 'premium_pro'],
    'verified_badge_basic' => ['premium_basic', 'premium_pro'],
    'personalized_app_development' => ['premium_pro'],
    'full_deployment_management' => ['premium_pro'],
    'verified_badge_pro' => ['premium_pro'],
    'monetization_access' => ['premium_pro'],
    'financial_activities' => ['premium_pro'],
    'white_label_solutions' => ['premium_pro'],
    'api_access' => ['premium_pro'],
    'custom_integrations' => ['premium_pro']
];

// Payment Configuration
$PAYMENT_CONFIG = [
    'currency' => 'PI',
    'minimum_payment' => 0.1,
    'maximum_payment' => 10000,
    'payment_timeout_minutes' => 30,
    'auto_confirm_payments' => true,
    'webhook_secret' => 'your_webhook_secret_here' // To be set during deployment
];

// User Roles and Permissions
$USER_ROLES = [
    'guest' => [
        'permissions' => ['view_public_content'],
        'max_session_time' => 60 // minutes
    ],
    'student' => [
        'permissions' => ['view_public_content', 'enroll_courses', 'post_forum', 'rate_content'],
        'max_session_time' => 480 // 8 hours
    ],
    'tutor' => [
        'permissions' => ['view_public_content', 'create_content', 'manage_students', 'grade_assignments'],
        'max_session_time' => 480 // 8 hours
    ],
    'admin' => [
        'permissions' => ['all'],
        'max_session_time' => 1440 // 24 hours
    ]
];

// Database Tables Configuration (for future use)
$DATABASE_TABLES = [
    'users' => [
        'id', 'username', 'email', 'pi_wallet_address', 'wallet_verified',
        'subscription_tier', 'subscription_expires', 'total_pi_earned',
        'total_pi_spent', 'created_at', 'last_login'
    ],
    'payments' => [
        'id', 'user_id', 'amount', 'currency', 'status', 'pi_payment_id',
        'description', 'created_at', 'completed_at'
    ],
    'certifications' => [
        'id', 'user_id', 'course_id', 'issued_date', 'expires_date',
        'certificate_hash', 'verification_url'
    ],
    'user_activity' => [
        'id', 'user_id', 'activity_type', 'points_earned', 'timestamp'
    ]
];

// Pi Network Integration Functions
class PiNetwork {
    private static $api_key;

    public static function init() {
        self::$api_key = PI_APP_API_KEY;
    }

    public static function authenticateUser($access_token) {
        // Verify token and get user information from Pi Network
        $user_response = self::getUserInfo($access_token);

        if (!$user_response['success']) {
            return [
                'success' => false,
                'error' => 'Failed to get user information from Pi Network'
            ];
        }

        $pi_user = $user_response['data'];

        // Check if user exists in our database
        $existing_user = getUserByPiWallet($pi_user['uid']);

        if (!$existing_user) {
            // Create new user
            $user_id = createUser([
                'username' => $pi_user['username'] ?? 'pi_user_' . substr($pi_user['uid'], -8),
                'pi_wallet_address' => $pi_user['uid'],
                'auth_type' => 'pi'
            ]);

            if (!$user_id) {
                return [
                    'success' => false,
                    'error' => 'Failed to create user account'
                ];
            }

            $user_data = [
                'uid' => $pi_user['uid'],
                'username' => $pi_user['username'] ?? 'pi_user_' . substr($pi_user['uid'], -8),
                'access_token' => $access_token,
                'db_user_id' => $user_id,
                'is_new_user' => true
            ];
        } else {
            // Update existing user
            updateUser($existing_user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            $user_data = [
                'uid' => $pi_user['uid'],
                'username' => $existing_user['username'],
                'access_token' => $access_token,
                'db_user_id' => $existing_user['id'],
                'is_new_user' => false
            ];
        }

        return [
            'success' => true,
            'data' => $user_data
        ];
    }


    private static function getUserInfo($access_token) {
        $url = PI_USER_ENDPOINT;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $http_code >= 200 && $http_code < 300,
            'data' => json_decode($response, true),
            'http_code' => $http_code
        ];
    }


    public static function verifyPayment($payment_id) {
        $url = PI_PAYMENT_ENDPOINT . '/' . $payment_id;
        return self::makeApiCall($url, 'GET');
    }

    private static function makeApiCall($url, $method = 'GET', $data = null) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . self::$api_key,
            'Content-Type: application/json'
        ]);

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $http_code >= 200 && $http_code < 300,
            'data' => json_decode($response, true),
            'http_code' => $http_code
        ];
    }
}

// Initialize Pi Network
PiNetwork::init();

// Feature Access Control Functions
function hasFeatureAccess($user_id, $feature) {
    global $FEATURE_ACCESS_MATRIX;

    // Get user's subscription tier
    $user_tier = getUserTier($user_id);

    // Check if feature is available for user's tier
    return in_array($user_tier, $FEATURE_ACCESS_MATRIX[$feature] ?? []);
}

function getUserTier($user_id) {
    if (!$user_id) return 'free';

    $user = fetchOne("SELECT subscription_tier FROM users WHERE id = ?", [$user_id]);
    return $user ? $user['subscription_tier'] : 'free';
}

function isWalletVerified($user_id) {
    if (!$user_id) return false;

    $user = fetchOne("SELECT wallet_verified FROM users WHERE id = ?", [$user_id]);
    return $user ? (bool)$user['wallet_verified'] : false;
}

function requireWalletVerification($user_id, $feature = null) {
    if (!isWalletVerified($user_id)) {
        // Show verification popup/modal
        showVerificationPrompt($feature);
        return false;
    }
    return true;
}

function showVerificationPrompt($feature = null) {
    $message = "To access " . ($feature ? ucfirst(str_replace('_', ' ', $feature)) : "premium features") .
               ", you need to verify your Pi wallet by paying a one-time fee of " .
               WALLET_VERIFICATION_FEE . " π.";

    echo "<script>
        if (confirm('$message\\n\\nClick OK to proceed with verification.')) {
            window.location.href = '/verify-wallet.php';
        }
    </script>";
}

function getPremiumTiers() {
    global $PREMIUM_TIERS;
    return $PREMIUM_TIERS;
}

function getFeatureMatrix() {
    global $FEATURE_ACCESS_MATRIX;
    return $FEATURE_ACCESS_MATRIX;
}

// User Management Functions
function getUserById($user_id) {
    return fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
}

function getUserByUsername($username) {
    return fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
}

function getUserByEmail($email) {
    return fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
}

function getUserByPiWallet($pi_wallet_address) {
    return fetchOne("SELECT * FROM users WHERE pi_wallet_address = ?", [$pi_wallet_address]);
}

function createUser($data) {
    $sql = "INSERT INTO users (username, email, pi_wallet_address, auth_type, user_role)
            VALUES (?, ?, ?, ?, ?)";
    $params = [
        $data['username'] ?? null,
        $data['email'] ?? null,
        $data['pi_wallet_address'] ?? null,
        $data['auth_type'] ?? 'pi',
        $data['user_role'] ?? 'student'
    ];

    if (executeUpdate($sql, $params)) {
        return getLastInsertId();
    }
    return false;
}

function updateUser($user_id, $data) {
    $fields = [];
    $params = [];

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $params[] = $value;
    }

    $params[] = $user_id;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

    return executeUpdate($sql, $params);
}

function verifyWallet($user_id) {
    return updateUser($user_id, ['wallet_verified' => true]);
}

function updateUserTier($user_id, $tier, $expires = null) {
    $data = ['subscription_tier' => $tier];
    if ($expires) {
        $data['subscription_expires'] = $expires;
    }
    return updateUser($user_id, $data);
}

// Payment Functions
function createPayment($user_id, $amount, $currency = 'PI', $description = '') {
    $sql = "INSERT INTO payments (user_id, amount, currency, description) VALUES (?, ?, ?, ?)";
    if (executeUpdate($sql, [$user_id, $amount, $currency, $description])) {
        return getLastInsertId();
    }
    return false;
}

function updatePaymentStatus($payment_id, $status, $pi_payment_id = null) {
    $data = ['status' => $status];
    if ($pi_payment_id) {
        $data['pi_payment_id'] = $pi_payment_id;
    }
    if ($status === 'completed') {
        $data['completed_at'] = date('Y-m-d H:i:s');
    }

    $sql = "UPDATE payments SET " . implode(' = ?, ', array_keys($data)) . " = ? WHERE id = ?";
    $params = array_values($data);
    $params[] = $payment_id;

    return executeUpdate($sql, $params);
}

function getUserPayments($user_id) {
    return fetchAll("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
}

// Activity Logging
function logUserActivity($user_id, $activity_type, $points = 0, $description = '') {
    $sql = "INSERT INTO user_activity (user_id, activity_type, points_earned, description) VALUES (?, ?, ?, ?)";
    return executeUpdate($sql, [$user_id, $activity_type, $points, $description]);
}

function getUserActivity($user_id, $limit = 50) {
    return fetchAll("SELECT * FROM user_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?", [$user_id, $limit]);
}

// Session Management
function startSecureSession() {
    if (!isset($_SESSION)) {
        session_start();
    }

    // Regenerate session ID for security
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > 1800) {
        // Session started more than 30 minutes ago
        session_regenerate_id(true); // Change session ID for security
        $_SESSION['CREATED'] = time(); // Update creation time
    }
}

// Note: Session initialization is handled in individual PHP files
// to avoid conflicts with session save path settings
?>