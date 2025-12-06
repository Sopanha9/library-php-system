<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Validate required fields
    if (empty($_POST['member_id']) || empty($_POST['book_id']) || empty($_POST['issue_date']) || empty($_POST['due_date'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit();
    }

    $memberId = $_POST['member_id'];
    $bookId = $_POST['book_id'];
    $issueDate = $_POST['issue_date'];
    $dueDate = $_POST['due_date'];
    $notes = $_POST['notes'] ?? null;
    $issuedBy = $_POST['issued_by'];

    // Validate member is active
    $stmt = $pdo->prepare("SELECT status, max_books_allowed FROM members WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        exit();
    }
    
    if ($member['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Member is not active']);
        exit();
    }

    // Check member's current borrows
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM issued_books WHERE member_id = ? AND return_date IS NULL");
    $stmt->execute([$memberId]);
    $currentBorrows = $stmt->fetchColumn();
    
    if ($currentBorrows >= $member['max_books_allowed']) {
        echo json_encode(['success' => false, 'message' => "Member has reached maximum borrow limit ({$member['max_books_allowed']} books)"]);
        exit();
    }

    // Check if member has outstanding fines above threshold
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(fine_amount - fine_paid), 0) as total_outstanding FROM issued_books WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $outstandingFine = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT setting_value FROM library_settings WHERE setting_key = 'fine_threshold_for_suspension'");
    $fineThreshold = $stmt->fetchColumn() ?? 20.00;
    
    if ($outstandingFine >= $fineThreshold) {
        echo json_encode(['success' => false, 'message' => "Member has outstanding fines of {$outstandingFine} Riel. Please clear fines first."]);
        exit();
    }

    // Check book exists (skip quantity check since column doesn't exist)
    $stmt = $pdo->prepare("SELECT book_id FROM books WHERE book_id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit();
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Insert issue record
    $sql = "INSERT INTO issued_books (book_id, member_id, issue_date, due_date, status, notes, issued_by) 
            VALUES (?, ?, ?, ?, 'Issued', ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bookId, $memberId, $issueDate, $dueDate, $notes, $issuedBy]);

    // Note: Skipping book quantity update since column doesn't exist
    // If you want to track availability, you'll need to add a quantity column to books table

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Book issued successfully!',
        'issue_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
