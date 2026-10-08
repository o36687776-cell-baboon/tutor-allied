<?php
// Test database connection and basic operations
include 'db.php';
include 'pi_config.php';

echo "<h1>Database Test</h1>";

// Test connection
try {
    $stmt = $db->query("SELECT 1");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test user creation
$user_id = createUser([
    'username' => 'testuser_' . time(),
    'email' => 'test' . time() . '@example.com',
    'auth_type' => 'traditional'
]);

if ($user_id) {
    echo "<p style='color: green;'>✓ User creation successful (ID: $user_id)</p>";

    // Test user retrieval
    $user = getUserById($user_id);
    if ($user) {
        echo "<p style='color: green;'>✓ User retrieval successful: " . htmlspecialchars($user['username']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ User retrieval failed</p>";
    }

    // Test blog creation
    $blog_id = executeUpdate(
        "INSERT INTO blogs (user_id, title, content, status) VALUES (?, ?, ?, ?)",
        [$user_id, 'Test Blog', 'This is a test blog post.', 'published']
    );

    if ($blog_id) {
        echo "<p style='color: green;'>✓ Blog creation successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Blog creation failed</p>";
    }

} else {
    echo "<p style='color: red;'>✗ User creation failed</p>";
}

// Test blog retrieval
$blogs = fetchAll("SELECT COUNT(*) as count FROM blogs");
if ($blogs) {
    echo "<p style='color: green;'>✓ Blog count query successful: " . $blogs[0]['count'] . " blogs</p>";
} else {
    echo "<p style='color: red;'>✗ Blog query failed</p>";
}

echo "<p><strong>Test completed.</strong></p>";
?>