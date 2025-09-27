<?php
/**
 * KIIT SEVA - Main Entry Point
<<<<<<< HEAD
 * Professional homepage with routing and authentication
 */

session_start();

// Include required files
require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/functions.php';

// Simple routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/kiit-seva-project/public', '', $path);

// Handle routing
switch ($path) {
    case '/':
    case '/home':
        showHomepage();
        break;
    case '/login':
        include dirname(__DIR__) . '/app/views/auth/login.php';
        break;
    case '/register':
        include dirname(__DIR__) . '/app/views/auth/register.php';
        break;
    case '/auth/login':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_once dirname(__DIR__) . '/app/models/User.php';
            $email = sanitizeInput($_POST['email'] ?? '', 'email');
            $password = $_POST['password'] ?? '';
            if (empty($email) || empty($password)) {
                $_SESSION['flash_message'] = 'Please fill in all fields';
                $_SESSION['flash_type'] = 'error';
                header('Location: /login');
                exit;
            }
            $userModel = new User();
            $user = $userModel->getUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: /dashboard');
                exit;
            }
            $_SESSION['flash_message'] = 'Invalid email or password';
            $_SESSION['flash_type'] = 'error';
            header('Location: /login');
            exit;
        }
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case '/auth/register':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_once dirname(__DIR__) . '/app/models/User.php';
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '', 'email');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $role = sanitizeInput($_POST['role'] ?? '');
            $errors = [];
            if (!$name) $errors[] = 'Name is required';
            if (!$email || !validateEmail($email)) $errors[] = 'Valid email is required';
            if (!validatePassword($password)) $errors[] = 'Password must be at least 8 characters with letters and numbers';
            if ($password !== $confirm) $errors[] = 'Passwords do not match';
            if (!in_array($role, ['student', 'teacher', 'staff'])) $errors[] = 'Please select a valid role';
            $userModel = new User();
            if (empty($errors) && $userModel->getUserByEmail($email)) $errors[] = 'Email already registered';
            if (!empty($errors)) {
                $_SESSION['flash_message'] = implode("\n", $errors);
                $_SESSION['flash_type'] = 'error';
                header('Location: /register');
                exit;
            }
            $userId = $userModel->createUser([
                'name' => $name,
                'email' => $email,
                'password' => hashPassword($password),
                'role' => $role
            ]);
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                header('Location: /dashboard');
                exit;
            }
            $_SESSION['flash_message'] = 'Registration failed. Please try again.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /register');
            exit;
        }
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case '/dashboard':
        requireLogin();
        showDashboard();
        break;
    case '/booking':
        requireLogin();
        include dirname(__DIR__) . '/app/views/booking/index.php';
        break;
    case '/booking/create':
        requireLogin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_once dirname(__DIR__) . '/app/models/Booking.php';
            $studentId = $_SESSION['user_id'];
            $teacherId = (int)($_POST['teacher_id'] ?? 0);
            $bookingDate = $_POST['booking_date'] ?? '';
            $timeSlot = $_POST['time_slot'] ?? '';
            $purpose = trim($_POST['purpose'] ?? '');
            if ($teacherId && $bookingDate && $timeSlot && $purpose) {
                $booking = new Booking();
                $result = $booking->createBooking([
                    'student_id' => $studentId,
                    'teacher_id' => $teacherId,
                    'booking_date' => $bookingDate,
                    'time_slot' => $timeSlot,
                    'purpose' => $purpose,
                    'status' => 'booked'
                ]);
                if ($result !== false) {
                    $_SESSION['flash_message'] = 'Booking created successfully!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Selected slot is not available.';
                    $_SESSION['flash_type'] = 'error';
                }
            } else {
                $_SESSION['flash_message'] = 'Please fill all booking details.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: /booking');
            exit;
        }
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case '/tracking':
        requireLogin();
        include dirname(__DIR__) . '/app/views/tracking/index.php';
        break;
    case '/api/vehicles':
        requireLogin();
        header('Content-Type: application/json');
        require_once dirname(__DIR__) . '/app/models/Vehicle.php';
        $routeParam = $_GET['route'] ?? null;
        $vehicleModel = new Vehicle();
        $data = $vehicleModel->getActiveVehicles($routeParam);
        echo json_encode(['data' => $data ?: []]);
        exit;
    case '/feedback':
        requireLogin();
        include dirname(__DIR__) . '/app/views/feedback/index.php';
        break;
    case '/feedback/create':
        requireLogin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $db = getKiitDatabase();
                $userId = $_SESSION['user_id'];
                $rating = (int)($_POST['rating'] ?? 0);
                $category = $_POST['category'] ?? '';
                $subject = trim($_POST['subject'] ?? '');
                $message = trim($_POST['message'] ?? '');
                $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
                if ($rating >= 1 && $rating <= 5 && $category && $subject && $message) {
                    $db->query(
                        "INSERT INTO feedback (user_id, category, subject, message, rating, is_anonymous, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                        [$userId, $category, $subject, $message, $rating, $isAnonymous]
                    );
                    $_SESSION['flash_message'] = 'Feedback submitted successfully!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Please complete all required fields.';
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Failed to submit feedback.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: /feedback');
            exit;
        }
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case '/logout':
        handleLogout();
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}

