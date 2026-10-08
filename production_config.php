<?php
/**
 * Production Configuration for AI-ACADEMY
 * This file contains production-specific settings for Lightsail deployment
 */

// Environment detection
define('IS_PRODUCTION', getenv('APP_ENV') === 'production' || isset($_SERVER['HTTPS']));
define('PRODUCTION_URL', 'https://pi.homework-help.online');

// Database Configuration for Bitnami LAMP Stack
// Update these values based on your Bitnami LAMP stack configuration
define('PROD_DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('PROD_DB_NAME', getenv('DB_NAME') ?: 'ai_academy');
define('PROD_DB_USER', getenv('DB_USER') ?: 'root'); // Default Bitnami MySQL user
define('PROD_DB_PASS', getenv('DB_PASS') ?: 'Imkw=WOl==S5'); // Set this to your actual database password

// Pi Network Configuration - SANDBOX ONLY
// This application uses Pi Network sandbox for all operations
// No production Pi Network configuration is maintained
define('PI_SANDBOX_ONLY', true); // Application operates in sandbox mode only

// Session Configuration for Production
define('PROD_SESSION_SECURE', true); // Always use secure cookies in production
define('PROD_SESSION_HTTPONLY', true);
define('PROD_SESSION_SAMESITE', 'Lax');
define('PROD_SESSION_LIFETIME', 3600); // 1 hour

// File Upload Configuration
define('PROD_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('PROD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Email Configuration (if needed)
// define('SMTP_HOST', 'your-smtp-host');
// define('SMTP_PORT', 587);
// define('SMTP_USER', 'your-email@domain.com');
// define('SMTP_PASS', 'your-email-password');

// Security Headers for Production
define('PROD_SECURITY_HEADERS', [
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'X-Frame-Options' => 'ALLOW-FROM https://minepi.com',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://sdk.minepi.com https://cdnjs.cloudflare.com https://static.cloudflareinsights.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' wss: https://api.minepi.com https://cdn.jsdelivr.net; media-src 'self' blob:; object-src 'none'; style-src-attr 'unsafe-inline'; frame-ancestors 'self' https://*.minepi.com;"
]);

// Error Reporting for Production
if (IS_PRODUCTION) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Create logs directory if it doesn't exist
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Function to apply production settings
function applyProductionSettings() {
    if (!IS_PRODUCTION) return;

    // Set security headers
    foreach (PROD_SECURITY_HEADERS as $header => $value) {
        header("$header: $value");
    }

    // Session settings - only if session is not active
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_secure', PROD_SESSION_SECURE);
        ini_set('session.cookie_httponly', PROD_SESSION_HTTPONLY);
        ini_set('session.cookie_samesite', PROD_SESSION_SAMESITE);
        ini_set('session.gc_maxlifetime', PROD_SESSION_LIFETIME);
        ini_set('session.use_strict_mode', 1);
    }

    // File upload settings
    ini_set('upload_max_filesize', PROD_MAX_FILE_SIZE / 1024 / 1024 . 'M');
    ini_set('post_max_size', (PROD_MAX_FILE_SIZE / 1024 / 1024 + 1) . 'M');
}

// Apply production settings
applyProductionSettings();
?>