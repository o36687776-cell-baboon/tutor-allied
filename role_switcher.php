<?php
// Role Switcher Component - HTML Only
// PHP logic should be handled by role_handler.php included at the top of the page
$current_role = $_SESSION['user_role'] ?? 'guest';
?>

<!-- Role Switcher Modal (Only for authenticated users) -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="roleSwitcherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Switch User Mode
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="mb-4">Choose your preferred mode to access different features:</p>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="role-card <?php echo $current_role === 'student' ? 'active' : ''; ?>" onclick="switchRole('student')">
                            <div class="role-icon">
                                <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                            </div>
                            <h5 class="mt-3">Student Mode</h5>
                            <p class="text-muted small">Access courses, track progress, connect with tutors</p>
                            <div class="features-list small">
                                <div><i class="fas fa-check text-success me-1"></i> Course Access</div>
                                <div><i class="fas fa-check text-success me-1"></i> Progress Tracking</div>
                                <div><i class="fas fa-check text-success me-1"></i> Live Sessions</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="role-card <?php echo $current_role === 'tutor' ? 'active' : ''; ?>" onclick="switchRole('tutor')">
                            <div class="role-icon">
                                <i class="fas fa-chalkboard-teacher fa-3x text-success"></i>
                            </div>
                            <h5 class="mt-3">Tutor Mode</h5>
                            <p class="text-muted small">Create content, manage students, earn income</p>
                            <div class="features-list small">
                                <div><i class="fas fa-check text-success me-1"></i> Content Creation</div>
                                <div><i class="fas fa-check text-success me-1"></i> Student Management</div>
                                <div><i class="fas fa-check text-success me-1"></i> Earnings Dashboard</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> You can switch between Learner and Tutor modes anytime. Admin access requires separate authentication.
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.role-card {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.role-card:hover {
    border-color: #007bff;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.2);
}

.role-card.active {
    border-color: #007bff;
    background: #f8f9ff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.role-icon {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.features-list div {
    margin-bottom: 5px;
}

.modal-content {
    border: none;
    border-radius: 15px;
}

.modal-header {
    border-radius: 15px 15px 0 0 !important;
}
</style>

<script>
function switchRole(role) {
    // Update UI immediately
    document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('active');
    });

    if (role === 'student') {
        document.querySelector('.role-card:first-child').classList.add('active');
    } else if (role === 'tutor') {
        document.querySelector('.role-card:last-child').classList.add('active');
    }

    // Show loading state
    const modalBody = document.querySelector('#roleSwitcherModal .modal-body');
    const originalContent = modalBody.innerHTML;
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Switching to ${role.charAt(0).toUpperCase() + role.slice(1)} Mode</h5>
            <p class="text-muted">Please wait...</p>
        </div>
    `;

    // Redirect after short delay
    setTimeout(() => {
        window.location.href = `/index.php?mode=${role}`;
    }, 1500);
}

// Auto-show role switcher for new users
document.addEventListener('DOMContentLoaded', function() {
    const userRole = '<?php echo $current_role; ?>';
    if (userRole === 'guest' && !localStorage.getItem('roleSwitcherShown')) {
        const modal = new bootstrap.Modal(document.getElementById('roleSwitcherModal'));
        modal.show();
        localStorage.setItem('roleSwitcherShown', 'true');
    }
});
</script>