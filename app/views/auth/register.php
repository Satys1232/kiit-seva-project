<?php
/**
 * KIIT SEVA - Registration Page
 * Professional registration interface with role selection
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

$title = $title ?? 'Sign Up - KIIT SEVA';
$csrf_token = $csrf_token ?? generateCSRFToken();
$name = $name ?? '';
$email = $email ?? '';
$role = $role ?? '';
$phone = $phone ?? '';
$error = $error ?? '';
$flashMessages = getFlashMessages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            max-width: 450px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
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
        
        .form-control.is-valid {
            border-color: var(--success-green);
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
        
        .password-strength {
            margin-top: var(--spacing-sm);
        }
        
        .strength-bar {
            height: 4px;
            background: var(--light-gray);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-fill {
            height: 100%;
            transition: all var(--transition-normal);
        }
        
        .strength-text {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .role-selection {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .role-option {
            position: relative;
        }
        
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
        }
        
        .role-label {
            display: block;
            padding: var(--spacing-md);
            border: 2px solid var(--medium-gray);
            border-radius: var(--radius-md);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-normal);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .role-option input[type="radio"]:checked + .role-label {
            border-color: var(--primary-blue);
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary-blue);
        }
        
        .role-icon {
            font-size: 1.5rem;
            display: block;
            margin-bottom: var(--spacing-xs);
        }
        
        .form-check {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            margin-top: 2px;
        }
        
        .form-check-label {
            margin: 0;
            cursor: pointer;
            font-size: 0.875rem;
            line-height: 1.4;
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
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--medium-gray);
            z-index: 1;
        }
        
        .auth-divider span {
            background: var(--white);
            padding: 0 var(--spacing-md);
            position: relative;
            z-index: 2;
        }
        
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
            text-decoration: underline;
        }
        
        .alert {
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
        
        .invalid-feedback {
            color: var(--danger-red);
            font-size: 0.75rem;
            margin-top: var(--spacing-xs);
            display: block;
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .role-selection {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: var(--spacing-lg);
                margin: var(--spacing-sm);
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>
            
            <div class="auth-header">
                <div class="auth-logo">KIIT SEVA</div>
                <p class="auth-subtitle">Create your account</p>
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
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="<?php echo getBaseUrl(); ?>/app/controllers/AuthController.php?action=register" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($name); ?>"
                        required 
                        autocomplete="name"
                        placeholder="Enter your full name"
                        minlength="2"
                        maxlength="100"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">KIIT Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            required 
                            autocomplete="email"
                            placeholder="your.email@kiit.ac.in"
                            pattern="[a-zA-Z0-9._%+-]+@(kiit\.ac\.in|ksom\.ac\.in|kiss\.ac\.in|kims\.ac\.in)"
                            data-error-message="Please use your KIIT university email address"
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($phone); ?>"
                            autocomplete="tel"
                            placeholder="9876543210"
                            pattern="[6-9][0-9]{9}"
                            data-error-message="Please enter a valid Indian mobile number"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Your Role</label>
                    <div class="role-selection">
                        <div class="role-option">
                            <input type="radio" id="role_student" name="role" value="student" <?php echo $role === 'student' ? 'checked' : ''; ?> required>
                            <label for="role_student" class="role-label">
                                <span class="role-icon">🎓</span>
                                Student
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="role_teacher" name="role" value="teacher" <?php echo $role === 'teacher' ? 'checked' : ''; ?>>
                            <label for="role_teacher" class="role-label">
                                <span class="role-icon">👨‍🏫</span>
                                Teacher
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="role_staff" name="role" value="staff" <?php echo $role === 'staff' ? 'checked' : ''; ?>>
                            <label for="role_staff" class="role-label">
                                <span class="role-icon">👷‍♂️</span>
                                Staff
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                required 
                                autocomplete="new-password"
                                placeholder="Create password"
                                minlength="8"
                                data-strength
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control" 
                                required 
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                    <label for="terms" class="form-check-label">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo getBaseUrl(); ?>/app/views/auth/login.php">Sign in here</a></p>
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

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            updatePasswordStrength(this);
        });

        function updatePasswordStrength(input) {
            const password = input.value;
            const strength = calculatePasswordStrength(password);
            
            let strengthIndicator = document.getElementById('passwordStrength');
            if (!strengthIndicator) return;
            
            const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const strengthColors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
            
            if (password.length === 0) {
                strengthIndicator.innerHTML = '';
                return;
            }
            
            strengthIndicator.innerHTML = `
                <div class="strength-bar">
                    <div class="strength-fill" style="width: ${strength * 20}%; background-color: ${strengthColors[strength - 1] || '#dc3545'}"></div>
                </div>
                <small class="strength-text">${strengthLevels[strength - 1] || 'Very Weak'}</small>
            `;
        }

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('is-invalid');
                showFieldError(this, 'Passwords do not match');
            } else {
                this.classList.remove('is-invalid');
                clearFieldError(this);
                if (confirmPassword) {
                    this.classList.add('is-valid');
                }
            }
        });

        // Enhanced form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const loadingOverlay = document.getElementById('loadingOverlay');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Validate passwords match
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            if (password !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
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
                    showNotification(data.message || 'Account created successfully!', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '/app/views/dashboard/';
                    }, 1500);
                } else {
                    // Show error message
                    showNotification(data.message || 'Registration failed. Please try again.', 'error');
                    
                    // Hide loading state
                    loadingOverlay.style.display = 'none';
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
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

        // Field error handling
        function showFieldError(field, message) {
            clearFieldError(field);
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }

        function clearFieldError(field) {
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
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

        // Auto-focus name field
        document.getElementById('name').focus();
    </script>
</body>
</html>