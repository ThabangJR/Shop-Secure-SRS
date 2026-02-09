<?php
require 'db_config.php';
$orderID = htmlspecialchars($_GET['orderID'] ?? 'N/A');
$total = htmlspecialchars($_GET['total'] ?? 'N/A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🎉 Order Confirmed!</h1>
        </header>
        <section>
            <p>Thank you for your purchase!</p>
            <p>Your order (ID: **<?php echo $orderID; ?>**) has been successfully placed and confirmed.</p>
            <p>The total amount charged was: **R<?php echo $total; ?>**.</p>
            <p>You can now view this order's history</p>
            <p><a href="index.php">Continue Shopping</a></p>
        </section>
    </div>
</body>
</html>