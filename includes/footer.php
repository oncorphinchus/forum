    </div><!-- /.container -->

    <!-- Footer -->
    <footer class="footer bg-dark text-white mt-5 py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="mb-4 text-white">Forum</h4>
                        <p class="mb-4">A modern, sophisticated forum platform for meaningful discussions and knowledge sharing.</p>
                        <div class="social-links">
                            <a href="#" class="me-2 btn btn-sm btn-outline-light rounded-circle">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="me-2 btn btn-sm btn-outline-light rounded-circle">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="me-2 btn btn-sm btn-outline-light rounded-circle">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="mb-4">Quick Links</h5>
                        <ul class="footer-links list-unstyled">
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/index.php" class="hover-lift">Home</a></li>
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/categories.php" class="hover-lift">Categories</a></li>
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/new_topic.php" class="hover-lift">New Topic</a></li>
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/contact.php" class="hover-lift">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="mb-4">Legal</h5>
                        <ul class="footer-links list-unstyled">
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/terms.php" class="hover-lift">Terms of Service</a></li>
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/privacy_policy.php" class="hover-lift">Privacy Policy</a></li>
                            <li class="mb-2"><a href="<?php echo BASE_URL; ?>/faq.php" class="hover-lift">FAQ</a></li>
                            <?php if (is_admin()): ?>
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>/admin/index.php" class="hover-lift">Admin Panel</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h5 class="mb-4">Newsletter</h5>
                        <p class="mb-4">Subscribe to our newsletter to receive updates and news.</p>
                        <form class="newsletter-form">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" placeholder="Your email" aria-label="Your email">
                                <button class="btn btn-primary" type="button">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom border-top border-secondary mt-4 pt-4">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-md-0">&copy; <?php echo date('Y'); ?> Forum. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> for a better web experience</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top btn btn-primary rounded-circle shadow" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/gsap@3.11.5/dist/gsap.min.js"></script> -->
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Disable AOS animations temporarily
        /*
        setTimeout(function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 50,
                delay: 50
            });
        }, 100);
        */
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        if (backToTopButton) {
            // Initially hide the button
            backToTopButton.style.display = 'none';
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'block';
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });
            
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // GSAP animations for page elements - Disable temporarily
        /*
        gsap.from('.card', {
            duration: 0.6,
            y: 20,
            opacity: 0,
            stagger: 0.1,
            ease: 'power1.out',
            delay: 0.2
        });
        */
        
        // Hover animations for buttons
        /*
        const buttons = document.querySelectorAll('.btn:not(.back-to-top)');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                gsap.to(this, {
                    scale: 1.05,
                    duration: 0.2
                });
            });
            
            button.addEventListener('mouseleave', function() {
                gsap.to(this, {
                    scale: 1,
                    duration: 0.2
                });
            });
        });
        */
    });
    </script>
    
    <?php if (isset($page_scripts)): ?>
        <?php echo $page_scripts; ?>
    <?php endif; ?>
</body>
</html> 