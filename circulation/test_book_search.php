<?php
session_start();
require_once '../config/db.php';

// Simulate search
$query = 'a'; // Search for letter 'a'

echo "<h2>Testing Book Search</h2>";
echo "<p>Searching for: '{$query}'</p>";

try {
    $sql = "SELECT * FROM books 
            WHERE (title LIKE ? OR author LIKE ? OR isbn LIKE ?)
            AND quantity > 0
            LIMIT 10";
    
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Found " . count($books) . " books</h3>";
    echo "<pre>";
    print_r($books);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Also check all books
echo "<hr><h3>All Books in Database:</h3>";
try {
    $stmt = $pdo->query("SELECT book_id, title, author, isbn, quantity FROM books");
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($allBooks);
    echo "</pre>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
