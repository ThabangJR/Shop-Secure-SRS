<?php
session_start(); 
require 'db_config.php';

//HANDLER LOGIC (Runs ONLY on POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(sanitize($_POST['email']), FILTER_SANITIZE_EMAIL);
    $inputPassword = $_POST['password'];
    $role = sanitize($_POST['role']); 
    
    //validates role input against allowed values
    if (!in_array($role, ['customer', 'manager'])) {
        $_SESSION['login_error'] = 'Invalid role selected.';
        
    } else {
        $stmt = $pdo->prepare("SELECT customerID, name, password_hash, role FROM Customers WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($inputPassword, $user['password_hash'])) {
            //SUCCESS: Redirect away from this page
            $_SESSION['customerID'] = $user['customerID'];
            $_SESSION['name'] = $user['name']; 
            $_SESSION['role'] = $user['role']; 
            
            if ($user['role'] === 'manager') {
                header('Location: manager_dashboard.php');
            } else {
                header('Location: index.php?login_success=true');
            }
            exit;
        } else {
            //if FAIL: Set stylish error message and continue to display HTML below
            $_SESSION['login_error'] = 'Invalid email, password, or role combination.';
        }
    }
} 


//DISPLAY LOGIC (Always Runs or is Redirected To)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopSecure</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styling for the "Show Password" toggle */
        .password-container {
            position: relative;
            margin-bottom: 15px; 
        }
      
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            padding-right: 60px; 
        }
        .toggle-password {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--color-primary); 
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px;
            z-index: 10;
            transition: color 0.2s;
        }
        .toggle-password:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <?php
    // DISPLAY STATUS MESSAGES
    if (isset($_SESSION['login_error'])) {
        echo '<div class="form-container" style="padding-bottom: 0; box-shadow: none; background: none;">';
        echo '<div class="status-message error">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
        echo '</div>';
        unset($_SESSION['login_error']);
    }
    if (isset($_SESSION['register_success'])) {
        echo '<div class="form-container" style="padding-bottom: 0; box-shadow: none; background: none;">';
        echo '<div class="status-message success">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
        echo '</div>';
        unset($_SESSION['register_success']);
    }
    ?>

    <div class="form-container">
        <p class="back-link"><a href="index.php">← Back to Shopping</a></p>
        
        <h2>Login to ShopSecure</h2>
        <form action="login.php" method="POST">
            
            <label for="role">Logging in as:</label>
            <select id="role" name="role" required>
                <option value="customer">Customer</option>
                <option value="manager">Manager / Admin</option>
            </select>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">Show Password</button>
            </div>

            <button type="submit">Log In</button>
        </form>
        <p>New user? <a href="register.php">Register here</a></p> 
    </div>
    
    <script>
    //JavaScript function to toggle password visibility
    function togglePasswordVisibility(fieldId, buttonElement) {
        const field = document.getElementById(fieldId);
        if (field.type === 'password') {
            field.type = 'text';
            buttonElement.textContent = 'Hide';
        } else {
            field.type = 'password';
            buttonElement.textContent = 'Show';
        }
    }
    </script>
</body>
</html>
