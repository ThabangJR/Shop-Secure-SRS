<?php
require 'db_config.php';
require_manager(); //only managers can access this

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $productID = (int)filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stockQty = (int)filter_input(INPUT_POST, 'stockQty', FILTER_SANITIZE_NUMBER_INT);
    $category = sanitize($_POST['category']);

    try {
        if ($productID) {
            //UPDATE
            $stmt = $pdo->prepare("UPDATE Products SET name=?, description=?, price=?, stockQty=?, category=? WHERE productID=?");
            $stmt->execute([$name, $description, $price, $stockQty, $category, $productID]);
            echo json_encode(['success' => true, 'action' => 'updated']);
        } else {
            //CREATE
            $stmt = $pdo->prepare("INSERT INTO Products (name, description, price, stockQty, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stockQty, $category]);
            echo json_encode(['success' => true, 'action' => 'created', 'productID' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        error_log("Product CRUD error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error during save/update.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //READ (Fetch all products)
    $stmt = $pdo->query("SELECT * FROM Products ORDER BY productID DESC");
    echo json_encode(['success' => true, 'products' => $stmt->fetchAll()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    //DELETE
    //PHP does not populate $_DELETE or $_POST for raw DELETE requests, so we parse input stream
    parse_str(file_get_contents("php://input"), $data); 
    
    //checking the CSRF token from the parsed $data
    $_POST['csrf_token'] = $data['csrf_token'] ?? ''; 
    check_csrf(); 
    
    $productID = (int)filter_var($data['productID'], FILTER_SANITIZE_NUMBER_INT);
    
    if ($productID) {
        $stmt = $pdo->prepare("DELETE FROM Products WHERE productID = ?");
        $stmt->execute([$productID]);
        echo json_encode(['success' => true, 'action' => 'deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID for deletion.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method or missing action.']);