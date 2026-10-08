<?php
$page_title = 'Support';
include 'header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Support Center</h1>
                <p class="lead fs-4 text-muted">How can we help you today?</p>
            </div>

            <!-- Quick Support Form -->
            <div class="card shadow mb-5">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">Quick Support Request</h2>
                </div>
                <div class="card-body p-4">
                    <form id="supportForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="supportEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="supportEmail" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="supportIssue" class="form-label">Issue Type</label>
                                <select class="form-select" id="supportIssue" required>
                                    <option value="">Select issue type</option>
                                    <option value="technical">Technical Problem</option>
                                    <option value="account">Account Issue</option>
                                    <option value="billing">Billing/Payment</option>
                                    <option value="course">Course Content</option>
                                    <option value="tutor">Tutor Support</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="supportMessage" class="form-label">Describe your issue</label>
                            <textarea class="form-control" id="supportMessage" rows="4" placeholder="Please provide details about your issue..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Support Request</button>
                    </form>

                    <div id="supportMessage" class="mt-3 d-none"></div>
                </div>
            </div>

            <!-- Support Categories -->
            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                            <h3 class="h5 card-title">Technical Support</h3>
                            <p class="card-text">Issues with platform functionality, login problems, or technical difficulties.</p>
                            <button class="btn btn-outline-primary" onclick="selectIssue('technical')">Get Help</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                            <h3 class="h5 card-title">Learning Support</h3>
                            <p class="card-text">Questions about courses, assignments, or learning materials.</p>
                            <button class="btn btn-outline-success" onclick="selectIssue('course')">Get Help</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-credit-card fa-3x text-warning mb-3"></i>
                            <h3 class="h5 card-title">Billing Support</h3>
                            <p class="card-text">Payment issues, refunds, or subscription questions.</p>
                            <button class="btn btn-outline-warning" onclick="selectIssue('billing')">Get Help</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                            <h3 class="h5 card-title">Account Support</h3>
                            <p class="card-text">Profile updates, password resets, or account settings.</p>
                            <button class="btn btn-outline-info" onclick="selectIssue('account')">Get Help</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2 class="h3 text-primary mb-3">Other Ways to Reach Us</h2>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="contact-method">
                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                <h5 class="h6">Email</h5>
                                <p>info@homework-help.online</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="contact-method">
                                <i class="fas fa-phone fa-2x text-success mb-2"></i>
                                <h5 class="h6">Phone</h5>
                                <p>+254 758 318 070</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="contact-method">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h5 class="h6">Response Time</h5>
                                <p>Within 24 hours</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Support form submission
document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const supportData = {
        email: document.getElementById('supportEmail').value,
        issue: document.getElementById('supportIssue').value,
        message: document.getElementById('supportMessage').value,
        timestamp: new Date().toISOString(),
        status: 'pending'
    };

    // Store support request (in real app, this would be sent to server)
    const requests = JSON.parse(localStorage.getItem('supportRequests') || '[]');
    requests.push(supportData);
    localStorage.setItem('supportRequests', JSON.stringify(requests));

    // Show success message
    const messageDiv = document.getElementById('supportMessage');
    messageDiv.className = 'alert alert-success';
    messageDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Support request submitted successfully! We\'ll respond to your email within 24 hours.';
    messageDiv.classList.remove('d-none');

    // Reset form
    this.reset();

    // Hide message after 5 seconds
    setTimeout(() => {
        messageDiv.classList.add('d-none');
    }, 5000);

    // Log support request
    console.log('Support request:', supportData);
});

// Select issue type from category buttons
function selectIssue(issueType) {
    document.getElementById('supportIssue').value = issueType;
    document.getElementById('supportEmail').focus();
}
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.contact-method {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.contact-method:hover {
    transform: translateY(-5px);
}

.shadow-sm {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
}
</style>

<?php include 'footer.php'; ?>