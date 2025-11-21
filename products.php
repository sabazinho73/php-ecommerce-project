<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get all products
$products_query = "SELECT * FROM products ORDER BY name";
$products_result = $conn->query($products_query);

// Get cart count
$user_id = getUserId();
$cart_count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_count_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$cart_count = $cart_result->fetch_assoc()['total'] ?? 0;
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-Commerce</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🛍️ E-Commerce Store</h2>
            </div>
            <div class="nav-links">
                <a href="products.php" class="nav-link active">Products</a>
                <a href="cart.php" class="nav-link">
                    Cart 
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="nav-link">Logout</a>
                <span class="nav-user">Hello, <?php echo htmlspecialchars(getUserFullName()); ?>!</span>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Our Products</h1>
            <p>Browse our collection of amazing products</p>
        </div>

        <div class="products-grid">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                            <?php if ($product['stock_quantity'] <= 0): ?>
                                <div class="sold-out-badge">Sold Out</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

