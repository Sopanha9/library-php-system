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
    if (empty($_POST['issue_id']) || empty($_POST['return_date']) || empty($_POST['book_condition'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit();
    }

    $issueId = $_POST['issue_id'];
    $returnDate = $_POST['return_date'];
    $bookCondition = $_POST['book_condition'];
    $notes = $_POST['notes'] ?? null;
    $returnedBy = $_POST['returned_by'];
    
    // Get issue details
    $stmt = $pdo->prepare("SELECT * FROM issued_books WHERE issue_id = ?");
    $stmt->execute([$issueId]);
    $issue = $stmt->fetch();
    
    if (!$issue) {
        echo json_encode(['success' => false, 'message' => 'Issue record not found']);
        exit();
    }
    
    if ($issue['return_date'] !== null) {
        echo json_encode(['success' => false, 'message' => 'Book already returned']);
        exit();
    }
    
    // Calculate fine
    $dueDate = new DateTime($issue['due_date']);
    $returnDateTime = new DateTime($returnDate);
    $diff = $returnDateTime->diff($dueDate);
    $daysOverdue = 0;
    $fine = 0;
    
    if ($returnDateTime > $dueDate) {
        // Get library settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM library_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $finePerDay = $settings['fine_per_day'] ?? 1.00;
        $gracePeriod = $settings['grace_period_days'] ?? 0;
        $maxFine = $settings['max_fine_amount'] ?? 50.00;
        
        $daysOverdue = $diff->days;
        
        if ($daysOverdue > $gracePeriod) {
            $fine = ($daysOverdue - $gracePeriod) * $finePerDay;
            $fine = min($fine, $maxFine);
        }
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Update issued_books record
    $sql = "UPDATE issued_books SET 
                return_date = ?,
                book_condition_on_return = ?,
                fine_amount = ?,
                notes = ?,
                returned_by = ?
            WHERE issue_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$returnDate, $bookCondition, $fine, $notes, $returnedBy, $issueId]);
    
    // Record payment if fine was paid
    $paymentAmount = $_POST['payment_amount'] ?? 0;
    if ($paymentAmount > 0) {
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $receiptNumber = 'RCP-' . time() . '-' . $issueId;
        
        $sql = "INSERT INTO fine_payments (issue_id, member_id, amount, payment_method, payment_date, receipt_number, recorded_by)
                VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$issueId, $issue['member_id'], $paymentAmount, $paymentMethod, $receiptNumber, $returnedBy]);
        
        // Update fine_paid in issued_books
        $stmt = $pdo->prepare("UPDATE issued_books SET fine_paid = fine_paid + ? WHERE issue_id = ?");
        $stmt->execute([$paymentAmount, $issueId]);
    }
    
    $pdo->commit();
    
    $message = 'Book returned successfully!';
    if ($fine > 0) {
        $outstanding = $fine - $paymentAmount;
        if ($outstanding > 0) {
            $message .= " Outstanding fine: " . number_format($outstanding, 2) . " Riel";
        } else {
            $message .= " Fine paid: " . number_format($paymentAmount, 2) . " Riel";
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'fine' => $fine,
        'paid' => $paymentAmount
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
