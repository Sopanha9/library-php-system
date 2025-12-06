<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['member_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID and status required']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE members SET status = ? WHERE member_id = ?");
    $stmt->execute([$data['status'], $data['member_id']]);

    $statusText = $data['status'] === 'active' ? 'activated' : 'suspended';
    
    echo json_encode([
        'success' => true,
        'message' => "Member {$statusText} successfully!"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
