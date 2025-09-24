<?php
/**
 * KIIT SEVA - Footer Layout
 * Professional footer with university information
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Footer Brand -->
            <div class="footer-section">
                <div class="footer-brand">
                    <h3>KIIT SEVA</h3>
                    <p>Your trusted student services platform for KIIT University. Streamlining academic and campus life through digital innovation.</p>
                </div>
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook">📘</a>
                    <a href="#" class="social-link" aria-label="Twitter">🐦</a>
                    <a href="#" class="social-link" aria-label="Instagram">📷</a>
                    <a href="#" class="social-link" aria-label="LinkedIn">💼</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo getBaseUrl(); ?>/public/">Home</a></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php">Services</a></li>
                    <li><a href="#about">About KIIT</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#support">Support</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="footer-section">
                <h4>Services</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo getBaseUrl(); ?>/app/views/booking/index.php">Teacher Booking</a></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/app/views/tracking/index.php">Vehicle Tracking</a></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/app/views/feedback/index.php">Feedback System</a></li>
                    <li><a href="#help">Help Center</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section">
                <h4>Contact Information</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <span class="contact-icon">📍</span>
                        <span>KIIT University, Bhubaneswar, Odisha 751024</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">📞</span>
                        <span>+91-674-272-7777</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">✉️</span>
                        <span>support@kiit.ac.in</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🚨</span>
                        <span>Emergency: +91-674-272-8888</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> KIIT University. All rights reserved.</p>
                    <p>Developed with ❤️ for KIIT Community</p>
                </div>
                <div class="footer-links-bottom">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Service</a>
                    <a href="#cookies">Cookie Policy</a>
                    <a href="#accessibility">Accessibility</a>
                </div>
            </div>
            <div class="footer-meta">
                <small>Last updated: <?php echo date('M j, Y'); ?> | Version 1.0.0</small>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" onclick="scrollToTop()" aria-label="Back to top">
    ↑
</button>

<script>
// Back to top functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide back to top button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.classList.add('show');
    } else {
        backToTop.classList.remove('show');
    }
});
</script>