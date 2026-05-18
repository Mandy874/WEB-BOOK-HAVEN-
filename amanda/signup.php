<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($firstName) < 2 || strlen($lastName) < 2) {
        $error = 'Name must be at least 2 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $pdo = getDB();
        
        if ($pdo) {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Create username from email
                $username = strtolower(explode('@', $email)[0]);
                
                // Hash password
                $passwordHash = hashPassword($password);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
                
                if ($stmt->execute([$username, $email, $passwordHash, $firstName, $lastName, $phone])) {
                    $success = 'Account created successfully! Redirecting to login...';
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } else {
            $error = 'Database connection failed';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Book Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c5530 0%, #8b4513 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            width: 100%;
            max-width: 450px;
        }

        .signup-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .signup-header .logo i {
            font-size: 2.5rem;
            color: #2c5530;
        }

        .signup-header h1 {
            color: #2c5530;
            font-size: 2rem;
            margin: 0;
        }

        .signup-header p {
            color: #999;
            margin: 10px 0 0 0;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5530;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: 'Open Sans', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2c5530;
            box-shadow: 0 0 0 3px rgba(44, 85, 48, 0.1);
        }

        .form-group input::placeholder {
            color: #bbb;
        }

        .error-message {
            color: #d32f2f;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .input-error {
            border-color: #d32f2f !important;
            background-color: #ffebee;
        }

        .terms-group {
            margin: 25px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 6px;
        }

        .terms-group label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-weight: 400;
            margin: 0;
        }

        .terms-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-group label span {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .terms-group a {
            color: #2c5530;
            font-weight: 600;
        }

        .signup-btn {
            width: 100%;
            padding: 14px;
            background-color: #2c5530;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .signup-btn:hover {
            background-color: #8b4513;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.95rem;
        }

        .login-link a {
            color: #2c5530;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #8b4513;
        }

        .password-strength {
            margin-top: 8px;
            height: 4px;
            background-color: #ddd;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            background-color: #d32f2f;
            transition: width 0.3s ease;
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #999;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 500px) {
            .signup-card {
                padding: 40px 25px;
            }

            .signup-header h1 {
                font-size: 1.5rem;
            }

            .form-group input {
                padding: 10px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <!-- Header -->
            <div class="signup-header">
                <div class="logo">
                    <i class="fas fa-book-open"></i>
                </div>
                <h1>Book Haven</h1>
                <p>Create your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo sanitize($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo sanitize($success); ?></div>
            <?php endif; ?>

            <!-- Signup Form -->
            <form id="signupForm" method="POST" action="">
                <!-- First Name -->
                <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input 
                        type="text" 
                        id="firstName" 
                        name="firstName" 
                        placeholder="John"
                        required
                        value="<?php echo isset($_POST['firstName']) ? sanitize($_POST['firstName']) : ''; ?>"
                    >
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input 
                        type="text" 
                        id="lastName" 
                        name="lastName" 
                        placeholder="Doe"
                        required
                        value="<?php echo isset($_POST['lastName']) ? sanitize($_POST['lastName']) : ''; ?>"
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="your@email.com"
                        required
                        value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a strong password"
                        required
                    >
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password strength: Weak</div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        placeholder="Re-enter your password"
                        required
                    >
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="+1 (555) 123-4567"
                        required
                        value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>"
                    >
                </div>

                <!-- Submit Button -->
                <button type="submit" class="signup-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let strengthLevel = 'Weak';
            let strengthColor = '#d32f2f';

            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[!@#$%^&*]/.test(password)) strength += 10;

            if (strength < 25) {
                strengthLevel = 'Weak';
                strengthColor = '#d32f2f';
            } else if (strength < 50) {
                strengthLevel = 'Fair';
                strengthColor = '#ff9800';
            } else if (strength < 75) {
                strengthLevel = 'Good';
                strengthColor = '#2196f3';
            } else {
                strengthLevel = 'Strong';
                strengthColor = '#4caf50';
            }

            strengthBar.style.width = Math.min(strength, 100) + '%';
            strengthBar.style.backgroundColor = strengthColor;
            strengthText.textContent = 'Password strength: ' + strengthLevel;
            strengthText.style.color = strengthColor;
        });
    </script>
</body>
</html>