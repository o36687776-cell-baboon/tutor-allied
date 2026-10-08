<?php
// Request Tutor page for Students
$page_title = 'Request Tutor Session';

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

include '../header.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'student';
if ($user_role !== 'student' && $user_role !== 'admin') {
    header('Location: ' . BASE_PATH . '/?error=This feature is for students only');
    exit;
}

// Get available tutors
$tutors = fetchAll("
    SELECT u.id, u.username, u.email,
           COUNT(c.id) as course_count,
           COALESCE(u.total_pi_earned, 0) as earnings,
           COUNT(DISTINCT ce.user_id) as student_count
    FROM users u
    LEFT JOIN courses c ON u.id = c.instructor_id
    LEFT JOIN course_enrollments ce ON c.id = ce.course_id
    WHERE u.user_role = 'tutor'
    GROUP BY u.id, u.username, u.email, u.total_pi_earned
    ORDER BY course_count DESC, earnings DESC
");

// Handle tutor request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tutor_id = (int)($_POST['tutor_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $preferred_time = $_POST['preferred_time'] ?? '';
    $duration_hours = (int)($_POST['duration_hours'] ?? 1);

    if (!$tutor_id || empty($subject) || empty($topic) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if tutor exists and is active
        $tutor = getUserById($tutor_id);
        if (!$tutor || $tutor['user_role'] !== 'tutor') {
            $error = 'Selected tutor is not available.';
        } else {
            // Create tutor request (could be stored in a new table or as a message)
            $request_data = [
                'student_id' => $_SESSION['user_id'],
                'tutor_id' => $tutor_id,
                'subject' => $subject,
                'topic' => $topic,
                'description' => $description,
                'preferred_time' => $preferred_time,
                'duration_hours' => $duration_hours,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // For now, store as a message to the tutor
            $message_content = "Tutor Session Request:\n\n" .
                              "Subject: $subject\n" .
                              "Topic: $topic\n" .
                              "Description: $description\n" .
                              "Preferred Time: $preferred_time\n" .
                              "Duration: $duration_hours hour(s)\n\n" .
                              "Please contact me to arrange the session.";

            $sql = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
            if (executeUpdate($sql, [$_SESSION['user_id'], $tutor_id, "Tutor Session Request: $subject", $message_content])) {
                $success = 'Your tutor session request has been sent! The tutor will contact you soon.';
            } else {
                $error = 'Failed to send request. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Request AI-Pi Development Tutor
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

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>How Tutor Sessions Work:</h6>
                        <ul class="mb-0 small">
                            <li>Select a tutor specializing in AI for Pi Network development</li>
                            <li>Specify your learning needs and preferred schedule</li>
                            <li>The tutor will review your request and contact you</li>
                            <li>Sessions are paid in Pi coins and conducted via video call</li>
                            <li>Get personalized guidance for your Pi app development projects</li>
                        </ul>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="tutor_id" class="form-label">Select Tutor *</label>
                            <select class="form-select" id="tutor_id" name="tutor_id" required>
                                <option value="">Choose a tutor...</option>
                                <?php foreach ($tutors as $tutor): ?>
                                    <option value="<?php echo $tutor['id']; ?>">
                                        <?php echo htmlspecialchars($tutor['username']); ?>
                                        (<?php echo $tutor['course_count']; ?> courses,
                                        <?php echo $tutor['student_count']; ?> students taught,
                                        <?php echo $tutor['earnings']; ?> π earned)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject Area *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select subject...</option>
                                    <option value="AI for Pi Apps">AI for Pi Apps</option>
                                    <option value="Pi Blockchain Development">Pi Blockchain Development</option>
                                    <option value="Pi Smart Contracts">Pi Smart Contracts</option>
                                    <option value="Pi dApp Architecture">Pi dApp Architecture</option>
                                    <option value="Machine Learning on Pi">Machine Learning on Pi</option>
                                    <option value="Pi API Integration">Pi API Integration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="topic" class="form-label">Specific Topic *</label>
                                <input type="text" class="form-control" id="topic" name="topic"
                                       placeholder="e.g., Building AI chatbots for Pi" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">What do you need help with? *</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Describe your current project, what you're trying to build, or what concepts you need help understanding..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="preferred_time" class="form-label">Preferred Time</label>
                                <input type="datetime-local" class="form-control" id="preferred_time" name="preferred_time">
                                <div class="form-text">When would you like to have the session?</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_hours" class="form-label">Session Duration</label>
                                <select class="form-select" id="duration_hours" name="duration_hours">
                                    <option value="1">1 hour</option>
                                    <option value="2">2 hours</option>
                                    <option value="3">3 hours</option>
                                    <option value="4">4 hours</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                            <ul class="mb-0 small">
                                <li>Tutors will respond within 24-48 hours</li>
                                <li>Session pricing varies by tutor expertise and duration</li>
                                <li>Payment is made directly to the tutor in Pi coins</li>
                                <li>Sessions are conducted via video call (link provided by tutor)</li>
                                <li>Be specific about your needs for better tutor matching</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/learnershub/" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Request
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Available Tutors -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Available Tutors</h5>
                </div>
                <div class="card-body">
                    <?php if (count($tutors) > 0): ?>
                        <?php foreach ($tutors as $tutor): ?>
                            <div class="tutor-card mb-3 p-3 border rounded">
                                <h6 class="text-primary"><?php echo htmlspecialchars($tutor['username']); ?></h6>
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-book me-1"></i><?php echo $tutor['course_count']; ?> courses<br>
                                    <i class="fas fa-users me-1"></i><?php echo $tutor['student_count']; ?> students<br>
                                    <i class="fas fa-circle text-warning me-1"></i><?php echo $tutor['earnings']; ?> π earned
                                </div>
                                <button class="btn btn-sm btn-outline-primary w-100"
                                        onclick="selectTutor(<?php echo $tutor['id']; ?>, '<?php echo htmlspecialchars($tutor['username']); ?>')">
                                    Select Tutor
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No tutors available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips for Success</h5>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Be specific about your project goals</li>
                        <li>Mention your current skill level</li>
                        <li>Include relevant code examples if possible</li>
                        <li>Check tutor availability before requesting</li>
                        <li>Prepare questions in advance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectTutor(tutorId, tutorName) {
    document.getElementById('tutor_id').value = tutorId;
    // Scroll to form
    document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
    // Show selected tutor
    alert('Selected tutor: ' + tutorName + '\n\nPlease fill out the form below to send your session request.');
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

.alert {
    border-radius: 10px;
    border: none;
}

.tutor-card {
    transition: all 0.3s ease;
}

.tutor-card:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
}
</style>

<?php include '../footer.php'; ?>