<?php
// Admin Content Approval page
$page_title = 'Content Approval';
include '../header.php';

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

// Check admin access
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/admin/');
    exit;
}

include '../mail_config.php';

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $course_id = (int)$_POST['course_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE courses SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        executeUpdate($sql, [$_SESSION['admin_id'] ?? 1, $course_id]);
        $message = 'Course approved successfully!';

        // Send approval email to tutor
        sendCourseApprovalNotification($course_id, true);
    } elseif ($action === 'reject') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        $sql = "UPDATE courses SET status = 'rejected', approved_by = ?, rejection_reason = ? WHERE id = ?";
        executeUpdate($sql, [$_SESSION['admin_id'] ?? 1, $reason, $course_id]);
        $message = 'Course rejected.';

        // Send rejection email to tutor
        sendCourseApprovalNotification($course_id, false, $reason);
    }
}

// Get pending courses
$pending_courses = fetchAll("
    SELECT c.*, u.username as instructor_name, u.email as instructor_email
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE c.status = 'pending_approval'
    ORDER BY c.created_at DESC
");

// Get approved courses for reference
$approved_courses = fetchAll("
    SELECT c.*, u.username as instructor_name
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE c.status = 'approved'
    ORDER BY c.approved_at DESC
    LIMIT 10
");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Content Approval Dashboard</h2>
            <p class="text-muted">Review and approve tutor-created courses before publication</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Pending Approvals -->
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2"></i>
                Pending Approvals (<?php echo count($pending_courses); ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (count($pending_courses) > 0): ?>
                <div class="row">
                    <?php foreach ($pending_courses as $course): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                                    <small class="text-muted">by <?php echo htmlspecialchars($course['instructor_name']); ?></small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 150)); ?>...</p>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Category</small><br>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($course['category']); ?></span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Difficulty</small><br>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Duration</small><br>
                                            <span><?php echo $course['duration_hours']; ?> hours</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Price</small><br>
                                            <span class="fw-bold text-warning">
                                                <i class="fas fa-circle me-1"></i>
                                                <?php echo number_format($course['price_pi'], 2); ?> π
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
                                            Submitted: <?php echo date('M j, Y H:i', strtotime($course['created_at'])); ?>
                                        </small>
                                        <div>
                                            <button class="btn btn-sm btn-success me-2" onclick="approveCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5 class="text-muted">No pending approvals</h5>
                    <p class="text-muted">All submitted content has been reviewed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recently Approved -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-check-circle me-2"></i>
                Recently Approved Courses
            </h5>
        </div>
        <div class="card-body">
            <?php if (count($approved_courses) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Instructor</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Approved</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($course['category']); ?></span></td>
                                    <td><?php echo number_format($course['price_pi'], 2); ?> π</td>
                                    <td><?php echo date('M j, Y', strtotime($course['approved_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No approved courses yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve <strong id="approveCourseTitle"></strong>?</p>
                <p class="text-muted">This course will be published and available for student enrollment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="course_id" id="approveCourseId">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-success">Approve Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to reject <strong id="rejectCourseTitle"></strong>?</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason (required)</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required
                                  placeholder="Please provide feedback on why this course was rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <input type="hidden" name="course_id" id="rejectCourseId">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger">Reject Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveCourse(courseId, courseTitle) {
    document.getElementById('approveCourseId').value = courseId;
    document.getElementById('approveCourseTitle').textContent = courseTitle;
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}

function rejectCourse(courseId, courseTitle) {
    document.getElementById('rejectCourseId').value = courseId;
    document.getElementById('rejectCourseTitle').textContent = courseTitle;
    new bootstrap.Modal(document.getElementById('rejectionModal')).show();
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

.table th {
    border-top: none;
    font-weight: 600;
}

.modal-content {
    border-radius: 15px;
    border: none;
}
</style>

<?php include '../footer.php'; ?>