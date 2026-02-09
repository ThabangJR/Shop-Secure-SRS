<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_config.php';

//HANDLER LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //sanitize and validate user inputs
    $name = sanitize($_POST['name']);
    $email = filter_var(sanitize($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    //basic validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $_SESSION['register_error'] = 'Invalid input. Ensure name, valid email, and password of 8+ characters are provided.';
    } else {
        // 1. check to see if an email already exists
        $stmt = $pdo->prepare("SELECT customerID FROM Customers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // Failure: email already exists - use session error message
            $_SESSION['register_error'] = 'The email you have entered already exists. Please try registering with a different email.';
        } else {
            // 2. Registration Process
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = 'customer'; 
            
            try {
                $stmt = $pdo->prepare("INSERT INTO Customers (name, email, password_hash, role) VALUES (?, ?, ?, ?)");

                if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                    // Success: redirects to login page to display success message
                    $_SESSION['register_success'] = 'Registration successful! You can now log in.';
                    header('Location: login.php'); 
                    exit; 
                } else {
                    $_SESSION['register_error'] = 'Registration failed due to a database error.';
                    
                }
            } catch (PDOException $e) {
                error_log("Registration PDO error: " . $e->getMessage());
                $_SESSION['register_error'] = 'Registration failed due to a system error. Please try again.';
                //Fall through to display HTML
            }
        }
    }
} 


//DISPLAY LOGIC (HTML) 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShopSecure</title>
    <link rel="stylesheet" href="styles.css">
    <style>
   
        .form-container h2, 
        .form-container p {
            text-align: center;
        }

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

      
        .form-container form button[type="submit"] {
            display: block; 
            width: 100%;
            max-width: 300px;
            margin: 20px auto 0 auto; 
        }
    </style>
</head>
<body>
    
    <?php
    //DISPLAY STATUS MESSAGES
    if (isset($_SESSION['register_error'])) {
        echo '<div class="form-container" style="padding-bottom: 0; box-shadow: none; background: none;">';
        echo '<div class="status-message error">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
        echo '</div>';
        unset($_SESSION['register_error']); //clear the error message after displaying it once
    }
    ?>
    
    <div class="form-container">
        <p class="back-link"><a href="index.php">← Back to Shopping</a></p>

        <h2>Customer Registration</h2>
        <form action="register.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" 
                       required minlength="8" 
                       title="Must be at least 8 characters.">
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">Show Password</button>
            </div>
            
            <button type="submit">Register Account</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
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
