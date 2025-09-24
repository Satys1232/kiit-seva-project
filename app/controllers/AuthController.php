<?php
/**
 * KIIT SEVA - Authentication Controller
 * Handles user registration, login, logout, and session management
 */

require_once dirname(__DIR__) . '/models/BaseModel.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/functions.php';

class AuthController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Handle user login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processLogin();
        }

        // Show login form
        $this->render('auth/login', [
            'title' => 'Sign In - KIIT SEVA',
            'csrf_token' => generateCSRFToken()
        ]);
    }

    /**
     * Process login form submission
     */
    private function processLogin()
    {
        try {
            // Validate CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Invalid security token. Please try again.');
            }

            // Sanitize input
            $email = sanitizeInput($_POST['email'] ?? '', 'email');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            // Validate required fields
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required.');
            }

            // Validate email format
            if (!validateEmail($email)) {
                throw new Exception('Please enter a valid email address.');
            }

            // Authenticate user
            $user = $this->userModel->authenticateUser($email, $password);
            
            if (!$user) {
                throw new Exception('Invalid email or password.');
            }

            // Check if user is active
            if (!$user['is_active']) {
                throw new Exception('Your account has been deactivated. Please contact support.');
            }

            // Start user session
            $this->startUserSession($user, $rememberMe);

            // Log successful login
            logActivity($user['id'], 'login', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // Update last login timestamp
            $this->userModel->updateLastLogin($user['id']);

            // Redirect to appropriate dashboard
            $dashboardUrl = $this->getDashboardUrl($user['role']);
            
            if (isAjaxRequest()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $dashboardUrl
                ]);
            } else {
                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                redirect($dashboardUrl);
            }

        } catch (Exception $e) {
            if (isAjaxRequest()) {
                jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            } else {
                setFlashMessage('error', $e->getMessage());
                $this->render('auth/login', [
                    'title' => 'Sign In - KIIT SEVA',
                    'csrf_token' => generateCSRFToken(),
                    'email' => $email ?? '',
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle user registration
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processRegistration();
        }

        // Show registration form
        $this->render('auth/register', [
            'title' => 'Sign Up - KIIT SEVA',
            'csrf_token' => generateCSRFToken()
        ]);
    }

    /**
     * Process registration form submission
     */
    private function processRegistration()
    {
        try {
            // Validate CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Invalid security token. Please try again.');
            }

            // Sanitize input
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '', 'email');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $role = sanitizeInput($_POST['role'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');

            // Validate required fields
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                throw new Exception('All fields are required.');
            }

            // Validate name
            if (strlen($name) < 2 || strlen($name) > 100) {
                throw new Exception('Name must be between 2 and 100 characters.');
            }

            // Validate email
            if (!validateEmail($email)) {
                throw new Exception('Please enter a valid email address.');
            }

            // Validate KIIT email domain
            if (!validateKiitEmail($email)) {
                throw new Exception('Please use your KIIT university email address.');
            }

            // Validate password
            if (strlen($password) < 8) {
                throw new Exception('Password must be at least 8 characters long.');
            }

            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match.');
            }

            // Validate role
            $allowedRoles = ['student', 'teacher', 'staff'];
            if (!in_array($role, $allowedRoles)) {
                throw new Exception('Please select a valid role.');
            }

            // Validate phone number if provided
            if (!empty($phone) && !validatePhoneNumber($phone)) {
                throw new Exception('Please enter a valid phone number.');
            }

            // Check if email already exists
            if ($this->userModel->emailExists($email)) {
                throw new Exception('An account with this email already exists.');
            }

            // Create user account
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'phone' => $phone,
                'is_active' => true,
                'email_verified' => false
            ];

            $userId = $this->userModel->createUser($userData);

            if (!$userId) {
                throw new Exception('Failed to create account. Please try again.');
            }

            // Create role-specific profile if needed
            if ($role === 'teacher') {
                $this->createTeacherProfile($userId);
            }

            // Log registration
            logActivity($userId, 'register', [
                'role' => $role,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Auto-login the user
            $user = $this->userModel->getUserById($userId);
            $this->startUserSession($user);

            $dashboardUrl = $this->getDashboardUrl($role);

            if (isAjaxRequest()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Account created successfully',
                    'redirect' => $dashboardUrl
                ]);
            } else {
                setFlashMessage('success', 'Welcome to KIIT SEVA, ' . htmlspecialchars($name) . '! Your account has been created successfully.');
                redirect($dashboardUrl);
            }

        } catch (Exception $e) {
            if (isAjaxRequest()) {
                jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            } else {
                setFlashMessage('error', $e->getMessage());
                $this->render('auth/register', [
                    'title' => 'Sign Up - KIIT SEVA',
                    'csrf_token' => generateCSRFToken(),
                    'name' => $name ?? '',
                    'email' => $email ?? '',
                    'role' => $role ?? '',
                    'phone' => $phone ?? '',
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle user logout
     */
    public function logout()
    {
        if (isLoggedIn()) {
            $user = getCurrentUser();
            
            // Log logout activity
            if ($user) {
                logActivity($user['id'], 'logout', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            }
        }

        // Destroy session
        session_unset();
        session_destroy();

        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        setFlashMessage('success', 'You have been logged out successfully.');
        redirect('/public/');
    }

    /**
     * Dashboard routing based on user role
     */
    public function dashboard()
    {
        requireLogin();
        
        $user = getCurrentUser();
        if (!$user) {
            redirect('/app/views/auth/login.php');
        }

        $dashboardUrl = $this->getDashboardUrl($user['role']);
        redirect($dashboardUrl);
    }

    /**
     * Start user session
     */
    private function startUserSession($user, $rememberMe = false)
    {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            
            // Store token in database (implement remember_tokens table if needed)
            // $this->userModel->storeRememberToken($user['id'], $token);
        }
    }

    /**
     * Get dashboard URL based on user role
     */
    private function getDashboardUrl($role)
    {
        $baseUrl = getBaseUrl();
        
        switch ($role) {
            case 'student':
                return $baseUrl . '/app/views/dashboard/student.php';
            case 'teacher':
                return $baseUrl . '/app/views/dashboard/teacher.php';
            case 'staff':
                return $baseUrl . '/app/views/dashboard/staff.php';
            default:
                return $baseUrl . '/public/';
        }
    }

    /**
     * Create teacher profile
     */
    private function createTeacherProfile($userId)
    {
        try {
            require_once dirname(__DIR__) . '/models/Teacher.php';
            $teacherModel = new Teacher();
            
            $teacherData = [
                'user_id' => $userId,
                'department' => '',
                'chamber_no' => '',
                'available_slots' => json_encode([
                    'monday' => [],
                    'tuesday' => [],
                    'wednesday' => [],
                    'thursday' => [],
                    'friday' => []
                ])
            ];
            
            $teacherModel->createTeacher($teacherData);
        } catch (Exception $e) {
            error_log("Failed to create teacher profile: " . $e->getMessage());
        }
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processForgotPassword();
        }

        $this->render('auth/forgot-password', [
            'title' => 'Forgot Password - KIIT SEVA',
            'csrf_token' => generateCSRFToken()
        ]);
    }

    /**
     * Process forgot password request
     */
    private function processForgotPassword()
    {
        try {
            // Validate CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Invalid security token. Please try again.');
            }

            $email = sanitizeInput($_POST['email'] ?? '', 'email');

            if (empty($email) || !validateEmail($email)) {
                throw new Exception('Please enter a valid email address.');
            }

            // Check if user exists
            $user = $this->userModel->getUserByEmail($email);
            
            if ($user) {
                // Generate password reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token (implement password_resets table if needed)
                // $this->userModel->storePasswordResetToken($user['id'], $token, $expiry);
                
                // Send password reset email (implement email functionality)
                // $this->sendPasswordResetEmail($user, $token);
            }

            // Always show success message for security
            setFlashMessage('success', 'If an account with that email exists, we have sent password reset instructions.');
            redirect('/app/views/auth/login.php');

        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            $this->render('auth/forgot-password', [
                'title' => 'Forgot Password - KIIT SEVA',
                'csrf_token' => generateCSRFToken(),
                'email' => $email ?? '',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check authentication status (AJAX endpoint)
     */
    public function checkAuth()
    {
        $isLoggedIn = isLoggedIn();
        $user = $isLoggedIn ? getCurrentUser() : null;

        jsonResponse([
            'authenticated' => $isLoggedIn,
            'user' => $user
        ]);
    }
}

// Handle direct requests to this controller
if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
    $controller = new AuthController();
    $action = $_GET['action'] ?? 'login';

    switch ($action) {
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'forgot-password':
            $controller->forgotPassword();
            break;
        case 'check-auth':
            $controller->checkAuth();
            break;
        default:
            $controller->login();
    }
}