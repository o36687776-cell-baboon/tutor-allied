<?php
// Course List page for Tutors
$page_title = 'My Courses';

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

include '../../header.php';

// Check if user is authenticated and is a tutor
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'student';
if ($user_role !== 'tutor' && $user_role !== 'admin') {
    header('Location: ' . BASE_PATH . '/?error=Access restricted to tutors only');
    exit;
}

// Get tutor's courses
$courses = fetchAll("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);

// Get enrollment stats for each course
$course_stats = [];
foreach ($courses as $course) {
    $enrollments = fetchOne("SELECT COUNT(*) as count FROM course_enrollments WHERE course_id = ?", [$course['id']]);
    $course_stats[$course['id']] = $enrollments['count'];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My AI-Pi Development Courses</h2>
            <p class="text-muted">Manage your courses and track student enrollment</p>
        </div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Create New Course
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Course created successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (count($courses) > 0): ?>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($course['category']); ?></span>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 150)); ?>...</p>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Difficulty</small><br>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Duration</small><br>
                                    <span><?php echo $course['duration_hours']; ?> hours</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Price</small><br>
                                    <span class="fw-bold text-warning">
                                        <i class="fas fa-circle me-1"></i>
                                        <?php echo $course['price_pi']; ?> π
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Students</small><br>
                                    <span class="fw-bold text-success">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $course_stats[$course['id']] ?? 0; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($course['tags']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Tags:</small><br>
                                    <?php
                                    $tags = explode(',', $course['tags']);
                                    foreach ($tags as $tag): ?>
                                        <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Created: <?php echo date('M j, Y', strtotime($course['created_at'])); ?>
                                </small>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-2" onclick="editCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-book fa-4x mb-4 text-muted"></i>
            <h3 class="text-muted">No Courses Yet</h3>
            <p class="text-muted mb-4">Start teaching by creating your first AI-Pi development course</p>
            <a href="create.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus-circle me-2"></i>Create Your First Course
            </a>
        </div>
    <?php endif; ?>

    <!-- Course Statistics -->
    <?php if (count($courses) > 0): ?>
        <div class="card mt-4 shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Course Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-primary"><?php echo count($courses); ?></h3>
                        <p class="mb-0">Total Courses</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success"><?php echo array_sum($course_stats); ?></h3>
                        <p class="mb-0">Total Enrollments</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">
                            <?php
                            $total_earnings = 0;
                            foreach ($courses as $course) {
                                $enrollments = $course_stats[$course['id']] ?? 0;
                                $total_earnings += $enrollments * $course['price_pi'];
                            }
                            echo $total_earnings;
                            ?>
                        </h3>
                        <p class="mb-0">Pi Coins Earned</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info">
                            <?php
                            $avg_rating = 4.5; // Placeholder - would calculate from actual ratings
                            echo number_format($avg_rating, 1);
                            ?>
                        </h3>
                        <p class="mb-0">Avg Rating</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function editCourse(courseId) {
    // Redirect to edit page (would need to create edit.php)
    alert('Edit functionality coming soon! Course ID: ' + courseId);
}

function deleteCourse(courseId, courseTitle) {
    if (confirm('Are you sure you want to delete "' + courseTitle + '"?\n\nThis action cannot be undone and will remove all student enrollments.')) {
        // Would need API endpoint for deletion
        alert('Delete functionality would be implemented here.\n\nIn a real application, this would:\n- Remove the course\n- Refund enrolled students\n- Update tutor earnings\n- Notify affected students');
    }
}
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php include '../../footer.php'; ?>