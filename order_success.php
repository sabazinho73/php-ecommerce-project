<?php
require_once 'config.php';
requireLogin();

$order_id = $_SESSION['order_id'] ?? null;
$order_total = $_SESSION['order_total'] ?? null;

if (!$order_id) {
    header('Location: products.php');
    exit();
}

// Clear session variables
unset($_SESSION['order_id']);
unset($_SESSION['order_total']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - E-Commerce</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🛍️ E-Commerce Store</h2>
            </div>
            <div class="nav-links">
                <a href="products.php" class="nav-link">Products</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="logout.php" class="nav-link">Logout</a>
                <span class="nav-user">Hello, <?php echo htmlspecialchars(getUserFullName()); ?>!</span>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">✓</div>
                <h1>Order Confirmed!</h1>
                <p class="success-message">Thank you for your purchase, <?php echo htmlspecialchars(getUserFullName()); ?>!</p>
                
                <div class="order-details">
                    <p><strong>Order Number:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($order_total, 2); ?></p>
                </div>
                
                <p class="success-info">A confirmation email has been sent to the store administrator.</p>
                
                <div class="success-actions">
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

