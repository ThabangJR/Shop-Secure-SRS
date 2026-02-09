# Shop-Secure-SRS

ShopSecure SRS is a web-based e-commerce and inventory management system. It combines a PHP frontend/API with a Java-based reporting engine to provide secure transactions and detailed inventory analytics.

### 🛒 Core Features
Shopping Cart & Checkout: Full session-based cart management (cart_handler.php).

User Authentication: Secure login and registration system.

Manager Dashboard: Real-time overview of inventory and orders.

Java Inventory Reporting: A dedicated Java utility (InventoryReporter) that generates high-performance text reports for stock levels.

Product API: A lightweight backend (product_api.php) to serve product data to the frontend.

### 🛠️ Tech Stack
Languages: PHP, Java, JavaScript, HTML/CSS

Database: MySQL

Java Library: MySQL Connector/J 9.4.0 (for database connectivity within Java)

### 🚀 How to Run the Reporting Engine
To run the inventory report manually from the command line:


"java -cp ".;mysql-connector-j-9.4.0.jar" InventoryReporter"

### 🔧 Database Configuration
To connect the application to your local MySQL environment, follow these steps:

### 1. Create the Database
Open phpMyAdmin or your preferred MySQL terminal.

Create a new database named shopsecure_db.

Import the ShopSecureSRS.sql file provided in this repository to generate the required tables.

### 2. Configure PHP Connection
Locate db_config_sample.php in the root directory.

Update the following variables to match your local server credentials:

### PHP
$host = "localhost";
$user = "your_username";     // usually "root"
$pass = "your_password";     // usually empty "" on XAMPP
$db_name = "shopsecure_db";
###
### 3. Configure Java Connection
The Java reporting engine (InventoryReporter.java) uses a JDBC connection string.

Ensure the connection line in the source code matches your PHP settings: jdbc:mysql://localhost:3306/shopsecure_db

Make sure the mysql-connector-j-9.4.0.jar is in the same folder when running the reporter.

### NB: default credentilas for the manager are: 
username: manager@srs.com
password: password123
