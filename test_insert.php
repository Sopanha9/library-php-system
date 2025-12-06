<?php
require_once 'config/db.php';

// Test what columns actually exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM fine_payments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Columns in fine_payments table:</h3>";
    echo "<pre>";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
    
    // Try a test insert
    echo "<h3>Testing INSERT:</h3>";
    $testSql = "INSERT INTO fine_payments (issue_id, member_id, amount, payment_method, payment_date, receipt_number, recorded_by)
                VALUES (1, 1, 10.50, 'cash', NOW(), 'TEST-123', 1)";
    echo "<pre>" . htmlspecialchars($testSql) . "</pre>";
    
    $pdo->exec($testSql);
    echo "<p style='color: green;'>✓ INSERT successful!</p>";
    
    // Delete the test row
    $pdo->exec("DELETE FROM fine_payments WHERE receipt_number = 'TEST-123'");
    echo "<p>Test row deleted.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
