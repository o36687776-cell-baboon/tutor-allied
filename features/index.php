<?php
$page_title = 'Features - AI-ACADEMY';
include '../header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">AI-ACADEMY Features</h1>
            <p class="lead">Discover the powerful tools and resources designed to accelerate your AI and Pi Network development journey.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">AI-Powered Learning</h5>
                    <p class="card-text">Advanced AI algorithms personalize your learning path, adapting to your pace and style for optimal comprehension.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fab fa-pied-piper fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Pi Network Integration</h5>
                    <p class="card-text">Seamlessly integrate Pi blockchain technology into your applications with our comprehensive SDK and tutorials.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Expert Tutors</h5>
                    <p class="card-text">Learn from industry experts with real-world experience in AI and blockchain development.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-code fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Hands-On Projects</h5>
                    <p class="card-text">Build real Pi Network applications through guided projects that reinforce your learning.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-certificate fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Certifications</h5>
                    <p class="card-text">Earn recognized certificates upon completion of courses to showcase your expertise.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-comments fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Community Support</h5>
                    <p class="card-text">Join a vibrant community of developers, share ideas, and get help from peers and mentors.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12 text-center">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <a href="<?php echo BASE_PATH; ?>/auth/" class="btn btn-primary btn-lg me-3">
                <i class="fab fa-pied-piper me-2"></i>Join Now
            </a>
            <a href="<?php echo BASE_PATH; ?>/learnershub/" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-play-circle me-2"></i>Explore Courses
            </a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>