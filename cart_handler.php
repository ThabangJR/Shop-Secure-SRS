<?php
require 'db_config.php'; 
header('Content-Type: application/json'); 

if (!isset($_SESSION['customerID'])) {
    //exit if not logged in
    echo json_encode(['success' => false, 'message' => 'Login required.']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? ''); // Check both GET and POST for action

//ACTION: add item
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $productID = (int)filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT);
    $quantity = (int)filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
        exit;
    }

    //checks for stock and price
    $stmt = $pdo->prepare("SELECT stockQty, price, name FROM Products WHERE productID = ?");
    $stmt->execute([$productID]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    if ($product['stockQty'] < $quantity) {
        echo json_encode(['success' => false, 'message' => "Insufficient stock. Available: {$product['stockQty']}"]);
        exit;
    }

    //store in session cart
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$productID] = $quantity;
    
    echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
    exit;
}

//modifying items (removing items individually)
if ($action === 'remove_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = (int)$_POST['productID'];
    
    if (isset($_SESSION['cart'][$productID])) {
        
        unset($_SESSION['cart'][$productID]);
        echo json_encode(['success' => true, 'message' => 'Item removed.', 'cartCount' => array_sum($_SESSION['cart'] ?? [])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
    }
    exit;
}

//clear cart function
if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['cart']);
    echo json_encode(['success' => true, 'message' => 'Cart successfully cleared.', 'cartCount' => 0]);
    exit;
}

//viewing the cart
if ($action === 'view') {
    $cartItems = [];
    $totalCount = 0;
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $productIDs = array_keys($_SESSION['cart']);

        if (empty($productIDs)) {
              echo json_encode(['success' => true, 'cartItems' => [], 'cartCount' => 0]);
              exit;
        }

        $placeholders = implode(',', array_fill(0, count($productIDs), '?'));
        
        //product details
        try {
            $stmt = $pdo->prepare("SELECT productID, name, price FROM Products WHERE productID IN ($placeholders)");
            $stmt->execute($productIDs);
            
            $rawProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $products = [];
            foreach ($rawProducts as $p) {
                $products[$p['productID']] = $p;
            }

            foreach ($_SESSION['cart'] as $id => $qty) {
                if (isset($products[$id])) {
                    $itemData = $products[$id];
                    $cartItems[] = [
                        'productID' => (int)$id, 
                        'name' => $itemData['name'],
                        'price' => (float)$itemData['price'], 
                        'quantity' => (int)$qty
                    ];
                    $totalCount += $qty;
                }
            }
        } catch (PDOException $e) {
            error_log("Cart View SQL Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error loading cart.']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'cartItems' => $cartItems, 'cartCount' => $totalCount]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid or missing action.']);