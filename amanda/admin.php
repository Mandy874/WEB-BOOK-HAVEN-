<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Admin Dashboard - Book Haven';
$pdo = getDB();

$stats = [
    'total_books' => 0,
    'total_users' => 0,
    'total_orders' => 0,
    'total_revenue' => 0
];

$recentOrders = [];
$books = [];

if ($pdo) {
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $stats['total_books'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'");
    $stats['total_revenue'] = $stmt->fetch()['total'];
    
    // Get recent orders
    $stmt = $pdo->query("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 10");
    $recentOrders = $stmt->fetchAll();
    
    // Get all books
    $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
    $books = $stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_book' && isAdmin()) {
            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $category_id = intval($_POST['category_id'] ?? null);
            $isbn = trim($_POST['isbn'] ?? '');
            $publisher = trim($_POST['publisher'] ?? '');
            $pages = intval($_POST['pages'] ?? 0);
            
            if (!empty($title) && !empty($author) && $price > 0) {
                $stmt = $pdo->prepare("INSERT INTO books (title, author, description, price, stock_quantity, category_id, isbn, publisher, pages) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author, $description, $price, $stock, $category_id, $isbn, $publisher, $pages]);
                $success = "Book added successfully!";
            }
        } elseif ($_POST['action'] === 'delete_book' && isAdmin()) {
            $bookId = intval($_POST['book_id'] ?? 0);
            if ($bookId > 0) {
                $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                $stmt->execute([$bookId]);
                $success = "Book deleted successfully!";
            }
        } elseif ($_POST['action'] === 'update_order' && isAdmin()) {
            $orderId = intval($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($orderId > 0 && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
                $stmt->execute([$status, $orderId]);
                $success = "Order updated successfully!";
            }
        }
    }
    
    // Refresh data
    header("Location: admin.php");
    exit;
}

// Get categories for the form
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
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        :root {
            --admin-primary: #2c5530;
            --admin-secondary: #d4a96c;
            --admin-accent: #8b4513;
            --admin-dark: #1a1a1a;
            --admin-light: #f8f5f0;
            --admin-gray: #eae7e1;
            --admin-success: #28a745;
            --admin-warning: #ffc107;
            --admin-danger: #dc3545;
            --admin-info: #17a2b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--admin-gray);
            padding-top: 80px;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: var(--admin-primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--admin-primary);
        }

        .stat-card .icon {
            float: right;
            font-size: 2.5rem;
            color: var(--admin-gray);
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .admin-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-section h2 {
            color: var(--admin-primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--admin-gray);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--admin-gray);
        }

        .data-table th {
            background: var(--admin-light);
            color: var(--admin-primary);
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d4edda; color: #28a745; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-primary { background: var(--admin-primary); color: white; }
        .btn-danger { background: var(--admin-danger); color: white; }
        .btn-sm { padding: 5px 10px; font-size: 0.8rem; }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--admin-primary);
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: var(--admin-gray);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
        }

        .tab-btn.active {
            background: var(--admin-primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 1024px) {
            .admin-grid {
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
                <h1>Book <span>Haven</span> <small style="font-size:0.5em">Admin</small></h1>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php">View Site</a></li>
                    <li><a href="#" class="active">Dashboard</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="user-actions">
                    <a href="#" class="auth-link"><i class="fas fa-user"></i> <?php echo sanitize($_SESSION['username'] ?? 'Admin'); ?></a>
                    <a href="logout.php" class="auth-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-cog"></i> Admin Dashboard</h1>
            <p>Welcome, <?php echo sanitize($_SESSION['first_name'] ?? 'Admin'); ?>!</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo sanitize($success); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-book icon"></i>
                <h3>Total Books</h3>
                <div class="value"><?php echo $stats['total_books']; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users icon"></i>
                <h3>Total Users</h3>
                <div class="value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart icon"></i>
                <h3>Total Orders</h3>
                <div class="value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign icon"></i>
                <h3>Total Revenue</h3>
                <div class="value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('orders')">Orders</button>
            <button class="tab-btn" onclick="showTab('books')">Books</button>
            <button class="tab-btn" onclick="showTab('add-book')">Add Book</button>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content active">
            <div class="admin-section">
                <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center">No orders yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo sanitize($order['username'] ?? 'Guest'); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="action" value="update_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding:5px; font-size:0.8rem">
                                                <option value="pending" <?php echo $order['order_status']=='pending'?'selected':''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['order_status']=='processing'?'selected':''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['order_status']=='shipped'?'selected':''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['order_status']=='delivered'?'selected':''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['order_status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Books Tab -->
        <div id="books" class="tab-content">
            <div class="admin-section">
                <h2><i class="fas fa-book"></i> Manage Books</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center">No books yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?php echo $book['id']; ?></td>
                                    <td><?php echo sanitize($book['title']); ?></td>
                                    <td><?php echo sanitize($book['author']); ?></td>
                                    <td>$<?php echo number_format($book['price'], 2); ?></td>
                                    <td><?php echo $book['stock_quantity']; ?></td>
                                    <td>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                            <input type="hidden" name="action" value="delete_book">
                                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Book Tab -->
        <div id="add-book" class="tab-content">
            <div class="admin-section">
                <h2><i class="fas fa-plus-circle"></i> Add New Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_book">
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="author">Author *</label>
                            <input type="text" id="author" name="author" required>
                        </div>
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input type="text" id="publisher" name="publisher">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pages">Pages</label>
                        <input type="number" id="pages" name="pages" min="0">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Book
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>