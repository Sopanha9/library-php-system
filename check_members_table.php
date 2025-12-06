<?php
// Check current members table structure
require_once 'config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Check Members Table</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #4F46E5; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .missing { background: #fee; }
        .exists { background: #efe; }
        h1 { color: #333; }
        .btn { display: inline-block; padding: 10px 20px; background: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
<h1>📊 Members Table Structure</h1>";

try {
    // Get current columns
    $stmt = $pdo->query("DESCRIBE members");
    $currentColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Columns:</h2>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($currentColumns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Required columns
    $requiredColumns = [
        'member_id', 'full_name', 'email', 'phone', 'address',
        'date_of_birth', 'id_number', 'profile_photo', 'membership_type',
        'join_date', 'status', 'max_books_allowed', 'notes'
    ];
    
    $existingColumnNames = array_column($currentColumns, 'Field');
    $missingColumns = array_diff($requiredColumns, $existingColumnNames);
    
    if (empty($missingColumns)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h2 style='color: green; margin: 0;'>✅ All Required Columns Exist!</h2>
                <p style='margin: 10px 0 0 0;'>Your members table is ready to use.</p>
              </div>";
        echo "<a href='members/manage.php' class='btn'>Go to Member Management →</a>";
    } else {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h2 style='color: #856404; margin: 0;'>⚠️ Missing Columns</h2>
              </div>";
        
        echo "<h3>Run these SQL statements:</h3>";
        echo "<pre>";
        
        // Generate only the missing ALTER statements
        $sqlStatements = [];
        $columnDefinitions = [
            'date_of_birth' => "ALTER TABLE members ADD COLUMN date_of_birth DATE AFTER address;",
            'id_number' => "ALTER TABLE members ADD COLUMN id_number VARCHAR(50) AFTER date_of_birth;",
            'profile_photo' => "ALTER TABLE members ADD COLUMN profile_photo VARCHAR(255) AFTER id_number;",
            'membership_type' => "ALTER TABLE members ADD COLUMN membership_type ENUM('standard', 'premium', 'student') DEFAULT 'standard' AFTER profile_photo;",
            'status' => "ALTER TABLE members ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER join_date;",
            'max_books_allowed' => "ALTER TABLE members ADD COLUMN max_books_allowed INT DEFAULT 5 AFTER status;",
            'notes' => "ALTER TABLE members ADD COLUMN notes TEXT AFTER max_books_allowed;"
        ];
        
        foreach ($missingColumns as $col) {
            if (isset($columnDefinitions[$col])) {
                echo $columnDefinitions[$col] . "\n";
                $sqlStatements[] = $columnDefinitions[$col];
            }
        }
        
        echo "</pre>";
        
        // Auto-execute the missing columns
        if (!empty($sqlStatements)) {
            echo "<h3>Auto-adding missing columns...</h3>";
            foreach ($sqlStatements as $sql) {
                try {
                    $pdo->exec($sql);
                    echo "<div style='color: green;'>✅ " . htmlspecialchars($sql) . "</div>";
                } catch (PDOException $e) {
                    echo "<div style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: green; margin: 0;'>✅ Done! Refresh this page to verify.</h3>
                  </div>";
            echo "<a href='check_members_table.php' class='btn'>Refresh</a>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; color: red;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
