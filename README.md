# E-Commerce Project

A PHP-based e-commerce application with user authentication, product catalog, shopping cart, and order management.

## Railway Deployment Guide

### Step 1: Create Railway Account
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub (recommended) or email

### Step 2: Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo" (if you've pushed to GitHub) OR "Empty Project"

### Step 3: Add MySQL Database
1. In your Railway project dashboard, click "+ New"
2. Select "Database" → "Add MySQL"
3. Railway will automatically create a MySQL database

### Step 4: Deploy Your Code

**Option A: Deploy from GitHub (Recommended)**
1. Push your code to a GitHub repository
2. In Railway, click "+ New" → "GitHub Repo"
3. Select your repository
4. Railway will automatically detect PHP and deploy

**Option B: Deploy via Railway CLI**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Initialize project
railway init

# Deploy
railway up
```

### Step 5: Configure Environment Variables
1. In Railway dashboard, go to your MySQL service
2. Click on "Variables" tab
3. Copy the connection variables (MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT)
4. Go to your web service → "Variables" tab
5. Add these environment variables:
   - `DB_HOST` = value from `MYSQLHOST`
   - `DB_USER` = value from `MYSQLUSER`
   - `DB_PASS` = value from `MYSQLPASSWORD`
   - `DB_NAME` = value from `MYSQLDATABASE`
   - `DB_PORT` = value from `MYSQLPORT`
   - `ADMIN_EMAIL` = your email address

### Step 6: Import Database Schema
1. In Railway dashboard, go to your MySQL service
2. Click "Connect" → "MySQL"
3. This opens a MySQL console
4. Copy and paste the contents of `database.sql` into the console
5. Execute the SQL script

### Step 7: Get Your Live URL
1. In Railway dashboard, go to your web service
2. Click "Settings" → "Generate Domain"
3. Railway will provide you with a public URL (e.g., `your-project.up.railway.app`)

### Step 8: Test Your Application
Visit your Railway URL and test:
- User registration
- Login
- Browse products
- Add to cart
- Checkout

## Local Development (XAMPP)

1. Place project in `htdocs` folder
2. Import `database.sql` into phpMyAdmin
3. Update `config.php` with local database credentials (or use `.env` file)
4. Access via `http://localhost/ecommerce-project`

## Project Structure

- `index.php` - Landing page (redirects to login/products)
- `login.php` - User login page
- `register.php` - User registration
- `products.php` - Product catalog
- `cart.php` - Shopping cart
- `checkout.php` - Checkout process
- `order_success.php` - Order confirmation
- `config.php` - Database and session configuration
- `database.sql` - Database schema and sample data

## Features

- User authentication (login/register)
- Product catalog with images
- Shopping cart functionality
- Order management
- Responsive design

## Notes

- Make sure PHP version is 7.4 or higher
- MySQL database is required
- Sessions are used for user authentication

