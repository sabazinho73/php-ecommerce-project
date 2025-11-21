<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$user_id = getUserId();

// Get cart items
$cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.stock_quantity 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
$total = 0;
$can_checkout = true;
$errors = [];

while ($item = $cart_result->fetch_assoc()) {
    // Check if still in stock
    if ($item['quantity'] > $item['stock_quantity']) {
        $can_checkout = false;
        $errors[] = "{$item['name']} - Only {$item['stock_quantity']} available in stock.";
    }
    
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
    $cart_items[] = $item;
}

$stmt->close();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_checkout) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_total = $total * 1.1; // Include 10% tax
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $order_stmt->bind_param("id", $user_id, $order_total);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Create order items and update inventory
        $order_items = [];
        foreach ($cart_items as $item) {
            // Insert order item
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $item_stmt->execute();
            $item_stmt->close();
            
            // Update product stock
            $new_stock = $item['stock_quantity'] - $item['quantity'];
            $update_stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_stock, $item['product_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $order_items[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }
        
        // Clear cart
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Get user details for email
        $user_stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        $user_stmt->close();
        
        // Send email
        $customer_name = $user['full_name'];
        $email_subject = "New Order Confirmation - Order #$order_id";
        
        $email_body = "$customer_name ordered the following items:\n\n";
        foreach ($order_items as $item) {
            $email_body .= "- {$item['name']} (Qty: {$item['quantity']}) - $" . number_format($item['price'] * $item['quantity'], 2) . "\n";
        }
        $email_body .= "\nTotal: $" . number_format($order_total, 2);
        
        // Send email (using PHP mail function - configure your server's mail settings)
        $admin_email = ADMIN_EMAIL;
        $headers = "From: noreply@ecommerce-store.com\r\n";
        $headers .= "Reply-To: " . $user['email'] . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($admin_email, $email_subject, $email_body, $headers);
        
        // Redirect to success page
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_total'] = $order_total;
        header('Location: order_success.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $errors[] = "Checkout failed. Please try again.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Commerce</title>
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
        <div class="page-header">
            <h1>Checkout</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Please fix the following issues:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="checkout-summary">
                <h2>Order Summary</h2>
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($total * 0.1, 2); ?></span>
                    </div>
                    <div class="total-row total-row-final">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total * 1.1, 2); ?></span>
                    </div>
                </div>

                <?php if ($can_checkout): ?>
                    <form method="POST" action="checkout.php">
                        <button type="submit" class="btn btn-primary btn-block">Confirm Order</button>
                    </form>
                <?php else: ?>
                    <a href="cart.php" class="btn btn-secondary btn-block">Return to Cart</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

