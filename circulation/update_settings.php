<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Only administrators can update settings']);
    exit();
}

try {
    $settings = [
        'fine_per_day',
        'grace_period_days',
        'max_fine_amount',
        'default_borrow_days',
        'max_books_per_member',
        'fine_threshold_for_suspension'
    ];
    
    $pdo->beginTransaction();
    
    foreach ($settings as $key) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            
            $sql = "UPDATE library_settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$value, $key]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully!'
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error updating settings: ' . $e->getMessage()
    ]);
}
