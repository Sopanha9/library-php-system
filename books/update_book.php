<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $book_id = (int)$_POST['book_id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $isbn = !empty($_POST['isbn']) ? trim($_POST['isbn']) : null;
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $publisher = !empty($_POST['publisher']) ? trim($_POST['publisher']) : null;
        $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
        $total = (int)$_POST['total_quantity'];
        $damaged = !empty($_POST['damaged_quantity']) ? (int)$_POST['damaged_quantity'] : 0;
        $lost = !empty($_POST['lost_quantity']) ? (int)$_POST['lost_quantity'] : 0;
        $year = !empty($_POST['published_year']) ? (int)$_POST['published_year'] : null;

        // Calculate available quantity
        $available = $total - $damaged - $lost;
        
        // Handle image upload
        $cover_image = $_POST['current_image'] ?? null;
        
        if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            $file_type = $_FILES['book_image']['type'];
            $file_size = $_FILES['book_image']['size'];
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                // Delete old image if exists
                if ($cover_image && file_exists('../uploads/books/' . $cover_image)) {
                    unlink('../uploads/books/' . $cover_image);
                }
                
                $extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
                $filename = 'book_' . time() . '_' . uniqid() . '.' . $extension;
                $upload_path = '../uploads/books/' . $filename;
                
                if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_path)) {
                    $cover_image = $filename;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE books SET 
            title = ?, author = ?, isbn = ?, category_id = ?, publisher = ?, 
            price = ?, total_quantity = ?, available_quantity = ?, 
            damaged_quantity = ?, lost_quantity = ?, published_year = ?, cover_image = ?
            WHERE book_id = ?");
        
        $success = $stmt->execute([
            $title, $author, $isbn, $category_id, $publisher, $price, 
            $total, $available, $damaged, $lost, $year, $cover_image, $book_id
        ]);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Book updated successfully!' : 'Failed to update book'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
