<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    try {
        $book_id = (int)$_GET['id'];
        
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if ($book) {
            echo json_encode($book);
        } else {
            echo json_encode(['error' => 'Book not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No book ID provided']);
}
?>
