<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $user_id = getUserId();
    
    if ($product_id > 0) {
        $conn = getDBConnection();
        
        // Check if product exists and is in stock
        $product_stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        
        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            
            if ($product['stock_quantity'] > 0) {
                // Check if item already in cart
                $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $check_stmt->bind_param("ii", $user_id, $product_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update quantity
                    $cart_item = $check_result->fetch_assoc();
                    $new_quantity = $cart_item['quantity'] + 1;
                    
                    if ($new_quantity <= $product['stock_quantity']) {
                        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                        $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                } else {
                    // Add new item to cart
                    $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                    $insert_stmt->bind_param("ii", $user_id, $product_id);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
            }
        }
        
        $product_stmt->close();
        $conn->close();
    }
}

header('Location: products.php');
exit();
?>

