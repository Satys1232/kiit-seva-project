<?php
<<<<<<< HEAD
session_start();
$flash = getFlashMessage();
=======
/**
 * KIIT SEVA - Login Page
 * Professional login interface with role-based authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helper functions
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    $dashboardUrl = match($user['role']) {
        'student' => '/app/views/dashboard/student.php',
        'teacher' => '/app/views/dashboard/teacher.php',
        'staff' => '/app/views/dashboard/staff.php',
        default => '/public/'
    };
    redirect($dashboardUrl);
}

$title = $title ?? 'Sign In - KIIT SEVA';
$csrf_token = $csrf_token ?? generateCSRFToken();
$email = $email ?? '';
$error = $error ?? '';
$flashMessages = getFlashMessages();
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Login - KIIT SEVA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: #4a90e2;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .login-form {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .btn:hover {
            background: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            color: #666;
        }
        
        .divider::before {
=======
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/app.css">
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/responsive.css">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }
        
        .auth-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-2xl);
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .auth-logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--medium-gray);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all var(--transition-normal);
            background-color: var(--white);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-red);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.125rem;
            color: var(--text-muted);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
        }
        
        .form-check-label {
            margin: 0;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-auth {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all var(--transition-normal);
        }
        
        .auth-divider {
            text-align: center;
            margin: var(--spacing-lg) 0;
            position: relative;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .auth-divider::before {
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
<<<<<<< HEAD
            background: #e1e5e9;
            z-index: 1;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
=======
            background: var(--medium-gray);
            z-index: 1;
        }
        
        .auth-divider span {
            background: var(--white);
            padding: 0 var(--spacing-md);
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
            position: relative;
            z-index: 2;
        }
        
<<<<<<< HEAD
        .google-btn {
            background: white;
            color: #333;
            border: 2px solid #e1e5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .google-btn:hover {
            background: #f8f9fa;
            border-color: #4a90e2;
            transform: translateY(-2px);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .remember-me input[type="checkbox"] {
            width: auto;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
=======
        .auth-links {
            text-align: center;
            margin-top: var(--spacing-lg);
        }
        
        .auth-links a {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .auth-links a:hover {
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
            text-decoration: underline;
        }
        
        .alert {
<<<<<<< HEAD
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }
        
        .alert-info {
            background: #eef;
            color: #336;
            border: 1px solid #ccf;
        }
        
        .role-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .role-info strong {
            color: #4a90e2;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
=======
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-xl);
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--medium-gray);
            border-top: 4px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: var(--spacing-lg);
                margin: var(--spacing-sm);
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
            }
        }
    </style>
</head>
<body>
<<<<<<< HEAD
    <div class="login-container">
        <div class="login-header">
            <h1>KIIT SEVA</h1>
            <p>Student Services Platform</p>
        </div>
        
        <div class="login-form">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error) && !empty($error)): ?>
=======
    <div class="auth-container">
        <div class="auth-card">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>
            
            <div class="auth-header">
                <div class="auth-logo">KIIT SEVA</div>
                <p class="auth-subtitle">Sign in to your account</p>
            </div>

            <!-- Flash Messages -->
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="alert alert-<?php echo $type === 'error' ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
<<<<<<< HEAD
            
            <div class="role-info">
                <strong>Demo Accounts:</strong><br>
                Student: student@kiit.ac.in / password123<br>
                Teacher: teacher@kiit.ac.in / password123<br>
                Staff: staff@kiit.ac.in / password123
            </div>
            
            <form method="POST" action="/auth/login">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your KIIT email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn">Sign In</button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <button class="btn google-btn">
                <span>🔍</span>
                Continue with Google
            </button>
            
            <div class="links">
                <p>Don't have an account? <a href="/kiit-seva-project/app/views/auth/register.php">Sign up here</a></p>
                <p><a href="#" onclick="alert('Please contact admin for password reset')">Forgot your password?</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus on first input
        document.getElementById('email').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        });
        
        // Google login placeholder
        document.querySelector('.google-btn').addEventListener('click', function() {
            alert('Google login will be implemented in future version');
        });
=======

            <form id="loginForm" method="POST" action="<?php echo getBaseUrl(); ?>/app/controllers/AuthController.php?action=login" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($email); ?>"
                        required 
                        autocomplete="email"
                        placeholder="your.email@kiit.ac.in"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                    <label for="remember_me" class="form-check-label">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="auth-links">
                <p>Don't have an account? <a href="<?php echo getBaseUrl(); ?>/app/views/auth/register.php">Sign up here</a></p>
                <p><a href="<?php echo getBaseUrl(); ?>/app/controllers/AuthController.php?action=forgot-password">Forgot your password?</a></p>
                <p><a href="<?php echo getBaseUrl(); ?>/public/">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="<?php echo getBaseUrl(); ?>/assets/js/app.js"></script>
    <script>
        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                toggle.textContent = '🙈';
            } else {
                input.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        // Enhanced form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const loadingOverlay = document.getElementById('loadingOverlay');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Show loading state
            loadingOverlay.style.display = 'flex';
            submitButton.disabled = true;
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(data.message || 'Login successful!', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '/app/views/dashboard/';
                    }, 1000);
                } else {
                    // Show error message
                    showNotification(data.message || 'Login failed. Please try again.', 'error');
                    
                    // Hide loading state
                    loadingOverlay.style.display = 'none';
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                
                // Hide loading state
                loadingOverlay.style.display = 'none';
                submitButton.disabled = false;
            });
        });

        // Show notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'error' : 'success'}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1060;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Auto-focus email field
        document.getElementById('email').focus();
>>>>>>> 65cb8454c82b5740693ef75febffc177411e1f9d
    </script>
</body>
</html>