<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$user_id = getUserId();

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_id = intval($_POST['cart_id'] ?? 0);
    if ($cart_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($cart_id > 0 && $quantity > 0) {
        // Check stock availability
        $check_stmt = $conn->prepare("SELECT p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
        $check_stmt->bind_param("ii", $cart_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $product = $check_result->fetch_assoc();
            if ($quantity <= $product['stock_quantity']) {
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Get cart items with product details
$cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.description, p.price, p.image_url, p.stock_quantity 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ? 
               ORDER BY c.created_at DESC";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
    $cart_items[] = $item;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - E-Commerce</title>
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
                <a href="cart.php" class="nav-link active">
                    Cart 
                    <?php if (count($cart_items) > 0): ?>
                        <span class="cart-badge"><?php echo array_sum(array_column($cart_items, 'quantity')); ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="nav-link">Logout</a>
                <span class="nav-user">Hello, <?php echo htmlspecialchars(getUserFullName()); ?>!</span>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Shopping Cart</h1>
        </div>

        <?php if (count($cart_items) > 0): ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/150x150?text=No+Image'">
                            </div>
                            <div class="cart-item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="cart-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                                
                                <div class="cart-item-actions">
                                    <form method="POST" action="cart.php" class="quantity-form">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <label>
                                            Quantity:
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                   class="quantity-input">
                                        </label>
                                        <button type="submit" name="update_quantity" class="btn btn-sm">Update</button>
                                    </form>
                                    
                                    <form method="POST" action="cart.php" class="remove-form">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-sm btn-danger">Remove</button>
                                    </form>
                                </div>
                                
                                <p class="cart-item-total">
                                    Subtotal: <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-card">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>$<?php echo number_format($total * 0.1, 2); ?></span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total * 1.1, 2); ?></span>
                        </div>
                        
                        <form method="POST" action="checkout.php">
                            <button type="submit" class="btn btn-primary btn-block">Proceed to Checkout</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Your cart is empty</h2>
                <p>Start shopping to add items to your cart!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

