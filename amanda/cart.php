<?php
require_once 'config.php';

$pageTitle = 'Shopping Cart - Book Haven';
$pdo = getDB();

// Get cart items from localStorage via AJAX or process directly
$cartItems = [];
$cartBooks = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Handle cart actions
    if ($_POST['action'] === 'update') {
        $bookId = $_POST['book_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        // This would be handled via JavaScript for localStorage
    } elseif ($_POST['action'] === 'remove') {
        $bookId = $_POST['book_id'] ?? 0;
        // Remove from localStorage via JavaScript
    } elseif ($_POST['action'] === 'checkout' && isLoggedIn()) {
        // Process checkout
        $cartData = json_decode($_POST['cart_data'] ?? '[]', true);
        
        if (!empty($cartData)) {
            try {
                $pdo->beginTransaction();
                
                // Calculate total
                $total = 0;
                foreach ($cartData as $item) {
                    $stmt = $pdo->prepare("SELECT price FROM books WHERE id = ?");
                    $stmt->execute([$item['bookId']]);
                    $book = $stmt->fetch();
                    $total += $book['price'] * $item['quantity'];
                }
                
                // Create order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_status, payment_status) VALUES (?, ?, 'pending', 'pending')");
                $stmt->execute([$_SESSION['user_id'], $total]);
                $orderId = $pdo->lastInsertId();
                
                // Add order items
                foreach ($cartData as $item) {
                    $stmt = $pdo->prepare("SELECT price FROM books WHERE id = ?");
                    $stmt->execute([$item['bookId']]);
                    $book = $stmt->fetch();
                    
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $subtotal = $book['price'] * $item['quantity'];
                    $stmt->execute([$orderId, $item['bookId'], $item['quantity'], $book['price'], $subtotal]);
                }
                
                $pdo->commit();
                $success = "Order placed successfully! Order ID: $orderId";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Order failed: " . $e->getMessage();
            }
        }
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
            background: #f5f5f5;
            padding-top: 100px;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .cart-header h1 {
            color: #2c5530;
        }
        
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        .cart-items {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-size: 1.1rem;
            color: #2c5530;
            margin-bottom: 5px;
        }
        
        .cart-item-author {
            color: #666;
            margin-bottom: 10px;
        }
        
        .cart-item-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c5530;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #2c5530;
            color: white;
            border-color: #2c5530;
        }
        
        .remove-btn {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .remove-btn:hover {
            text-decoration: underline;
        }
        
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .cart-summary h2 {
            color: #2c5530;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c5530;
            border-bottom: none;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #2c5530;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .checkout-btn:hover {
            background: #8b4513;
        }
        
        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        
        .continue-shopping:hover {
            color: #2c5530;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h2 {
            color: #666;
            margin-bottom: 10px;
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
        
        @media (max-width: 768px) {
            .cart-grid {
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
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> <span id="cartCount">0</span></a>
                        <a href="#" class="auth-link"><i class="fas fa-user"></i> <?php echo sanitize($_SESSION['first_name'] ?? 'User'); ?></a>
                        <a href="logout.php" class="auth-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> <span id="cartCount">0</span></a>
                        <a href="login.php" class="auth-link login"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="signup.php" class="auth-link signup"><i class="fas fa-user-plus"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo sanitize($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        
        <div class="cart-grid">
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be loaded via JavaScript -->
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Add some books to get started!</p>
                    <a href="index.php" class="btn">Continue Shopping</a>
                </div>
            </div>
            
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span id="total">$0.00</span>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <button class="checkout-btn" onclick="processCheckout()">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                <?php else: ?>
                    <a href="login.php" class="checkout-btn" style="display:inline-block; text-align:center; text-decoration:none;">
                        <i class="fas fa-lock"></i> Login to Checkout
                    </a>
                <?php endif; ?>
                
                <a href="index.php" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        // Book data - in production, this would come from the database
        const booksData = {
            <?php if ($pdo): ?>
            <?php 
            $stmt = $pdo->query("SELECT id, title, author, price, cover_image FROM books WHERE is_active = 1");
            $first = true;
            while ($book = $stmt->fetch()): 
            ?>
            <?php echo $first ? '' : ','; ?>
            <?php echo $book['id']; ?>: {
                title: '<?php echo addslashes($book['title']); ?>',
                author: '<?php echo addslashes($book['author']); ?>',
                price: <?php echo $book['price']; ?>,
                image: '<?php echo $book['cover_image'] ?: 'books/book1.jpg'; ?>'
            }
            <?php $first = false; endwhile; ?>
            <?php endif; ?>
        };
        
        function renderCart() {
            const cartItemsEl = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartItemsEl.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Your cart is empty</h2>
                        <p>Add some books to get started!</p>
                        <a href="index.php" class="btn">Continue Shopping</a>
                    </div>
                `;
                updateSummary();
                return;
            }
            
            let html = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const book = booksData[item.bookId];
                if (book) {
                    const itemTotal = book.price * item.quantity;
                    subtotal += itemTotal;
                    
                    html += `
                        <div class="cart-item">
                            <img src="${book.image}" alt="${book.title}" class="cart-item-image">
                            <div class="cart-item-details">
                                <h3 class="cart-item-title">${book.title}</h3>
                                <p class="cart-item-author">by ${book.author}</p>
                                <p class="cart-item-price">$${book.price.toFixed(2)}</p>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(${item.bookId}, -1)">-</button>
                                    <span>${item.quantity}</span>
                                    <button class="quantity-btn" onclick="updateQuantity(${item.bookId}, 1)">+</button>
                                    <button class="remove-btn" onclick="removeFromCart(${item.bookId})">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            cartItemsEl.innerHTML = html;
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('total').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
        }
        
        function updateQuantity(bookId, change) {
            const item = cart.find(i => i.bookId === bookId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.bookId !== bookId);
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                renderCart();
            }
        }
        
        function removeFromCart(bookId) {
            cart = cart.filter(i => i.bookId !== bookId);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        
        function updateSummary() {
            document.getElementById('subtotal').textContent = '$0.00';
            document.getElementById('total').textContent = '$0.00';
        }
        
        function processCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Submit order via AJAX
            const formData = new FormData();
            formData.append('action', 'checkout');
            formData.append('cart_data', JSON.stringify(cart));
            
            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert('Order placed successfully!');
                cart = [];
                localStorage.setItem('cart', JSON.stringify(cart));
                renderCart();
            })
            .catch(error => {
                alert('Error processing order. Please try again.');
            });
        }
        
        // Initialize cart
        renderCart();
    </script>
</body>
</html>