function showHomepage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>KIIT SEVA - Student Services Platform</title>
        <link rel="stylesheet" href="assets/css/app.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            
            .hero {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 100px 0;
                text-align: center;
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }
            
            .hero h1 {
                font-size: 3.5rem;
                margin-bottom: 20px;
                font-weight: 700;
            }
            
            .hero p {
                font-size: 1.3rem;
                margin-bottom: 40px;
                opacity: 0.9;
            }
            
            .btn {
                display: inline-block;
                padding: 15px 30px;
                margin: 10px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .btn-primary {
                background: #4a90e2;
                color: white;
            }
            
            .btn-secondary {
                background: transparent;
                color: white;
                border: 2px solid white;
            }
            
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            
            .services {
                padding: 80px 0;
                background: #f8f9fa;
            }
            
            .services h2 {
                text-align: center;
                font-size: 2.5rem;
                margin-bottom: 60px;
                color: #333;
            }
            
            .service-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 40px;
            }
            
            .service-card {
                background: white;
                padding: 40px 30px;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
            }
            
            .service-card:hover {
                transform: translateY(-5px);
            }
            
            .service-icon {
                font-size: 3rem;
                margin-bottom: 20px;
            }
            
            .service-card h3 {
                font-size: 1.5rem;
                margin-bottom: 15px;
                color: #4a90e2;
            }
            
            .service-card p {
                color: #666;
                margin-bottom: 25px;
            }
            
            .navbar {
                background: white;
                padding: 15px 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                position: fixed;
                width: 100%;
                top: 0;
                z-index: 1000;
            }
            
            .nav-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .logo {
                font-size: 1.8rem;
                font-weight: 700;
                color: #4a90e2;
            }
            
            .nav-links {
                display: flex;
                gap: 30px;
            }
            
            .nav-links a {
                text-decoration: none;
                color: #333;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            
            .nav-links a:hover {
                color: #4a90e2;
            }
            
            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 2.5rem;
                }
                
                .hero p {
                    font-size: 1.1rem;
                }
                
                .service-grid {
                    grid-template-columns: 1fr;
                }
                
                .nav-links {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar">
            <div class="container">
                <div class="nav-container">
                    <div class="logo">KIIT SEVA</div>
                    <div class="nav-links">
                        <a href="#services">Services</a>
                        <a href="/login">Login</a>
                        <a href="/register">Sign Up</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <section class="hero">
            <div class="container">
                <h1>KIIT SEVA</h1>
                <p>Your Trusted Student Services Platform</p>
                <a href="/register" class="btn btn-primary">Get Started</a>
                <a href="/login" class="btn btn-secondary">Sign In</a>
            </div>
        </section>
        
        <section class="services" id="services">
            <div class="container">
                <h2>Our Services</h2>
                <div class="service-grid">
                    <div class="service-card">
                        <div class="service-icon">👨🏫</div>
                        <h3>Teacher Booking</h3>
                        <p>Book appointments with teachers easily. View available slots and schedule meetings with your professors.</p>
                        <a href="/login" class="btn btn-primary">Book Now</a>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">🚌</div>
                        <h3>Vehicle Tracking</h3>
                        <p>Track campus buses in real-time. Know exactly when your bus will arrive at your stop.</p>
                        <a href="/login" class="btn btn-primary">Track Vehicles</a>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">💬</div>
                        <h3>Feedback System</h3>
                        <p>Share your feedback and help improve university services. Rate and review your experiences.</p>
                        <a href="/login" class="btn btn-primary">Give Feedback</a>
                    </div>
                </div>
            </div>
        </section>
    </body>
    </html>
    <?php
}

function showDashboard() {
    $role = $_SESSION['user_role'] ?? 'student';
    
    switch ($role) {
        case 'student':
            include dirname(__DIR__) . '/app/views/dashboard/student.php';
            break;
        case 'teacher':
            include dirname(__DIR__) . '/app/views/dashboard/teacher.php';
            break;
        case 'staff':
            include dirname(__DIR__) . '/app/views/dashboard/staff.php';
            break;
        default:
            include dirname(__DIR__) . '/app/views/dashboard/student.php';
            break;
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

function handleLogout() {
    session_destroy();
    header('Location: /');
    exit;
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getKiitDatabase();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}
?>
=======
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
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
