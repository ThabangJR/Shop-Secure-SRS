<?php
require 'db_config.php';
require_manager(); // Security check to ensure only managers access this page

// Fetch CSRF token for forms
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manager Dashboard</h1>
            <nav>
<a href="generate_java_report.php" class="report-button">Generate System Reports (PHP & Java)</a>
                <a href="index.php">Back to Shop</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <h2>Manage Inventory</h2>
        <div id="statusMessage" class="status-message"></div>

        <form id="productForm" class="product-form">
            <h3>Add New Product / Edit Product</h3>
            <input type="hidden" name="productID" id="productID" value="0">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>
            
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required min="0.01">
            
            <label for="stockQty">Stock Quantity:</label>
            <input type="number" id="stockQty" name="stockQty" required min="0">

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <button type="submit" id="submitButton">Add Product</button>
            <button type="button" onclick="resetForm()">Clear Form</button>
        </form>

        <h2>Current Products</h2>
        <table id="productTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
        </table>
    </div>

<script src="manager_script.js?v=2"></script> 
</body>
</html>

<script>
//JavaScript for Manager Dashboard Logic
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    const tableBody = document.querySelector('#productTable tbody');
    const statusDiv = document.getElementById('statusMessage');
    const submitButton = document.getElementById('submitButton');

    function showStatus(message, type = 'success') {
        statusDiv.textContent = message;
        statusDiv.className = 'status-message ' + type;
        setTimeout(() => statusDiv.textContent = '', 3000);
    }

    window.resetForm = function() {
        form.reset();
        document.getElementById('productID').value = '0';
        submitButton.textContent = 'Add Product';
    }

    window.loadProducts = function() {
        fetch('product_api.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    tableBody.innerHTML = '';
                    data.products.forEach(p => {
                        const row = tableBody.insertRow();
                        row.innerHTML = `
                            <td>${p.productID}</td>
                            <td>${p.name}</td>
                            <td>${p.category}</td>
                            <td>R${parseFloat(p.price).toFixed(2)}</td>
                            <td>${p.stockQty}</td>
                            <td>
                                <button onclick="editProduct(${p.productID}, '${p.name}', '${p.category}', ${p.price}, ${p.stockQty}, '${p.description.replace(/'/g, "\\'")}')">Edit</button>
                                <button onclick="deleteProduct(${p.productID})">Delete</button>
                            </td>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6">Failed to load products.</td></tr>';
                }
            });
    };

    window.editProduct = function(id, name, category, price, stockQty, description) {
        document.getElementById('productID').value = id;
        document.getElementById('name').value = name;
        document.getElementById('category').value = category;
        document.getElementById('price').value = price;
        document.getElementById('stockQty').value = stockQty;
        document.getElementById('description').value = description;
        submitButton.textContent = 'Save Changes';
    };

    window.deleteProduct = function(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('product_api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `productID=${id}&csrf_token=${csrfToken}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStatus('Product deleted.', 'error');
                loadProducts();
            } else {
                showStatus(data.message || 'Deletion failed.', 'error');
            }
        });
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const action = formData.get('productID') !== '0' ? 'updated' : 'created';

        fetch('product_api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStatus('Product successfully ' + action + '!', 'success');
                resetForm();
                loadProducts();
            } else {
                showStatus(data.message || 'Action failed.', 'error');
            }
        });
    });

    loadProducts(); 
});
</script>