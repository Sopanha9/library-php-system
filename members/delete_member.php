<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID required']);
    exit();
}

try {
    $memberId = $data['member_id'];

    // Check if member has active book borrows
    $checkBorrows = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE member_id = ? AND return_date IS NULL");
    $checkBorrows->execute([$memberId]);
    $activeBorrows = $checkBorrows->fetchColumn();

    if ($activeBorrows > 0) {
        echo json_encode([
            'success' => false,
            'message' => "Cannot delete member with {$activeBorrows} active book borrow(s). Please return all books first."
        ]);
        exit();
    }

    // Get member photo to delete
    $stmt = $pdo->prepare("SELECT profile_photo FROM members WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();

    // Delete member photo
    if ($member && $member['profile_photo']) {
        $photoPath = __DIR__ . '/../uploads/members/' . $member['profile_photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    // Delete member
    $deleteStmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
    $deleteStmt->execute([$memberId]);

    echo json_encode([
        'success' => true,
        'message' => 'Member deleted successfully!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
