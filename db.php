<?php
/**
 * Database Connection Configuration
 * Uses PDO for secure database operations
 */

// Database configuration for production
// Update these values for your Lightsail Bitnami LAMP stack
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1'); // Use environment variable or default
define('DB_NAME', getenv('DB_NAME') ?: 'ai_academy');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: ''); // Set this to your actual database password
define('DB_CHARSET', 'utf8mb4');

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // In production, log this error instead of displaying
    die("Database connection failed: " . $e->getMessage());
}

// Global database connection variable
$db = $pdo;

/**
 * Helper function to get database connection
 */
function getDB() {
    global $db;
    return $db;
}

/**
 * Helper function to execute prepared statements
 */
function executeQuery($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper function for SELECT queries
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Helper function for single row SELECT
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

/**
 * Helper function for INSERT/UPDATE/DELETE
 */
function executeUpdate($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->rowCount() : 0;
}

/**
 * Get last inserted ID
 */
function getLastInsertId() {
    global $db;
    return $db->lastInsertId();
}
?>