<?php
// Course Creation page for Tutors
$page_title = 'Create Course';

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

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'ai';
    $difficulty = $_POST['difficulty'] ?? 'beginner';
    $duration_hours = (int)($_POST['duration_hours'] ?? 0);
    $price_pi = (float)($_POST['price_pi'] ?? 0);
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Title and description are required.';
    } else {
        // Create course (pending approval)
        $sql = "INSERT INTO courses (title, description, instructor_id, category, difficulty, duration_hours, price_pi, tags, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending_approval', NOW())";

        if (executeUpdate($sql, [$title, $description, $_SESSION['user_id'], $category, $difficulty, $duration_hours, $price_pi, $tags])) {
            $success = 'Course created successfully!';
            // Redirect to course list
            header('Location: list.php?success=1');
            exit;
        } else {
            $error = 'Failed to create course. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Course
                    </h3>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Course Title *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                <div class="form-text">Choose a clear, descriptive title for your AI-Pi development course</div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="ai" <?php echo ($_POST['category'] ?? '') === 'ai' ? 'selected' : ''; ?>>AI for Pi Apps</option>
                                    <option value="blockchain" <?php echo ($_POST['category'] ?? '') === 'blockchain' ? 'selected' : ''; ?>>Pi Blockchain</option>
                                    <option value="software" <?php echo ($_POST['category'] ?? '') === 'software' ? 'selected' : ''; ?>>Pi App Development</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Course Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-text">Describe what students will learn and how this course helps them build AI applications for the Pi ecosystem</div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="difficulty" class="form-label">Difficulty Level</label>
                                <select class="form-select" id="difficulty" name="difficulty">
                                    <option value="beginner" <?php echo ($_POST['difficulty'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="intermediate" <?php echo ($_POST['difficulty'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="advanced" <?php echo ($_POST['advanced'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="duration_hours" class="form-label">Duration (hours)</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours"
                                       value="<?php echo htmlspecialchars($_POST['duration_hours'] ?? ''); ?>" min="1">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="price_pi" class="form-label">Price (Pi coins)</label>
                                <input type="number" class="form-control" id="price_pi" name="price_pi" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($_POST['price_pi'] ?? ''); ?>">
                                <div class="form-text">Set to 0 for free courses</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                   placeholder="AI, Pi Network, Machine Learning, Blockchain">
                            <div class="form-text">Comma-separated tags to help students find your course</div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Course Creation Tips:</h6>
                            <ul class="mb-0 small">
                                <li>Focus on practical AI applications for Pi Network development</li>
                                <li>Include hands-on projects and real Pi app examples</li>
                                <li>Set competitive pricing to attract students</li>
                                <li>Use clear tags like "AI", "Pi Network", "dApps", "Smart Contracts"</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="../" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Course
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

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

.alert {
    border-radius: 10px;
    border: none;
}
</style>

<?php include '../../footer.php'; ?>