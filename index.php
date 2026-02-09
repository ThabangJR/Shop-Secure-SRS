<?php
require 'db_config.php';

$is_logged_in = isset($_SESSION['customerID']);
$is_manager = $is_logged_in && $_SESSION['role'] === 'manager';
$csrf_token = $_SESSION['csrf_token'];

//user name for personalized greeting
$user_name = $_SESSION['name'] ?? 'Guest'; 
$greeting = $is_logged_in ? "Welcome, " . htmlspecialchars($user_name) . "!" : "Hello, Guest!"; 

//collects all the available products and their categories categories
try {
    $products = $pdo->query("SELECT productID, name, description, price, stockQty, category FROM Products WHERE stockQty > 0")->fetchAll();
    $categories = $pdo->query("SELECT DISTINCT category FROM Products")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Failed to fetch products for index: " . $e->getMessage());
    $products = [];
    $categories = [];
}

$cart_count = 0; 
if ($is_logged_in && isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopSecure | Home</title>
     <span style="font-size: 0.6em; font-weight: 430; color: #000000ff; display: block; line-height: 1;">by Thabang Mohale</span>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>ShopSecure Inventory</h1>
            <nav>
                <span style="margin-right: 15px; font-weight: bold; color: #0056b3;"><?php echo $greeting; ?></span>
                
                <?php if ($is_logged_in): ?>
                    <a href="order_history.php">Order History</a> <a href="logout.php">Logout</a>
                    
                    <?php if ($is_manager): ?>
                        <a href="manager_dashboard.php">Manager Dashboard</a>
                    <?php endif; ?>
                    <button id="cartToggle">🛒 Cart (<span id="cartCount"><?php echo $cart_count; ?></span>)</button>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </header>

        <section>
            <h2>Available Products</h2>
            
            <div style="margin-bottom: 20px;">
                <label for="categoryFilter">Filter by Category:</label>
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="product-grid" id="productGrid">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1/-1;">No products currently in stock.</p>
                <?php endif; ?>
                
                <?php foreach ($products as $p): ?>
                <div class="product-card" data-category="<?php echo htmlspecialchars($p['category']); ?>">
                    <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    <p><?php echo htmlspecialchars($p['description']); ?></p>
                    <p class="price">R<?php echo number_format($p['price'], 2); ?></p>
                    <p>Stock: <span class="stock-qty"><?php echo (int)$p['stockQty']; ?></span></p>

                    <?php if ($is_logged_in && $p['stockQty'] > 0): ?>
                    <form onsubmit="event.preventDefault(); addToCart(this);">
                        <input type="hidden" name="productID" value="<?php echo (int)$p['productID']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <input type="number" name="quantity" min="1" max="<?php echo (int)$p['stockQty']; ?>" value="1" required>
                        <button type="submit">Add to Cart</button>
                    </form>
                    <?php elseif ($p['stockQty'] == 0): ?>
                        <p class="out-of-stock">Out of Stock</p>
                    <?php else: ?>
                        <p><a href="login.html">Login to Purchase</a></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="cart" id="cart"></div>
    </div>
    <script src="script.js"></script>
</body>
</html>