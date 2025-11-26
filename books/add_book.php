<?php
require_once '../config/db_connect.php';
header('Content-Type: application/json');

if ($_POST) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'] ?? null;
    $category_id = $_POST['category_id'] ?: null;
    $publisher = $_POST['publisher'] ?? null;
    $price = $_POST['price'] ?: null;
    $total = (int)$_POST['total_quantity'];
    $damaged = (int)($_POST['damaged_quantity'] ?? 0);
    $lost = (int)($_POST['lost_quantity'] ?? 0);
    $year = $_POST['published_year'] ?: null;

    $available = $total - $damaged - $lost;

    $stmt = $pdo->prepare("INSERT INTO books 
        (title, author, isbn, category_id, publisher, price, total_quantity, available_quantity, damaged_quantity, lost_quantity, published_year)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $success = $stmt->execute([$title, $author, $isbn, $category_id, $publisher, $price, $total, $available, $damaged, $lost, $year]);

    echo json_encode(['success' => $success, 'message' => $success ? 'Book added!' : 'Error']);
}
?>