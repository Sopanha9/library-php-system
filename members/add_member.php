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
    if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit();
    }

    // Check if email already exists
    $checkEmail = $pdo->prepare("SELECT member_id FROM members WHERE email = ?");
    $checkEmail->execute([$_POST['email']]);
    if ($checkEmail->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }

    // Handle profile photo upload
    $photoFilename = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['profile_photo']['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG and PNG images are allowed']);
            exit();
        }

        if ($_FILES['profile_photo']['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Image size must be less than 2MB']);
            exit();
        }

        $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $photoFilename = 'member_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/members/' . $photoFilename;

        if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
            exit();
        }
    }

    // Insert member
    $sql = "INSERT INTO members (
                full_name, email, phone, address, date_of_birth, 
                id_number, membership_type, status, max_books_allowed, 
                profile_photo, notes, join_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'] ?? null,
        $_POST['date_of_birth'] ?? null,
        $_POST['id_number'] ?? null,
        $_POST['membership_type'] ?? 'standard',
        $_POST['status'] ?? 'active',
        $_POST['max_books_allowed'] ?? 5,
        $photoFilename,
        $_POST['notes'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Member added successfully!',
        'member_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
