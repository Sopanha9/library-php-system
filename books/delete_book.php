<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $book_id = (int)$data['book_id'];

        // Check if book is currently issued
        $check = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = ? AND status = 'Issued'");
        $check->execute([$book_id]);
        $issued_count = $check->fetchColumn();

        if ($issued_count > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cannot delete! This book has active issues.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
        $success = $stmt->execute([$book_id]);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Book deleted successfully!' : 'Failed to delete book'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
