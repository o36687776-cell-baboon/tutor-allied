<?php
/**
 * Pi Network User Role Selection
 * Allows new Pi users to select their role (student or tutor)
 */

$page_title = 'Select Your Role - AI-ACADEMY';

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

// Check if user is authenticated and is a new Pi user
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pi_authenticated'])) {
    header('Location: ' . BASE_PATH . '/auth/');
    exit;
}

// Check if user already has a role set (not new)
$user = getUserById($_SESSION['user_id']);
if (!$user || $user['user_role'] !== 'student') { // Assuming new users default to student
    // User already has role set, redirect to dashboard
    if ($user['user_role'] === 'admin') {
        header('Location: ' . BASE_PATH . '/admin/');
    } elseif ($user['user_role'] === 'tutor') {
        header('Location: ' . BASE_PATH . '/tutor/');
    } else {
        header('Location: ' . BASE_PATH . '/learnershub/');
    }
    exit;
}

// Handle role selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_role'])) {
    $selected_role = $_POST['user_role'] ?? 'student';

    if (in_array($selected_role, ['student', 'tutor'])) {
        // Update user role
        updateUser($_SESSION['user_id'], ['user_role' => $selected_role]);

        // Set session role
        $_SESSION['user_role'] = $selected_role;

        // Update last login
        updateUser($_SESSION['user_id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Redirect to appropriate dashboard
        if ($selected_role === 'tutor') {
            header('Location: ' . BASE_PATH . '/tutor/');
        } else {
            header('Location: ' . BASE_PATH . '/learnershub/');
        }
        exit;
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-user-tag me-2"></i>
                        Welcome to AI-ACADEMY!
                    </h3>
                    <p class="mb-0 mt-2">Choose your role to get started</p>
                </div>
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <i class="fab fa-pied-piper fa-4x text-primary mb-3"></i>
                        <h4>Pi Network Authentication Successful!</h4>
                        <p class="text-muted">Now select how you'd like to use AI-ACADEMY</p>
                    </div>

                    <form method="POST" action="">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="role-option-card" onclick="selectRole('student')">
                                    <input type="radio" name="user_role" value="student" id="role_student" class="d-none">
                                    <label for="role_student" class="card h-100 border-primary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                                            <h5 class="card-title">Student</h5>
                                            <p class="card-text">Learn AI, take courses, earn certificates, and connect with tutors.</p>
                                            <div class="features-list small text-start">
                                                <div><i class="fas fa-check text-success me-2"></i> Access to all courses</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Progress tracking</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Certificate earning</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Community forums</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="role-option-card" onclick="selectRole('tutor')">
                                    <input type="radio" name="user_role" value="tutor" id="role_tutor" class="d-none">
                                    <label for="role_tutor" class="card h-100 border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                                            <h5 class="card-title">Tutor</h5>
                                            <p class="card-text">Teach AI, create courses, mentor students, and earn Pi coins.</p>
                                            <div class="features-list small text-start">
                                                <div><i class="fas fa-check text-success me-2"></i> Course creation tools</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Student management</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Earnings dashboard</div>
                                                <div><i class="fas fa-check text-success me-2"></i> Premium features</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="select_role" class="btn btn-primary btn-lg px-5 py-3" id="continueBtn" disabled>
                                <i class="fas fa-arrow-right me-2"></i>Continue as <span id="selectedRole">Student</span>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">You can change your role later in your account settings</small>
                    </div>
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

.role-option-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.role-option-card:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.role-option-card input[type="radio"]:checked + label .card {
    border-color: #007bff !important;
    border-width: 3px !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.features-list div {
    margin-bottom: 8px;
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}
</style>

<script>
let selectedRole = 'student';

function selectRole(role) {
    // Update radio buttons
    document.getElementById('role_student').checked = (role === 'student');
    document.getElementById('role_tutor').checked = (role === 'tutor');

    // Update visual selection
    document.querySelectorAll('.role-option-card label .card').forEach(card => {
        card.classList.remove('border-primary');
        card.style.borderWidth = '1px';
    });

    if (role === 'student') {
        document.querySelector('label[for="role_student"] .card').classList.add('border-primary');
        document.querySelector('label[for="role_student"] .card').style.borderWidth = '3px';
    } else {
        document.querySelector('label[for="role_tutor"] .card').classList.add('border-primary');
        document.querySelector('label[for="role_tutor"] .card').style.borderWidth = '3px';
    }

    selectedRole = role;
    document.getElementById('selectedRole').textContent = role.charAt(0).toUpperCase() + role.slice(1);
    document.getElementById('continueBtn').disabled = false;
}

// Auto-select student by default
document.addEventListener('DOMContentLoaded', function() {
    selectRole('student');
});
</script>

<?php include '../footer.php'; ?>