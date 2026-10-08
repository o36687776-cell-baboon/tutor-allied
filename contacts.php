<?php
$page_title = 'Contact Us';
include 'header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Contact Us</h1>
                <p class="lead fs-4 text-muted">Get in touch with the AI-ACADEMY team</p>
            </div>

            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-6 mb-5">
                    <div class="card shadow h-100">
                        <div class="card-header bg-primary text-white">
                            <h2 class="h4 mb-0">Get In Touch</h2>
                        </div>
                        <div class="card-body">
                            <div class="contact-info mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-envelope fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Email</h5>
                                        <p class="mb-0">info@homework-help.online</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-phone fa-2x text-success me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Phone</h5>
                                        <p class="mb-0">+254 758 318 070</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-map-marker-alt fa-2x text-warning me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Location</h5>
                                        <p class="mb-0">U.A.E, QATAR</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-clock fa-2x text-info me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Business Hours</h5>
                                        <p class="mb-0">Mon - Fri: 9:00 AM - 6:00 PM GMT</p>
                                        <p class="mb-0">Sat: 10:00 AM - 4:00 PM GMT</p>
                                    </div>
                                </div>
                            </div>

                            <div class="social-links">
                                <h5 class="mb-3">Follow Us</h5>
                                <div class="d-flex gap-3">
                                    <a href="#" class="btn btn-outline-primary btn-sm">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-info btn-sm">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-danger btn-sm">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-primary btn-sm">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-6 mb-5">
                    <div class="card shadow h-100">
                        <div class="card-header bg-success text-white">
                            <h2 class="h4 mb-0">Send us a Message</h2>
                        </div>
                        <div class="card-body">
                            <form id="contactForm">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" id="subject" required>
                                        <option value="">Choose a subject</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="support">Technical Support</option>
                                        <option value="partnership">Partnership</option>
                                        <option value="feedback">Feedback</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                            </form>

                            <div id="formMessage" class="mt-3 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card shadow mb-5">
                <div class="card-header bg-info text-white">
                    <h2 class="h4 mb-0">Frequently Asked Questions</h2>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I enroll in a course?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Simply browse our course catalog, select the course you're interested in, and click "Enroll Now". You'll need to create a free account to track your progress.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We ONLY  accept  Pi Network payments.You can check on pi network to know how to On-Ramp other currencies into PI wallet before proceeding with your requests
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you offer certificates?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! Upon successful completion of any course, you'll receive a certificate of completion that you can share on your professional profiles.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    How can I become a tutor?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    If you're an expert in AI, software development, or blockchain, you can apply to become a tutor through our tutor application process. Visit the tutor section for more details.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value,
        timestamp: new Date().toISOString()
    };

    // Store contact message - would need API endpoint for database storage
    console.log('Contact message submitted:', formData);
    // In production, send to server API

    // Show success message
    const messageDiv = document.getElementById('formMessage');
    messageDiv.className = 'alert alert-success';
    messageDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Thank you for your message! We\'ll get back to you within 24 hours.';
    messageDiv.classList.remove('d-none');

    // Reset form
    this.reset();

    // Hide message after 5 seconds
    setTimeout(() => {
        messageDiv.classList.add('d-none');
    }, 5000);
});
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

.contact-info .d-flex {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
}

.social-links .btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.accordion-button:focus {
    box-shadow: none;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #0c63e4;
}
</style>

<?php include 'footer.php'; ?>