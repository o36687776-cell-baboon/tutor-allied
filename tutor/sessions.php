<?php
// Tutor Sessions Management page
$page_title = 'Manage Sessions';

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

// Handle session completion and payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $session_id = (int)$_POST['session_id'];

    if ($_POST['action'] === 'complete') {
        // Mark session as completed
        executeUpdate("UPDATE tutoring_sessions SET status = 'completed', completed_at = NOW() WHERE id = ? AND tutor_id = ?",
                     [$session_id, $_SESSION['user_id']]);

        // Create payment record for the session
        $session = fetchOne("SELECT * FROM tutoring_sessions WHERE id = ? AND tutor_id = ?", [$session_id, $_SESSION['user_id']]);

        if ($session && $session['status'] === 'completed') {
            $payment_id = createPayment($session['student_id'], $session['session_price_pi'], 'PI',
                                      "Tutoring session payment - {$session['subject']}");

            if ($payment_id) {
                // Mark payment as completed (in real implementation, this would be after Pi payment confirmation)
                updatePaymentStatus($payment_id, 'completed');

                // Update session with payment ID and mark as paid
                executeUpdate("UPDATE tutoring_sessions SET payment_id = ?, status = 'paid', paid_at = NOW() WHERE id = ?",
                             [$payment_id, $session_id]);

                // Update tutor earnings
                executeUpdate("UPDATE users SET total_pi_earned = total_pi_earned + ? WHERE id = ?",
                             [$session['session_price_pi'], $_SESSION['user_id']]);

                $success = 'Session completed and payment processed successfully!';
            }
        }
    }
}

// Get tutor's sessions
$sessions = fetchAll("
    SELECT ts.*, u.username as student_name, u.email as student_email
    FROM tutoring_sessions ts
    LEFT JOIN users u ON ts.student_id = u.id
    WHERE ts.tutor_id = ?
    ORDER BY ts.created_at DESC
", [$_SESSION['user_id']]);
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My Tutoring Sessions</h2>
            <p class="text-muted">Manage your scheduled sessions and process payments</p>
        </div>
        <a href="../learnershub/request-tutor.php" class="btn btn-outline-primary">
            <i class="fas fa-plus-circle me-2"></i>View Student Requests
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (count($sessions) > 0): ?>
        <div class="row">
            <?php foreach ($sessions as $session): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?php echo htmlspecialchars($session['subject']); ?></h6>
                            <span class="badge bg-<?php
                                echo $session['status'] === 'paid' ? 'success' :
                                     ($session['status'] === 'completed' ? 'warning' :
                                      ($session['status'] === 'scheduled' ? 'info' : 'secondary'));
                            ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $session['status']))); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Student</small><br>
                                    <strong><?php echo htmlspecialchars($session['student_name']); ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Duration</small><br>
                                    <strong><?php echo $session['duration_hours']; ?> hour(s)</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Topic</small><br>
                                <strong><?php echo htmlspecialchars($session['topic'] ?? $session['subject']); ?></strong>
                            </div>

                            <?php if ($session['description']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Description</small><br>
                                    <p class="small"><?php echo htmlspecialchars($session['description']); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Scheduled</small><br>
                                    <span><?php echo $session['scheduled_at'] ? date('M j, Y H:i', strtotime($session['scheduled_at'])) : 'Not scheduled'; ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Payment</small><br>
                                    <span class="fw-bold text-warning">
                                        <i class="fas fa-circle me-1"></i>
                                        <?php echo number_format($session['session_price_pi'], 2); ?> π
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Requested: <?php echo date('M j, Y', strtotime($session['created_at'])); ?>
                                </small>

                                <?php if ($session['status'] === 'scheduled' || $session['status'] === 'requested'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('Mark this session as completed and process payment?')">
                                            <i class="fas fa-check me-1"></i>Complete & Pay
                                        </button>
                                    </form>
                                <?php elseif ($session['status'] === 'completed'): ?>
                                    <span class="text-warning">
                                        <i class="fas fa-clock me-1"></i>Payment Processing
                                    </span>
                                <?php elseif ($session['status'] === 'paid'): ?>
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Paid
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x mb-4 text-muted"></i>
            <h3 class="text-muted">No Sessions Yet</h3>
            <p class="text-muted mb-4">You haven't had any tutoring sessions scheduled yet.</p>
            <a href="../learnershub/request-tutor.php" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Check for Student Requests
            </a>
        </div>
    <?php endif; ?>

    <!-- Earnings Summary -->
    <?php if (count($sessions) > 0): ?>
        <div class="card mt-4 shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Session Earnings Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-primary"><?php echo count($sessions); ?></h3>
                        <p class="mb-0">Total Sessions</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success">
                            <?php
                            $completed_sessions = array_filter($sessions, function($s) {
                                return in_array($s['status'], ['completed', 'paid']);
                            });
                            echo count($completed_sessions);
                            ?>
                        </h3>
                        <p class="mb-0">Completed Sessions</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">
                            <?php
                            $total_earned = array_reduce($sessions, function($sum, $s) {
                                return $sum + ($s['status'] === 'paid' ? $s['session_price_pi'] : 0);
                            }, 0);
                            echo number_format($total_earned, 2);
                            ?>
                        </h3>
                        <p class="mb-0">Pi Coins Earned</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info">
                            <?php
                            $avg_rating = 4.8; // Placeholder - would calculate from actual ratings
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

.badge {
    font-size: 0.75em;
}
</style>

<?php include '../footer.php'; ?>