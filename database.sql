-- E-commerce Database Schema

CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table (for storing cart items)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample products
INSERT INTO products (name, description, price, image_url, stock_quantity) VALUES
('Wireless Headphones', 'Premium noise-cancelling wireless headphones with 30-hour battery life', 199.99, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500', 15),
('Smart Watch', 'Feature-rich smartwatch with fitness tracking and heart rate monitor', 299.99, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500', 8),
('Laptop Stand', 'Ergonomic aluminum laptop stand for better posture', 49.99, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500', 20),
('Mechanical Keyboard', 'RGB backlit mechanical keyboard with cherry MX switches', 129.99, 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=500', 0),
('Wireless Mouse', 'Ergonomic wireless mouse with precision tracking', 39.99, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500', 12),
('USB-C Hub', '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader', 79.99, 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=500', 25),
('Desk Lamp', 'LED desk lamp with adjustable brightness and color temperature', 59.99, 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500', 18),
('Webcam HD', '1080p HD webcam with built-in microphone', 89.99, 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=500', 0);

