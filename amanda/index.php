<?php
require_once 'config.php';

// Get books from database
$books = [];
$pdo = getDB();

if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM books WHERE is_active = 1 ORDER BY created_at DESC");
    $books = $stmt->fetchAll();
}

// Get categories
$categories = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Haven | Digital Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
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
                    <li><a href="#home">Home</a></li>
                    <li><a href="#featured">Featured</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="user-actions">
                    <a href="#" class="cart-link" id="cartBtn"><i class="fas fa-shopping-cart"></i> <span id="cartCount">0</span></a>
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
    <section class="hero" id="home">
        <div class="container">
            <h2>Discover Your Next Favorite Book</h2>
            <p>Explore our vast collection of over 100,000 books from classic literature to modern bestsellers. Join our community of avid readers today.</p>
            <a href="signup.php" class="btn">Get Started</a>
        </div>
    </section>

    <!-- Featured Books -->
    <section class="featured-section" id="featured">
        <div class="container">
            <h2 class="section-title">Featured Books This Month</h2>
            <div class="featured-search">
                <i class="fas fa-search"></i>
                <input type="text" id="featuredSearch" placeholder="Search featured books...">
            </div>
            <div class="books-grid">
                <?php if (empty($books)): ?>
                    <p>No books available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if (!empty($book['cover_image'])): ?>
                                    <img src="<?php echo sanitize($book['cover_image']); ?>" alt="<?php echo sanitize($book['title']); ?>">
                                <?php else: ?>
                                    <img src="books/book<?php echo (array_search($book, $books) % 4) + 1; ?>.jpg" alt="<?php echo sanitize($book['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h3 class="book-title"><?php echo sanitize($book['title']); ?></h3>
                                <p class="book-author">by <?php echo sanitize($book['author']); ?></p>
                                <p class="book-price">$<?php echo number_format($book['price'], 2); ?></p>
                                <div class="book-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <button class="btn view-details" onclick="addToCart(<?php echo $book['id']; ?>)">Add to Cart</button>
                                <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn view-details">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories-section" id="categories">
        <div class="container">
            <h2 class="section-title">Browse by Category</h2>
            <div class="categories-grid">
                <?php if (empty($categories)): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>All Books</h3>
                        <p>Browse our complete collection</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card" onclick="filterByCategory(<?php echo $category['id']; ?>)">
                            <div class="category-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3><?php echo sanitize($category['name']); ?></h3>
                            <p><?php echo sanitize($category['description'] ?? 'Browse books in this category'); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="contact-grid">
                <div class="contact-info">
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
                </div>
                <form class="contact-form" method="POST" action="contact.php">
                    <input type="text" placeholder="Your Name" required>
                    <input type="email" placeholder="Your Email" required>
                    <textarea placeholder="Your Message" rows="5" required></textarea>
                    <button type="submit" class="btn">Send Message</button>
                </form>
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
                        <li><a href="#home">Home</a></li>
                        <li><a href="#featured">Featured</a></li>
                        <li><a href="#categories">Categories</a></li>
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
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function updateCartCount() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
        }

        function addToCart(bookId) {
            const existingItem = cart.find(item => item.bookId === bookId);
            
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ bookId: bookId, quantity: 1 });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            alert('Book added to cart!');
        }

        function filterByCategory(categoryId) {
            // Filter books by category
            window.location.href = 'index.php?category=' + categoryId;
        }

        // Initialize cart count on page load
        updateCartCount();
    </script>
</body>
</html>