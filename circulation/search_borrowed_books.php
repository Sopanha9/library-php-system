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
    // Search borrowed books
    $sql = "SELECT 
                ib.*,
                b.title as book_title,
                b.author as book_author,
                m.full_name as member_name,
                m.email as member_email,
                m.phone as member_phone
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            JOIN members m ON ib.member_id = m.member_id
            WHERE ib.return_date IS NULL
            AND (b.title LIKE ? OR b.isbn LIKE ?)
            LIMIT 10";
    
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm]);
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($books);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
