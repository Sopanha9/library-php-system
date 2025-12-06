<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    echo json_encode([]);
    exit();
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    // Search available books - without quantity column
    $sql = "SELECT * FROM books 
            WHERE (title LIKE ? OR author LIKE ? OR isbn LIKE ?)
            LIMIT 10";
    
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add quantity = 1 for compatibility (assuming all books are available)
    foreach ($books as &$book) {
        $book['quantity'] = 1;
    }
    
    echo json_encode($books);
    
} catch (PDOException $e) {
    // Return error for debugging
    echo json_encode(['error' => $e->getMessage()]);
}
