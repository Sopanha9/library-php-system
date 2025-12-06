<?php
// Quick setup script to create members table
require_once 'config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Members Module</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid blue; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🚀 Setup Members Module</h1>";

try {
    // Read the SQL file
    $sqlFile = __DIR__ . '/database_members.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("database_members.sql file not found!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split into individual statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    
    // Split by semicolon but keep statements together
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errors = [];
    
    echo "<div class='info'>📋 Found " . count($statements) . " SQL statements to execute...</div>";
    
    foreach ($statements as $index => $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show what was created
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "<div class='success'>✅ Created table: <strong>{$tableName}</strong></div>";
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO.*?`?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "<div class='success'>✅ Inserted data into: <strong>{$tableName}</strong></div>";
            }
            
        } catch (PDOException $e) {
            // Check if error is "table already exists" - that's okay
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<div class='info'>ℹ️ Table already exists (skipped)</div>";
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<div class='info'>ℹ️ Data already exists (skipped)</div>";
            } else {
                $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
            }
        }
    }
    
    echo "<hr>";
    echo "<div class='success'><h2>✅ Setup Complete!</h2>";
    echo "<p>Successfully executed <strong>{$successCount}</strong> statements.</p>";
    
    if (count($errors) > 0) {
        echo "<div class='error'><h3>⚠️ Some errors occurred:</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
    
    // Verify tables exist
    echo "<h3>📊 Verification:</h3>";
    $tables = ['members', 'library_settings', 'issued_books', 'fine_payments'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "<div class='success'>✅ Table <strong>{$table}</strong> exists with {$count} record(s)</div>";
        } else {
            echo "<div class='error'>❌ Table <strong>{$table}</strong> not found</div>";
        }
    }
    
    echo "</div>";
    echo "<a href='members/manage.php' class='btn'>Go to Member Management →</a>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
}

echo "</div></body></html>";
?>
