<?php
require 'db_config.php';

// 1. Security Check: user must be logged in
if (!isset($_SESSION['customerID'])) {
    header('Location: login.html');
    exit;
}

// 2. Security Check: cart cannot be empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php?msg=cart_empty');
    exit;
}

$customerID = $_SESSION['customerID'];
$cart = $_SESSION['cart'];
$orderID = 0;
$totalAmount = 0;

try {
 
    $pdo->beginTransaction();

    //validating the stock and calculating the total cost of items in the cart
    $productIDs = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($productIDs), '?'));
    
    //query for productID, price, and stockQty
    $stmt = $pdo->prepare("SELECT productID, price, stockQty FROM Products WHERE productID IN ($placeholders)");
    $stmt->execute($productIDs);
    
    $rawProducts = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    $products = [];
    foreach ($rawProducts as $p) {
        $products[$p['productID']] = $p;
    }


    $orderItemsData = [];
    
    foreach ($cart as $productID => $qty) {

        if (!isset($products[$productID])) {
            throw new Exception("Product ID $productID not found or discontinued.");
        }
        $product = $products[$productID];

        if ($product['stockQty'] < $qty) {
            // if stock is not enough
            throw new Exception("Insufficient stock for product ID $productID. Available: {$product['stockQty']}");
        }

        $unitPrice = (float)$product['price'];
        $totalAmount += $unitPrice * $qty;
        
        
        $orderItemsData[] = [
            'productID' => $productID,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'new_stock' => $product['stockQty'] - $qty
        ];
    }
    
    //creating the order record (history)
    $stmt = $pdo->prepare("INSERT INTO Orders (customerID, totalAmount, status) VALUES (?, ?, 'confirmed')");
    $stmt->execute([$customerID, $totalAmount]);
    $orderID = $pdo->lastInsertId();

    //adding items and updating stock
    $itemStmt = $pdo->prepare("INSERT INTO Order_Items (orderID, productID, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE Products SET stockQty = ? WHERE productID = ?");

    foreach ($orderItemsData as $item) {
        //inserting item record
        $itemStmt->execute([$orderID, $item['productID'], $item['quantity'], $item['unit_price']]);
        
        //update stock
        $stockStmt->execute([$item['new_stock'], $item['productID']]);
    }

    //commit the transaction if all steps succeeded
    $pdo->commit();

    //clear cart and complete order 
    unset($_SESSION['cart']);
    header("Location: order_success.php?orderID=$orderID&total=" . number_format($totalAmount, 2));
    exit;

} catch (Exception $e) {
    //If anything fails, rollback the transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Checkout error for user $customerID: " . $e->getMessage());
    die("Checkout Failed: " . htmlspecialchars($e->getMessage()));
}
?>