<?php
require_once 'config.php';

$pageTitle = 'Book Details - Book Haven';
$pdo = getDB();

$book = null;
$reviews = [];

// Get book ID from URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bookId > 0 && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_active = 1");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    
    // Get reviews for this book
    $stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.book_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$bookId]);
    $reviews = $stmt->fetchAll();
    
    $pageTitle = $book ? $book['title'] . ' - Book Haven' : 'Book Not Found';
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $bookId = intval($_POST['book_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($bookId > 0) {
        // This will be handled by JavaScript localStorage
        $addedToCart = true;
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
        .book-details-section {
            padding: 120px 0 60px;
        }
        
        .book-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 50px;
            align-items: start;
        }
        
        .book-details-cover {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .book-details-cover img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .book-details-info {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .book-details-info h1 {
            font-size: 2.5rem;
            color: #2c5530;
            margin-bottom: 10px;
        }
        
        .book-details-info .author {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .book-meta {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .meta-item i {
            font-size: 1.5rem;
            color: #2c5530;
            margin-bottom: 5px;
        }
        
        .meta-item span {
            font-size: 0.9rem;
            color: #666;
        }
        
        .meta-item strong {
            color: #2c5530;
        }
        
        .book-price {
            font-size: 2rem;
            color: #2c5530;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .book-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .quantity-selector label {
            font-weight: 600;
            color: #2c5530;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #2c5530;
            color: white;
            border-color: #2c5530;
        }
        
        .quantity-value {
            font-size: 1.2rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #2c5530;
            color: white;
        }
        
        .btn-primary:hover {
            background: #8b4513;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #2c5530;
        }
        
        .btn-secondary:hover {
            background: #e5e5e5;
        }
        
        .reviews-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        
        .reviews-section h2 {
            color: #2c5530;
            margin-bottom: 20px;
        }
        
        .review-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-author {
            font-weight: 600;
            color: #2c5530;
        }
        
        .review-date {
            color: #999;
            font-size: 0.9rem;
        }
        
        .review-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .review-comment {
            color: #666;
            line-height: 1.6;
        }
        
        .not-found {
            text-align: center;
            padding: 100px 20px;
        }
        
        .not-found i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .not-found h2 {
            color: #666;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .book-details-container {
                grid-template-columns: 1fr;
            }
            
            .book-meta {
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
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

    <?php if (!$book): ?>
        <section class="book-details-section">
            <div class="not-found">
                <i class="fas fa-book"></i>
                <h2>Book Not Found</h2>
                <p>The book you're looking for doesn't exist or has been removed.</p>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        </section>
    <?php else: ?>
        <section class="book-details-section">
            <div class="book-details-container">
                <!-- Book Cover -->
                <div class="book-details-cover">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo sanitize($book['cover_image']); ?>" alt="<?php echo sanitize($book['title']); ?>">
                    <?php else: ?>
                        <img src="books/book1.jpg" alt="<?php echo sanitize($book['title']); ?>">
                    <?php endif; ?>
                </div>
                
                <!-- Book Info -->
                <div class="book-details-info">
                    <h1><?php echo sanitize($book['title']); ?></h1>
                    <p class="author">by <?php echo sanitize($book['author']); ?></p>
                    
                    <div class="book-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span>Price</span>
                            <strong>$<?php echo number_format($book['price'], 2); ?></strong>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-layer-group"></i>
                            <span>Stock</span>
                            <strong><?php echo $book['stock_quantity']; ?> available</strong>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-book-open"></i>
                            <span>Pages</span>
                            <strong><?php echo $book['pages'] ?? 'N/A'; ?></strong>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Published</span>
                            <strong><?php echo $book['publish_date'] ? date('Y', strtotime($book['publish_date'])) : 'N/A'; ?></strong>
                        </div>
                    </div>
                    
                    <p class="book-price">$<?php echo number_format($book['price'], 2); ?></p>
                    
                    <p class="book-description"><?php echo nl2br(sanitize($book['description'] ?? 'No description available.')); ?></p>
                    
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                            <span class="quantity-value" id="quantity">1</span>
                            <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $book['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Books
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="book-details-container">
                <div class="reviews-section" style="grid-column: 1 / -1;">
                    <h2><i class="fas fa-star"></i> Customer Reviews</h2>
                    
                    <?php if (empty($reviews)): ?>
                        <p>No reviews yet. Be the first to review this book!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="review-author"><?php echo sanitize($review['username'] ?? 'Anonymous'); ?></span>
                                    <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="review-comment"><?php echo nl2br(sanitize($review['comment'] ?? '')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <script>
        let quantity = 1;
        
        function updateQuantity(change) {
            quantity += change;
            if (quantity < 1) quantity = 1;
            if (quantity > 10) quantity = 10;
            document.getElementById('quantity').textContent = quantity;
        }
        
        function addToCart(bookId) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.bookId === bookId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({ bookId: bookId, quantity: quantity });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
            
            alert('Book added to cart!');
        }
        
        // Update cart count on page load
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    </script>
</body>
</html>