<?php
session_start();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
            z-index: 1;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }
        
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
            }
        }
    </style>
</head>
<body>
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
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
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
    </script>
</body>
</html>