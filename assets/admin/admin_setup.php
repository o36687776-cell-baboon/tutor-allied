<?php
// Admin Setup and Access Code Management
// This file handles initial admin setup and access code generation

// Start session first - Fix session path for XAMPP
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}

// Only set session save path if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($session_path);
    session_start();
} elseif (!isset($_SESSION)) {
    // If session is already active but $_SESSION is not set, start it anyway
    session_start();
}

// Include database connection
include 'db.php';

// Check if admin is already set up
$admin_user = fetchOne("SELECT * FROM users WHERE user_role = 'admin' LIMIT 1");
$setup_complete = $admin_user ? true : false;

if (!$setup_complete) {
    // First-time setup data
    $initial_setup = [
        'admin_username' => 'admin',
        'access_code' => 'AIACADEMY2024',
        'setup_date' => date('Y-m-d H:i:s'),
        'setup_complete' => false
    ];
} else {
    $initial_setup = [
        'admin_username' => $admin_user['username'],
        'access_code' => 'AIACADEMY2024', // Default, but check from db
        'setup_date' => $admin_user['created_at'],
        'setup_complete' => true
    ];
}

// Handle setup completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_setup'])) {
    $new_username = trim($_POST['new_username']);

    if (empty($new_username)) {
        $error = "Username is required.";
    } elseif (getUserByUsername($new_username)) {
        $error = "Username already taken.";
    } else {
        // Create admin user in database
        $admin_id = createUser([
            'username' => $new_username,
            'email' => 'admin@ai-academy.com', // Default admin email
            'auth_type' => 'pi',
            'user_role' => 'admin'
        ]);

        if ($admin_id) {
            // Create initial access code in database
            $access_code = 'AIACADEMY2024';
            executeUpdate("INSERT INTO admin_access_codes (code, description, created_by, expires_at) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE code = VALUES(code)",
                          [$access_code, 'Initial access code', $admin_id, date('Y-m-d H:i:s', strtotime('+1 year'))]);

            $success = "Admin setup completed successfully! You can now log in via Pi Network.";
            $setup_complete = true;
        } else {
            $error = "Failed to create admin user.";
        }
    }
}

// Handle access code generation (for logged-in admins)
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_code'])) {
    $description = trim($_POST['code_description']) ?: 'Generated access code';
    $expires_days = (int)($_POST['expires_days'] ?? 30);

    // Generate unique access code
    $new_code = 'AAC' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    // Insert into database
    $admin_user = getUserByUsername($_SESSION['admin_username']);
    executeUpdate("INSERT INTO admin_access_codes (code, description, created_by, expires_at) VALUES (?, ?, ?, ?)",
                  [$new_code, $description, $admin_user['id'], date('Y-m-d H:i:s', strtotime("+{$expires_days} days"))]);

    // Log the action
    include 'logger.php';
    logEvent('admin_action', ['action' => 'access_code_generated', 'code' => $new_code, 'expires_days' => $expires_days, 'description' => $description]);

    $code_generated = $new_code;
}

// Handle access code deactivation
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] && isset($_GET['deactivate_code'])) {
    $code_to_deactivate = $_GET['deactivate_code'];

    executeUpdate("UPDATE admin_access_codes SET used = TRUE, used_at = NOW(), used_by = ? WHERE code = ?",
                  [$_SESSION['user_id'], $code_to_deactivate]);

    include 'logger.php';
    logEvent('admin_action', ['action' => 'access_code_deactivated', 'code' => $code_to_deactivate]);

    header('Location: admin_setup.php');
    exit;
}

$page_title = 'Admin Setup & Access Codes';
include 'header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        <?php echo $setup_complete ? 'Admin Access Code Management' : 'Initial Admin Setup'; ?>
                    </h3>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($code_generated)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-key me-2"></i>
                            <strong>New Access Code Generated:</strong>
                            <code class="ms-2 fs-5"><?php echo $code_generated; ?></code>
                            <br><small class="text-muted">Share this code securely with authorized personnel.</small>
                        </div>
                    <?php endif; ?>

                    <?php if (!$setup_complete): ?>
                        <!-- Initial Setup Form -->
                        <div class="text-center mb-4">
                            <i class="fas fa-user-shield fa-4x text-primary mb-3"></i>
                            <h4>Welcome to AI-ACADEMY Admin Setup</h4>
                            <p class="text-muted">Set up your admin username for the first time. Admin login is handled via Pi Network.</p>
                        </div>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="new_username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Admin Username
                                </label>
                                <input type="text" class="form-control form-control-lg" id="new_username" name="new_username"
                                       value="<?php echo htmlspecialchars($initial_setup['admin_username']); ?>" required>
                                <div class="invalid-feedback">Please provide a username.</div>
                            </div>


                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Initial Access Code:</strong> <code><?php echo $initial_setup['access_code']; ?></code>
                                <br><small>You will need this code to access admin features after logging in via Pi Network.</small>
                            </div>

                            <button type="submit" name="complete_setup" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-check me-2"></i>Complete Setup
                            </button>
                        </form>

                    <?php else: ?>
                        <!-- Access Code Management (for logged-in admins) -->
                        <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>

                            <!-- Generate New Access Code -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Generate New Access Code</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="code_description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="code_description" name="code_description"
                                                   placeholder="e.g., For new admin user" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="expires_days" class="form-label">Expires In (Days)</label>
                                            <select class="form-select" id="expires_days" name="expires_days">
                                                <option value="7">7 days</option>
                                                <option value="30" selected>30 days</option>
                                                <option value="90">90 days</option>
                                                <option value="365">1 year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" name="generate_code" class="btn btn-success w-100">
                                                <i class="fas fa-plus me-1"></i>Generate
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Active Access Codes -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Active Access Codes</h5>
                                    <span class="badge bg-primary"><?php echo count(fetchAll("SELECT * FROM admin_access_codes")); ?> codes</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Access Code</th>
                                                    <th>Description</th>
                                                    <th>Created By</th>
                                                    <th>Expires</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $access_codes = fetchAll("SELECT a.*, u.username as creator FROM admin_access_codes a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC");
                                                foreach ($access_codes as $code):
                                                    $is_expired = strtotime($code['expires_at']) < time();
                                                    $is_used = $code['used'];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <code><?php echo htmlspecialchars($code['code']); ?></code>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($code['description']); ?></td>
                                                        <td><?php echo htmlspecialchars($code['creator'] ?? 'System'); ?></td>
                                                        <td>
                                                            <?php
                                                            echo date('M d, Y', strtotime($code['expires_at']));
                                                            if ($is_expired) echo ' <span class="badge bg-danger">Expired</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!$is_used && !$is_expired): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php elseif ($is_used): ?>
                                                                <span class="badge bg-secondary">Used</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Expired</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!$is_used && !$is_expired): ?>
                                                                <a href="?deactivate_code=<?php echo urlencode($code['code']); ?>"
                                                                   class="btn btn-sm btn-outline-danger"
                                                                   onclick="return confirm('Deactivate this access code?')">
                                                                    <i class="fas fa-ban me-1"></i>Deactivate
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Login Required Message -->
                            <div class="text-center py-5">
                                <i class="fas fa-lock fa-4x text-muted mb-3"></i>
                                <h4>Admin Access Required</h4>
                                <p class="text-muted">You must be logged in as an admin to manage access codes.</p>
                                <a href="<?php echo BASE_PATH; ?>/auth/?redirect=<?php echo BASE_PATH; ?>/admin/" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
}

code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9em;
}
</style>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

</script>

<?php include 'footer.php'; ?>