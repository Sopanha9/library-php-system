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
    // Search members who have borrowed books
    $sql = "SELECT DISTINCT
                m.member_id,
                m.full_name,
                m.email,
                m.phone,
                COUNT(ib.issue_id) as borrowed_count
            FROM members m
            JOIN issued_books ib ON m.member_id = ib.member_id
            WHERE ib.return_date IS NULL
            AND (m.full_name LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)
            GROUP BY m.member_id
            LIMIT 10";
    
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($members);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
