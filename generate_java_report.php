<?php
require 'db_config.php';
require_manager(); //ONLY MANAGERS CAN RUN THIS SCRIPT


$JAVA_CLASS_DIR = __DIR__; 
$MYSQL_CONNECTOR_PATH = "C:/xampp/htdocs/ShopSecure SRS/mysql-connector-j-9.4.0.jar"; 
$classpath_separator = ';';

$command = "java -cp \"$JAVA_CLASS_DIR$classpath_separator$MYSQL_CONNECTOR_PATH\" InventoryReporter 2>&1";

$javaOutputLines = [];
$returnStatus = 0;
exec($command, $javaOutputLines, $returnStatus);
$javaOutput = implode("\n", $javaOutputLines); //combines lines into a single string

$reportFileName = 'java_report_' . date('YmdHis') . '.txt';

//checks to see if Java execution failed
if ($returnStatus !== 0) {
    $executionError = "Java Execution Failed! (Error Code: $returnStatus)\nCommand Executed: $command\nOutput (Errors):\n" . $javaOutput;
    error_log($executionError);
    $javaOutput = "ERROR: Failed to run Java report. Check server logs for details.\n\nJava Output:\n" . $javaOutput;
} else if (empty($javaOutput)) {
    //checks to see if the command ran but produced no output
    $javaOutput = "WARNING: Java command executed successfully but returned no data. Check database connection/query logic in InventoryReporter.java.";
}

//writes the captured output (even if it's an error/warning) to the file
file_put_contents($reportFileName, $javaOutput);

//collects low-stock items via PHP/MySQL
$stmt = $pdo->query("SELECT name, stockQty FROM Products WHERE stockQty < 10 ORDER BY stockQty ASC");
$lowStock = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Reports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>System Reports</h1>
            <nav><a href="manager_dashboard.php">Back to Dashboard</a></nav>
        </header>

        <h2>Java-Generated Report (High-Value Stock)</h2>
        <pre style="background: #f4f4f4; padding: 15px; border: 1px dashed #ccc;"><?php echo htmlspecialchars($javaOutput); ?></pre>
        <p><a href="<?php echo htmlspecialchars($reportFileName); ?>" download>Download Java Report File (<?php echo htmlspecialchars($reportFileName); ?>)</a></p>

        <hr>

        <h2>Low Stock Report!</h2>
        <?php if ($lowStock): ?>
            <ul>
                <?php foreach ($lowStock as $item): ?>
                <li><?php echo htmlspecialchars($item['name']); ?>: <?php echo htmlspecialchars($item['stockQty']); ?> units remaining.</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>All stock levels are currently acceptable (above 10 units).</p>
        <?php endif; ?>
    </div>
</body>
</html>