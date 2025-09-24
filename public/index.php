<?php
/**
 * KIIT SEVA - Main Entry Point
 * Professional homepage with role-based navigation
 */

// Start session for user state management
session_start();

// Include helper functions
require_once dirname(__DIR__) . '/app/helpers/functions.php';

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIIT SEVA - Your Trusted Student Services Platform</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header -->
    <?php include dirname(__DIR__) . '/app/views/layouts/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>KIIT SEVA</h1>
                <p class="hero-subtitle">Your Trusted Student Services Platform</p>
                <p class="hero-description">Streamline your university experience with our comprehensive digital platform for teacher bookings, vehicle tracking, and feedback management.</p>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="hero-actions">
                        <a href="app/views/auth/register.php" class="btn btn-primary">Get Started</a>
                        <a href="app/views/auth/login.php" class="btn btn-secondary">Sign In</a>
                    </div>
                <?php else: ?>
                    <div class="hero-actions">
                        <a href="app/views/dashboard/<?php echo $currentUser['role']; ?>.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services">
        <div class="container">
            <h2>Our Services</h2>
            <div class="services-grid">
                <!-- Teacher Booking Service -->
                <div class="service-card">
                    <div class="service-icon">👨‍🏫</div>
                    <h3>Teacher Booking</h3>
                    <p>Book appointments with faculty members easily. View real-time availability and manage your academic consultations efficiently.</p>
                    <?php if ($isLoggedIn && $currentUser['role'] === 'student'): ?>
                        <a href="app/views/booking/index.php" class="btn btn-outline">Book Now</a>
                    <?php else: ?>
                        <a href="app/views/auth/login.php" class="btn btn-outline">Learn More</a>
                    <?php endif; ?>
                </div>

                <!-- Vehicle Tracking Service -->
                <div class="service-card">
                    <div class="service-icon">🚌</div>
                    <h3>Vehicle Tracking</h3>
                    <p>Track campus buses in real-time. Get live updates on vehicle locations and estimated arrival times for all campus routes.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="app/views/tracking/index.php" class="btn btn-outline">Track Vehicles</a>
                    <?php else: ?>
                        <a href="app/views/auth/login.php" class="btn btn-outline">Learn More</a>
                    <?php endif; ?>
                </div>

                <!-- Feedback Service -->
                <div class="service-card">
                    <div class="service-icon">💬</div>
                    <h3>Feedback System</h3>
                    <p>Share your experiences and suggestions. Help us improve university services with your valuable feedback and ratings.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="app/views/feedback/index.php" class="btn btn-outline">Give Feedback</a>
                    <?php else: ?>
                        <a href="app/views/auth/login.php" class="btn btn-outline">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5000+</div>
                    <div class="stat-label">Bookings Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">25+</div>
                    <div class="stat-label">Vehicles Tracked</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include dirname(__DIR__) . '/app/views/layouts/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>