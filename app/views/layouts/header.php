<?php
/**
 * KIIT SEVA - Header Layout
 * Professional navigation header with role-based menu
 */

$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
?>
<header class="navbar">
    <div class="navbar-container">
        <!-- Logo and Brand -->
        <div class="navbar-brand">
            <a href="<?php echo getBaseUrl(); ?>/public/">
                <img src="<?php echo getBaseUrl(); ?>/assets/images/logos/kiit-seva-logo.png" alt="KIIT SEVA" class="logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span class="brand-text">KIIT SEVA</span>
            </a>
        </div>

        <!-- Desktop Navigation -->
        <nav class="navbar-nav desktop-nav">
            <?php if ($isLoggedIn): ?>
                <!-- Role-based navigation -->
                <?php if ($currentUser['role'] === 'student'): ?>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/student.php" class="nav-link">Dashboard</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/booking/index.php" class="nav-link">Book Teacher</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/index.php" class="nav-link">Track Vehicle</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/feedback/index.php" class="nav-link">Feedback</a>
                <?php elseif ($currentUser['role'] === 'teacher'): ?>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/teacher.php" class="nav-link">Dashboard</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/booking/manage.php" class="nav-link">My Bookings</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/booking/availability.php" class="nav-link">Availability</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/feedback/teacher.php" class="nav-link">Student Feedback</a>
                <?php elseif ($currentUser['role'] === 'staff'): ?>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/staff.php" class="nav-link">Dashboard</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/duty.php" class="nav-link">Duty Status</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/control.php" class="nav-link">Vehicle Control</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/admin/reports.php" class="nav-link">Reports</a>
                <?php endif; ?>
            <?php else: ?>
                <!-- Guest navigation -->
                <a href="<?php echo getBaseUrl(); ?>/public/" class="nav-link">Home</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php" class="nav-link">Services</a>
                <a href="#contact" class="nav-link">Contact</a>
            <?php endif; ?>
        </nav>

        <!-- User Profile / Auth Buttons -->
        <div class="navbar-actions">
            <?php if ($isLoggedIn): ?>
                <!-- User Profile Dropdown -->
                <div class="user-dropdown">
                    <button class="user-button" onclick="toggleUserDropdown()">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                                <small><?php echo ucfirst($currentUser['role']); ?></small>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo getBaseUrl(); ?>/app/views/profile/index.php" class="dropdown-item">
                            👤 Profile Settings
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>/app/views/profile/notifications.php" class="dropdown-item">
                            🔔 Notifications
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo getBaseUrl(); ?>/app/controllers/AuthController.php?action=logout" class="dropdown-item logout">
                            🚪 Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Auth Buttons -->
                <div class="auth-buttons">
                    <a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php" class="btn btn-outline">Sign In</a>
                    <a href="<?php echo getBaseUrl(); ?>/app/views/auth/register.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav" id="mobileNav">
        <?php if ($isLoggedIn): ?>
            <!-- Mobile role-based navigation -->
            <?php if ($currentUser['role'] === 'student'): ?>
                <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/student.php" class="mobile-nav-link">📊 Dashboard</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/booking/index.php" class="mobile-nav-link">👨‍🏫 Book Teacher</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/index.php" class="mobile-nav-link">🚌 Track Vehicle</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/feedback/index.php" class="mobile-nav-link">💬 Feedback</a>
            <?php elseif ($currentUser['role'] === 'teacher'): ?>
                <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/teacher.php" class="mobile-nav-link">📊 Dashboard</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/booking/manage.php" class="mobile-nav-link">📅 My Bookings</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/booking/availability.php" class="mobile-nav-link">⏰ Availability</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/feedback/teacher.php" class="mobile-nav-link">⭐ Student Feedback</a>
            <?php elseif ($currentUser['role'] === 'staff'): ?>
                <a href="<?php echo getBaseUrl(); ?>/app/views/dashboard/staff.php" class="mobile-nav-link">📊 Dashboard</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/duty.php" class="mobile-nav-link">🚛 Duty Status</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/tracking/control.php" class="mobile-nav-link">🎛️ Vehicle Control</a>
                <a href="<?php echo getBaseUrl(); ?>/app/views/admin/reports.php" class="mobile-nav-link">📈 Reports</a>
            <?php endif; ?>
            <div class="mobile-nav-divider"></div>
            <a href="<?php echo getBaseUrl(); ?>/app/views/profile/index.php" class="mobile-nav-link">👤 Profile</a>
            <a href="<?php echo getBaseUrl(); ?>/app/controllers/AuthController.php?action=logout" class="mobile-nav-link logout">🚪 Logout</a>
        <?php else: ?>
            <a href="<?php echo getBaseUrl(); ?>/public/" class="mobile-nav-link">🏠 Home</a>
            <a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php" class="mobile-nav-link">🔧 Services</a>
            <a href="#contact" class="mobile-nav-link">📞 Contact</a>
            <div class="mobile-nav-divider"></div>
            <a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php" class="mobile-nav-link">🔑 Sign In</a>
            <a href="<?php echo getBaseUrl(); ?>/app/views/auth/register.php" class="mobile-nav-link">📝 Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    mobileNav.classList.toggle('show');
    toggle.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const userButton = document.querySelector('.user-button');
    
    if (dropdown && !userButton.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>