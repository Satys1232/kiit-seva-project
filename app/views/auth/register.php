<?php
session_start();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KIIT SEVA</title>
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
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            background: #4a90e2;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .register-header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .register-form {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .terms-checkbox input[type="checkbox"] {
            width: auto;
            margin-top: 3px;
        }
        
        .terms-checkbox label {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .terms-checkbox a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
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
            text-decoration: underline;
        }
        
        .alert {
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
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.8rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        .role-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .role-info h4 {
            color: #4a90e2;
            margin-bottom: 10px;
        }
        
        .role-info ul {
            margin-left: 20px;
        }
        
        .role-info li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 480px) {
            .register-container {
                margin: 10px;
            }
            
            .register-header {
                padding: 25px 20px;
            }
            
            .register-form {
                padding: 30px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Join KIIT SEVA</h1>
            <p>Create your account to get started</p>
        </div>
        
        <div class="register-form">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="role-info">
                <h4>Choose Your Role:</h4>
                <ul>
                    <li><strong>Student:</strong> Book teacher appointments, track vehicles, submit feedback</li>
                    <li><strong>Teacher:</strong> Manage availability, view bookings, respond to feedback</li>
                    <li><strong>Staff:</strong> Update vehicle locations, manage duty status</li>
                </ul>
            </div>
            
            <form method="POST" action="/auth/register" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your KIIT email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Create password">
                        <div id="passwordStrength" class="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Select Your Role</label>
                    <select id="role" name="role" required>
                        <option value="">Choose your role...</option>
                        <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>
                
                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#" onclick="showTerms()">Terms of Service</a> 
                        and <a href="#" onclick="showPrivacy()">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">Create Account</button>
            </form>
            
            <div class="links">
                <p>Already have an account? <a href="/kiit-seva-project/app/views/auth/login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) strength++;
            else feedback.push('at least 8 characters');
            
            // Letter check
            if (/[a-zA-Z]/.test(password)) strength++;
            else feedback.push('letters');
            
            // Number check
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('numbers');
            
            // Special character check
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength < 2) {
                strengthDiv.textContent = 'Weak - Need: ' + feedback.join(', ');
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength < 3) {
                strengthDiv.textContent = 'Medium - Consider adding special characters';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = 'Strong password!';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Please accept the terms and conditions');
                return;
            }
            
            // Disable submit button to prevent double submission
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Creating Account...';
        });
        
        // Terms and Privacy modals (placeholder)
        function showTerms() {
            alert('Terms of Service:\n\n1. Use KIIT SEVA responsibly\n2. Provide accurate information\n3. Respect other users\n4. Follow university guidelines\n\n(Full terms will be available in production)');
        }
        
        function showPrivacy() {
            alert('Privacy Policy:\n\n1. We protect your personal data\n2. Information is used only for platform services\n3. Data is not shared with third parties\n4. You can request data deletion\n\n(Full policy will be available in production)');
        }
        
        // Auto-focus on first input
        document.getElementById('name').focus();
    </script>
</body>
</html>