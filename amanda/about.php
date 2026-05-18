<?php
require_once 'config.php';

$pageTitle = 'About Us - Book Haven';
$pdo = getDB();

// Get statistics from database
$stats = [
    'books' => 0,
    'users' => 0,
    'orders' => 0
];

if ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books WHERE is_active = 1");
    $stats['books'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $stmt->fetch()['count'];
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
        .about-hero {
            background: linear-gradient(135deg, #2c5530 0%, #1a3d1c 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .about-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .about-content {
            padding: 60px 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .about-section {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 60px;
        }
        
        .about-section:nth-child(even) {
            flex-direction: row-reverse;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text h2 {
            color: #2c5530;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .about-text p {
            color: #555;
            line-height: 1.8;
        }
        
        .about-image {
            flex: 1;
            background: #f5f5f5;
            border-radius: 10px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #2c5530;
        }
        
        .stats-section {
            background: #2c5530;
            padding: 60px 0;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }
        
        .stat-item i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d4a96c;
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-item p {
            opacity: 0.9;
        }
        
        .team-section {
            padding: 60px 0;
            background: #f9f9f9;
        }
        
        .team-section h2 {
            text-align: center;
            color: #2c5530;
            font-size: 2.5rem;
            margin-bottom: 40px;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .team-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .team-card .avatar {
            width: 100px;
            height: 100px;
            background: #2c5530;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }
        
        .team-card h3 {
            color: #2c5530;
            margin-bottom: 5px;
        }
        
        .team-card .role {
            color: #8b4513;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .team-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .about-section {
                flex-direction: column !important;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                    <li><a href="about.php" class="active">About</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
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
    <section class="about-hero">
        <div class="container">
            <h1>About Book Haven</h1>
            <p>Your trusted destination for digital books and knowledge since 2020</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-section">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>Book Haven was founded with a simple mission: to make books accessible to everyone, everywhere. What started as a small online bookstore has grown into a comprehensive digital library offering over 100,000 titles across all genres.</p>
                    <br>
                    <p>We believe that knowledge is power, and books are the gateways to infinite possibilities. Our platform connects readers with their next favorite story, whether it's a bestselling novel, an educational textbook, or a niche technical manual.</p>
                </div>
                <div class="about-image">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
            
            <div class="about-section">
                <div class="about-text">
                    <h2>Our Mission</h2>
                    <p>We're committed to creating the best possible reading experience for book lovers worldwide. Our team works tirelessly to curate the finest collection of books, ensuring that every reader finds exactly what they're looking for.</p>
                    <br>
                    <p>From classic literature to modern bestsellers, from academic texts to self-improvement guides, Book Haven is your one-stop destination for all things books.</p>
                </div>
                <div class="about-image">
                    <i class="fas fa-bullseye"></i>
                </div>
            </div>
            
            <div class="about-section">
                <div class="about-text">
                    <h2>Why Choose Us?</h2>
                    <p><strong>Extensive Collection:</strong> Over 100,000 books across all genres and categories.</p>
                    <p><strong>Fast Delivery:</strong> Digital downloads available instantly after purchase.</p>
                    <p><strong>Secure Shopping:</strong> Safe and encrypted transactions protecting your data.</p>
                    <p><strong>24/7 Support:</strong> Our customer service team is always ready to help.</p>
                    <p><strong>Regular Updates:</strong> New titles added daily to keep your reading list fresh.</p>
                </div>
                <div class="about-image">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $stats['books']; ?>+</h3>
                    <p>Books Available</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $stats['users']; ?>+</h3>
                    <p>Happy Readers</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-shopping-cart"></i>
                    <h3><?php echo $stats['orders']; ?>+</h3>
                    <p>Orders Completed</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-globe"></i>
                    <h3>50+</h3>
                    <p>Countries Served</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Sarah Johnson</h3>
                    <p class="role">CEO & Founder</p>
                    <p>Passionate about books and technology</p>
                </div>
                <div class="team-card">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Michael Chen</h3>
                    <p class="role">Head of Operations</p>
                    <p>Ensures smooth delivery of services</p>
                </div>
                <div class="team-card">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Emily Davis</h3>
                    <p class="role">Customer Success</p>
                    <p>Dedicated to reader satisfaction</p>
                </div>
                <div class="team-card">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>James Wilson</h3>
                    <p class="role">Technical Lead</p>
                    <p>Building the best platform for readers</p>
                </div>
            </div>
        </div>
    </section>

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