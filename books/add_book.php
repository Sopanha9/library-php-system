<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

        $available = $total - $damaged - $lost;
        
        // Handle image upload
        $cover_image = null;
        $upload_debug = [];
        
        if (isset($_FILES['book_image'])) {
            $upload_debug['file_exists'] = true;
            $upload_debug['error'] = $_FILES['book_image']['error'];
            $upload_debug['name'] = $_FILES['book_image']['name'];
            $upload_debug['size'] = $_FILES['book_image']['size'];
            $upload_debug['type'] = $_FILES['book_image']['type'];
            
            if ($_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                $file_type = $_FILES['book_image']['type'];
                $file_size = $_FILES['book_image']['size'];
                
                if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                    $extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'book_' . time() . '_' . uniqid() . '.' . $extension;
                    $upload_path = __DIR__ . '/../uploads/books/' . $filename;
                    
                    $upload_debug['upload_path'] = $upload_path;
                    $upload_debug['directory_exists'] = is_dir(dirname($upload_path));
                    $upload_debug['directory_writable'] = is_writable(dirname($upload_path));
                    
                    if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_path)) {
                        $cover_image = $filename;
                        $upload_debug['upload_success'] = true;
                    } else {
                        $upload_debug['upload_success'] = false;
                        $upload_debug['move_error'] = error_get_last();
                    }
                } else {
                    $upload_debug['validation_failed'] = true;
                    $upload_debug['type_allowed'] = in_array($file_type, $allowed_types);
                    $upload_debug['size_ok'] = $file_size <= $max_size;
                    
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Invalid image format or size too large (max 2MB)',
                        'debug' => $upload_debug
                    ]);
                    exit;
                }
            } else {
                $upload_debug['upload_error'] = $_FILES['book_image']['error'];
            }
        } else {
            $upload_debug['no_file_uploaded'] = true;
        }

        $stmt = $pdo->prepare("INSERT INTO books 
            (title, author, isbn, category_id, publisher, price, total_quantity, available_quantity, 
            damaged_quantity, lost_quantity, published_year, cover_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $title, $author, $isbn, $category_id, $publisher, $price, 
            $total, $available, $damaged, $lost, $year, $cover_image
        ]);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Book added successfully!' : 'Failed to add book',
            'cover_image' => $cover_image,
            'debug' => $upload_debug
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>