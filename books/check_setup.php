<?php
// Database column checker and fixer
require_once '../config/db.php';

echo "<h2>Database Column Check</h2>";

// Check if cover_image column exists
try {
    $stmt = $pdo->query("DESCRIBE books");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current columns in 'books' table:</h3>";
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li>" . htmlspecialchars($col) . "</li>";
    }
    echo "</ul>";
    
    if (!in_array('cover_image', $columns)) {
        echo "<p style='color: red;'><strong>❌ cover_image column is MISSING!</strong></p>";
        echo "<p>Adding column now...</p>";
        
        $pdo->exec("ALTER TABLE books ADD COLUMN cover_image VARCHAR(255) NULL AFTER published_year");
        
        echo "<p style='color: green;'><strong>✅ cover_image column added successfully!</strong></p>";
        echo "<p><a href='manage.php'>Go back to Book Management</a></p>";
    } else {
        echo "<p style='color: green;'><strong>✅ cover_image column exists!</strong></p>";
        echo "<p><a href='manage.php'>Go back to Book Management</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check uploads directory
echo "<h3>Uploads Directory Check:</h3>";
$uploads_dir = '../uploads/books/';
if (is_dir($uploads_dir)) {
    if (is_writable($uploads_dir)) {
        echo "<p style='color: green;'>✅ Directory exists and is writable</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Directory exists but is NOT writable</p>";
        echo "<p>Run this command in PowerShell:</p>";
        echo "<code>icacls \"c:\\wamp64\\www\\library-php-system\\uploads\\books\" /grant Users:F</code>";
    }
    
    // List files
    $files = array_diff(scandir($uploads_dir), ['.', '..']);
    if (count($files) > 0) {
        echo "<p>Files in directory: " . count($files) . "</p>";
        echo "<ul>";
        foreach ($files as $file) {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No files uploaded yet</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Directory does NOT exist!</p>";
    if (mkdir($uploads_dir, 0777, true)) {
        echo "<p style='color: green;'>✅ Directory created successfully!</p>";
    }
}
?>
