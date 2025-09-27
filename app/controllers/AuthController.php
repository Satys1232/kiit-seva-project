<?php
/**
 * KIIT SEVA - Authentication Controller
 * Handles user login, registration, and session management
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/functions.php';
require_once dirname(__DIR__) . '/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Handle user login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitizeInput($_POST['email'], 'email');
            $password = $_POST['password'];
            
            // Validate input
            if (empty($email) || empty($password)) {
                return $this->loginView('Please fill in all fields');
            }
            
            // Authenticate user
            $user = $this->userModel->authenticateUser($email, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Log activity
                logActivity($user['id'], 'login', 'User logged in');
                
                // Redirect to dashboard
                redirect('/dashboard', 'Welcome back, ' . $user['name'] . '!', 'success');
            } else {
                return $this->loginView('Invalid email or password');
            }
        } else {
            return $this->loginView();
        }
    }
    
    /**
     * Handle user registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email'], 'email');
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = sanitizeInput($_POST['role']);
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email) || !validateEmail($email)) {
                $errors[] = 'Valid email is required';
            }
            
            if (empty($password) || !validatePassword($password)) {
                $errors[] = 'Password must be at least 8 characters with letters and numbers';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (!in_array($role, ['student', 'teacher', 'staff'])) {
                $errors[] = 'Please select a valid role';
            }
            
            // Check if email already exists
            if (empty($errors) && $this->userModel->getUserByEmail($email)) {
                $errors[] = 'Email already registered';
            }
            
            if (empty($errors)) {
                // Create user
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => hashPassword($password),
                    'role' => $role
                ];
                
                $userId = $this->userModel->createUser($userData);
                
                if ($userId) {
                    // Auto-login after registration
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;
                    
                    // Log activity
                    logActivity($userId, 'register', 'User registered');
                    
                    // Create teacher profile if role is teacher
                    if ($role === 'teacher') {
                        $this->createTeacherProfile($userId);
                    }
                    
                    redirect('/dashboard', 'Registration successful! Welcome to KIIT SEVA.', 'success');
                } else {
                    return $this->registerView('Registration failed. Please try again.');
                }
            } else {
                return $this->registerView(implode('<br>', $errors));
            }
        } else {
            return $this->registerView();
        }
    }
    
    /**
     * Handle user logout
     */
    public function logout() {
        if (isLoggedIn()) {
            logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        redirect('/', 'You have been logged out successfully.', 'info');
    }
    
    /**
     * Create teacher profile after registration
     */
    private function createTeacherProfile($userId) {
        try {
            $db = getKiitDatabase();
            $stmt = $db->query(
                "INSERT INTO teachers (user_id, department, available_slots, created_at) VALUES (?, ?, ?, NOW())",
                [$userId, 'General', '{}']
            );
        } catch (Exception $e) {
            error_log("Error creating teacher profile: " . $e->getMessage());
        }
    }
    
    /**
     * Display login view
     */
    private function loginView($error = '') {
        include dirname(__DIR__) . '/views/auth/login.php';
    }
    
    /**
     * Display registration view
     */
    private function registerView($error = '') {
        include dirname(__DIR__) . '/views/auth/register.php';
    }
}

// Handle requests
if (isset($_GET['action'])) {
    $auth = new AuthController();
    
    switch ($_GET['action']) {
        case 'login':
            $auth->login();
            break;
        case 'register':
            $auth->register();
            break;
        case 'logout':
            $auth->logout();
            break;
        default:
            redirect('/');
            break;
    }
} else {
    redirect('/');
}
?>