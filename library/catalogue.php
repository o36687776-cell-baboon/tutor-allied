<?php
// Start session first - Fix session path for XAMPP
$session_path = __DIR__ . '/../sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
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

// Only set session save path if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($session_path);
    session_start();
} elseif (!isset($_SESSION)) {
    // If session is already active but $_SESSION is not set, start it anyway
    session_start();
}

// Check user authentication BEFORE including header
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Library Catalogue for AI-ACADEMY
$page_title = 'Library Catalogue';
include '../header.php';

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build query for library resources
$query = "SELECT * FROM library_resources WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY created_at DESC";

// Handle demo mode vs real database queries
if ($is_demo_mode) {
    // Demo mode: Provide mock data
    $resources = [
        [
            'id' => 1,
            'title' => 'Introduction to AI Programming',
            'description' => 'Learn the basics of AI programming with practical examples',
            'type' => 'video',
            'category' => 'ai',
            'tags' => 'AI, Programming, Basics',
            'thumbnail_url' => 'https://via.placeholder.com/200?text=AI+Programming',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ],
        [
            'id' => 2,
            'title' => 'Pi Network Smart Contracts',
            'description' => 'Build smart contracts for the Pi Network ecosystem',
            'type' => 'document',
            'category' => 'blockchain',
            'tags' => 'Pi Network, Smart Contracts, Blockchain',
            'thumbnail_url' => 'https://via.placeholder.com/200?text=Smart+Contracts',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ]
    ];

    $courses = [
        [
            'id' => 1,
            'title' => 'AI Development Fundamentals',
            'description' => 'Master the core concepts of AI development',
            'category' => 'ai',
            'difficulty' => 'beginner',
            'price_pi' => 25.0,
            'instructor_name' => 'Demo Instructor'
        ],
        [
            'id' => 2,
            'title' => 'Blockchain for Developers',
            'description' => 'Learn blockchain development from scratch',
            'category' => 'blockchain',
            'difficulty' => 'intermediate',
            'price_pi' => 50.0,
            'instructor_name' => 'Demo Instructor'
        ]
    ];
} else {
    // Real database queries
    $resources = fetchAll($query, $params);

    // Get approved courses
    $courses_query = "SELECT c.*, u.username as instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.id WHERE c.status = 'approved'";
    $courses_params = [];

    if ($search) {
        $courses_query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.tags LIKE ?)";
        $courses_params = array_merge($courses_params, [$search_param, $search_param, $search_param]);
    }

    if ($category) {
        $courses_query .= " AND c.category = ?";
        $courses_params[] = $category;
    }

    $courses_query .= " ORDER BY c.created_at DESC";
    $courses = fetchAll($courses_query, $courses_params);
}
?>

<div class="container">
    <h2>Library Catalogue</h2>

    <!-- Search Bar -->
    <div class="widget">
        <form method="get" action="">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search resources..." value="<?php echo $_GET['search'] ?? ''; ?>">
                <button class="btn" type="submit">Search</button>
            </div>
        </form>
    </div>

    <!-- Categories -->
    <div class="widget">
        <h3>Categories</h3>
        <div class="row">
            <div class="col-md-3"><a href="?category=ai" class="btn btn-outline-primary">AI</a></div>
            <div class="col-md-3"><a href="?category=blockchain" class="btn btn-outline-primary">Blockchain</a></div>
            <div class="col-md-3"><a href="?category=software" class="btn btn-outline-primary">Software Dev</a></div>
            <div class="col-md-3"><a href="?category=other" class="btn btn-outline-primary">Other</a></div>
        </div>
    </div>

    <!-- Resources -->
    <div class="widget">
        <h3>Available Resources <?php if ($search): ?><small class="text-muted">(Search: "<?php echo htmlspecialchars($search); ?>")</small><?php endif; ?></h3>
        <?php if (count($resources) > 0): ?>
            <div class="row">
                <?php foreach ($resources as $resource): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="<?php echo $resource['thumbnail_url'] ?: 'https://via.placeholder.com/200?text=' . urlencode($resource['type']); ?>"
                                 class="card-img-top" alt="<?php echo htmlspecialchars($resource['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($resource['description'], 0, 100)); ?>...</p>
                                <div class="mb-2">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($resource['type']); ?></span>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($resource['category']); ?></span>
                                    <?php if ($resource['is_premium']): ?>
                                        <span class="badge bg-warning">Premium</span>
                                    <?php endif; ?>
                                </div>
                                <a href="details.php?id=<?php echo $resource['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php if ($resource['is_premium']): ?>
                                    <span class="badge bg-warning ms-2">Premium</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                <p class="text-muted">No resources found<?php if ($search): ?> for "<?php echo htmlspecialchars($search); ?>"<?php endif; ?>.</p>
                <?php if ($search): ?>
                    <a href="catalogue.php" class="btn btn-outline-primary">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Courses Section -->
    <div class="widget">
        <h3>Courses <?php if ($search): ?><small class="text-muted">(Search: "<?php echo htmlspecialchars($search); ?>")</small><?php endif; ?></h3>
        <?php if (count($courses) > 0): ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                                <div class="mb-2">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['category']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                    <?php if ($course['price_pi'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo number_format($course['price_pi'], 2); ?> π</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">FREE</span>
                                    <?php endif; ?>
                                </div>
                                <p class="small text-muted mb-2">By <?php echo htmlspecialchars($course['instructor_name'] ?? 'AI-ACADEMY'); ?></p>
                                <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">Enroll Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-graduation-cap fa-3x mb-3 text-muted"></i>
                <p class="text-muted">No courses found<?php if ($search): ?> for "<?php echo htmlspecialchars($search); ?>"<?php endif; ?>.</p>
                <?php if ($search): ?>
                    <a href="catalogue.php" class="btn btn-outline-primary">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>