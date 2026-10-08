<?php
// Setup database script
echo "<h1>Database Setup</h1>";

// Connect without database first
try {
    $pdo = new PDO(
        "mysql:host=localhost",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ MySQL connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ MySQL connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ai_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database 'ai_academy' created</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database creation failed: " . $e->getMessage() . "</p>";
    exit;
}

// Select database
$pdo->exec("USE ai_academy");

// Read and execute schema
$schema = file_get_contents('db_schema.sql');

// Remove CREATE DATABASE and USE statements
$schema = preg_replace('/CREATE DATABASE.*;/i', '', $schema);
$schema = preg_replace('/USE.*;/i', '', $schema);

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $schema)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $pdo->exec($statement);
            $success_count++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error executing: " . substr($statement, 0, 50) . "... - " . $e->getMessage() . "</p>";
            $error_count++;
        }
    }
}

echo "<p style='color: green;'>✓ $success_count statements executed successfully</p>";
if ($error_count > 0) {
    echo "<p style='color: red;'>✗ $error_count statements failed</p>";
} else {
    echo "<p style='color: green;'>✓ Database setup completed successfully!</p>";
}
?>