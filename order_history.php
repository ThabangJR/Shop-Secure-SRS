<?php
require 'db_config.php';

//Security Check: user must be logged in to view history
if (!isset($_SESSION['customerID'])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['customerID'];
$orders = [];

try {
    //1. Fetch ALL orders for the current customer
    $stmt = $pdo->prepare("SELECT orderID, totalAmount, status, order_date FROM Orders WHERE customerID = ? ORDER BY order_date DESC");
    $stmt->execute([$customerID]);
    $rawOrders = $stmt->fetchAll();

    //2. Collect all Order IDs to fetch items efficiently
    $orderIDs = array_column($rawOrders, 'orderID');

    if (!empty($orderIDs)) {
        $placeholders = implode(',', array_fill(0, count($orderIDs), '?'));
        
        //3. Fetch all items related to these orders in a single query
        $itemStmt = $pdo->prepare("
            SELECT oi.orderID, oi.quantity, oi.unit_price, p.name 
            FROM Order_Items oi
            JOIN Products p ON oi.productID = p.productID
            WHERE oi.orderID IN ($placeholders)
            ORDER BY oi.orderID, p.name
        ");
        $itemStmt->execute($orderIDs);
        
        //Fetch items and group them by orderID
        $itemsByOrder = [];
        $rawItems = $itemStmt->fetchAll();
        foreach ($rawItems as $item) {
            $itemsByOrder[$item['orderID']][] = $item;
        }

        //4. Combine orders and items for display
        foreach ($rawOrders as $order) {
            $order['items'] = $itemsByOrder[$order['orderID']] ?? [];
            $orders[] = $order;
        }
    }

} catch (PDOException $e) {
    error_log("Order history error for user $customerID: " . $e->getMessage());
    die("Could not load order history due to a system error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - ShopSecure</title>
    <span style="font-size: 0.6em; font-weight: 430; color: #000000ff; display: block; line-height: 1;">by Thabang Mohale</span>
    <link rel="stylesheet" href="styles.css">
    <style>
        .order-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .order-card h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 0;
            color: #0056b3;
        }
        .item-list {
            list-style: none;
            padding-left: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Your Order History</h1>
            <nav>
                <a href="index.php">Back to Shop</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <section>
            <?php if (empty($orders)): ?>
                <p>You have not placed any orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h3>Order #<?php echo htmlspecialchars($order['orderID']); ?> 
                        <span style="float: right; font-weight: normal; font-size: 0.9em; color: #666;">
                            Date: <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?>
                        </span>
                    </h3>
                    <p>
                        <strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?><br>
                        <strong>Total:</strong> R <?php echo number_format($order['totalAmount'], 2); ?>
                    </p>
                    
                    <h4>Items Purchased:</h4>
                    <ul class="item-list">
                        <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['name']); ?> 
                                (<?php echo (int)$item['quantity']; ?> x R <?php echo number_format($item['unit_price'], 2); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>