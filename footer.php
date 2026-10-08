    </div> <!-- End container -->

    <!-- Dynamic base path detection for different deployment environments -->
    <?php if (!defined('BASE_PATH')) {
        // Get the directory of the current script
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        // Get the parent directory (deployment subdirectory)
        $base_path = dirname($script_dir);

        // If at root level, use empty string; otherwise use the subdirectory path
        if ($base_path === '/' || $base_path === '\\' || $base_path === '.') {
            $base_path = '';
        }

        define('BASE_PATH', $base_path);
    } ?>

    <!-- Footer Links -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>AI-ACADEMY</h5>
                    <p>Master AI, Software Development, and Blockchain</p>
                    <div class="mt-3">
                        <a href="<?php echo BASE_PATH; ?>/contacts.php" class="text-light me-3"><i class="fas fa-envelope"></i></a>
                        <a href="<?php echo BASE_PATH; ?>/contacts.php" class="text-light me-3"><i class="fas fa-phone"></i></a>
                        <small class="text-muted d-block mt-2">+254 758 318 070</small>
                        <small class="text-muted">info@homework-help.online</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h5>Platform</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_PATH; ?>/learnershub/">Learners Hub</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/tutor/">Tutor Portal</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/library/catalogue.php">Library</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/forums/">Forums</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/blog-catalogue/">Blogs</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/bootstrap/">Developer's Tools</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_PATH; ?>/support.php">Help Center</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/contacts.php">Contact Us</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/rating-badge/">Rating Badge</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/rating-badge/community.php">Rate Products</a></li>
                        <li>
                            <!-- Support Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-link text-light p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Quick Support
                                </button>
                                <div class="dropdown-menu p-3" style="min-width: 300px;">
                                    <form id="footerSupportForm">
                                        <div class="mb-2">
                                            <input type="email" class="form-control form-control-sm" placeholder="Your email" id="footerEmail" required>
                                        </div>
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm" id="footerIssue" required>
                                                <option value="">Select issue</option>
                                                <option value="technical">Technical</option>
                                                <option value="account">Account</option>
                                                <option value="billing">Billing</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <textarea class="form-control form-control-sm" rows="2" placeholder="Describe issue" id="footerMessage" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100">Submit</button>
                                    </form>
                                    <div id="footerSupportMsg" class="mt-2 d-none small"></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_PATH; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/about-pi.php">About Pi Network</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/legal/privacy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/legal/terms.php">Terms of Service</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/rating-badge/">Trust Badge</a></li>
                    </ul>
                </div>
            </div>

            <!-- Pi Network Section -->
            <div class="row mt-4 pt-4 border-top">
                <div class="col-md-6">
                    <h5 class="text-warning">
                        <i class="fas fa-circle text-warning me-2"></i>
                        Powered by Pi Network
                    </h5>
                    <p class="small text-muted mb-2">
                        AI-ACADEMY is proudly built on the Pi Network ecosystem, revolutionizing education through cryptocurrency innovation.
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark me-2">Pi Coin Payments</span>
                        <span class="badge bg-info text-white me-2">Blockchain Technology</span>
                        <span class="badge bg-success text-white">Decentralized Learning</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning">Pi Network Links</h6>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled small">
                                <li><a href="https://minepi.com/" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>Official Website</a></li>
                                <li><a href="https://socialchain.app/" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>Social Chain</a></li>
                                <li><a href="https://pi-blockchain.net/" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>Blockchain Explorer</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled small">
                                <li><a href="https://pinetwork-official.medium.com/" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>Medium Blog</a></li>
                                <li><a href="https://www.youtube.com/@PiNetwork" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>YouTube</a></li>
                                <li><a href="https://discord.gg/pi-network" target="_blank" class="text-light"><i class="fas fa-external-link-alt me-1"></i>Discord</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> AI-ACADEMY. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">Made with <i class="fas fa-heart text-danger"></i> for learners worldwide</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/global.js"></script>
    <?php if (isset($extra_js)) echo "<script src='assets/$extra_js'></script>"; ?>

    <script>
    // Footer support form
    document.getElementById('footerSupportForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const supportData = {
            email: document.getElementById('footerEmail').value,
            issue: document.getElementById('footerIssue').value,
            message: document.getElementById('footerMessage').value,
            timestamp: new Date().toISOString(),
            source: 'footer_dropdown'
        };

        // Store support request - would need API endpoint for database storage
        console.log('Support request submitted:', supportData);
        // In production, send to server API

        // Show success message
        const messageDiv = document.getElementById('footerSupportMsg');
        messageDiv.className = 'alert alert-success small mt-2';
        messageDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i>Request sent! Check your email.';
        messageDiv.classList.remove('d-none');

        // Reset form
        this.reset();

        // Hide message after 3 seconds
        setTimeout(() => {
            messageDiv.classList.add('d-none');
        }, 3000);

        // Close dropdown
        const dropdown = this.closest('.dropdown');
        const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
        if (bsDropdown) {
            bsDropdown.hide();
        }
    });

    // Admin Session Timer
    <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
    function updateSessionTimer() {
        const timerElement = document.getElementById('sessionTimer');
        if (!timerElement) return;

        const loginTime = <?php echo $_SESSION['admin_login_time']; ?>;
        const sessionTimeout = <?php echo $session_timeout ?? 1800; ?>;
        const currentTime = Math.floor(Date.now() / 1000);
        const elapsed = currentTime - loginTime;
        const remaining = sessionTimeout - elapsed;

        if (remaining <= 0) {
            // Session expired
            window.location.href = '<?php echo BASE_PATH; ?>/auth/?redirect=<?php echo BASE_PATH; ?>/admin/&timeout=1';
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;

        timerElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // Change color when less than 5 minutes remaining
        if (remaining < 300) {
            timerElement.style.color = '#dc3545';
            timerElement.style.fontWeight = 'bold';
        }
    }

    // Update timer every second
    setInterval(updateSessionTimer, 1000);
    updateSessionTimer(); // Initial call
    <?php endif; ?>

    // Demo Mode Timer
    <?php if (isset($is_demo_mode) && $is_demo_mode): ?>
    function updateDemoTimer() {
        const timerElement = document.getElementById('demoTimer');
        if (!timerElement) return;

        const demoExpires = <?php echo $_SESSION['demo_expires']; ?>;
        const currentTime = Math.floor(Date.now() / 1000);
        const remaining = demoExpires - currentTime;

        if (remaining <= 0) {
            // Demo expired
            window.location.href = '<?php echo BASE_PATH; ?>/auth/?message=demo_expired';
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;

        timerElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // Change color when less than 1 hour remaining
        if (remaining < 3600) {
            timerElement.style.color = '#dc3545';
            timerElement.style.fontWeight = 'bold';
        }
    }

    // Update demo timer every second
    setInterval(updateDemoTimer, 1000);
    updateDemoTimer(); // Initial call
    <?php endif; ?>
    </script>
</body>
</html>