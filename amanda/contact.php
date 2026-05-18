<?php
require_once 'config.php';

$pageTitle = 'Contact Us - Book Haven';
$pdo = getDB();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In production, you would save this to a database or send an email
        $success = 'Thank you for your message! We will get back to you within 24 hours.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            padding-top: 80px;
        }
        
        .contact-hero {
            background: linear-gradient(135deg, #2c5530 0%, #1a3d1c 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .contact-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .contact-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .contact-info h2 {
            color: #2c5530;
            margin-bottom: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .contact-item i {
            font-size: 1.5rem;
            color: #2c5530;
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .contact-item h3 {
            color: #2c5530;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .contact-item p {
            color: #666;
        }
        
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .contact-form h2 {
            color: #2c5530;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c5530;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: 'Open Sans', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c5530;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #2c5530;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .submit-btn:hover {
            background: #8b4513;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .map-section {
            margin-top: 50px;
        }
        
        .map-placeholder {
            background: #f5f5f5;
            height: 300px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <h1>Book <span>Haven</span></h1>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#featured">Featured</a></li>
                    <li><a href="index.php#categories">Categories</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="user-actions">
                    <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> <span id="cartCount">0</span></a>
                    <?php if (isLoggedIn()): ?>
                        <a href="#" class="auth-link"><i class="fas fa-user"></i> <?php echo sanitize($_SESSION['first_name'] ?? 'User'); ?></a>
                        <a href="logout.php" class="auth-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="auth-link login"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="signup.php" class="auth-link signup"><i class="fas fa-user-plus"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Get in touch with our team.</p>
        </div>
    </section>

    <!-- Contact Content -->
    <div class="contact-container">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo sanitize($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2><i class="fas fa-envelope"></i> Get in Touch</h2>
                
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h3>Address</h3>
                        <p>123 Book Street, Library City, LC 12345</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Phone</h3>
                        <p>+1 (555) 123-4567</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email</h3>
                        <p>info@bookhaven.com</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Business Hours</h3>
                        <p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: 10:00 AM - 4:00 PM</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-comments"></i>
                    <div>
                        <h3>Live Chat</h3>
                        <p>Available 24/7 for instant support</p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form">
                <h2><i class="fas fa-paper-plane"></i> Send a Message</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject">
                            <option value="general">General Inquiry</option>
                            <option value="support">Customer Support</option>
                            <option value="orders">Order Related</option>
                            <option value="partnership">Partnership</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? sanitize($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="map-section">
            <div class="map-placeholder">
                <i class="fas fa-map"></i> Map will be displayed here
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Book Haven</h3>
                    <p>Your trusted source for digital books. Join thousands of readers discovering new stories every day.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#featured">Featured</a></li>
                        <li><a href="index.php#categories">Categories</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Account</h3>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                        <li><a href="#">My Account</a></li>
                        <li><a href="#">Order History</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Book Haven. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Update cart count on page load
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    </script>
</body>
</html>