<?php
require_once 'config.php';

// Redirect to login if not logged in, otherwise to products
if (isLoggedIn()) {
    header('Location: products.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>

