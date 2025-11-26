<?php
require_once '../config/db.php';
$search = $_GET['search'] ?? '';
$cat = $_GET['category'] ?? '';

$sql = "SELECT b.*, c.name as category_name FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id WHERE 1=1";
if ($search) $sql .= " AND (b.title LIKE :search OR b.author LIKE :search OR b.isbn LIKE :search)";
if ($cat) $sql .= " AND b.category_id = :cat";
$sql .= " ORDER BY b.book_id DESC";

$stmt = $pdo->prepare($sql);
if ($search) $stmt->bindValue(':search', "%$search%");
if ($cat) $stmt->bindValue(':cat', $cat);
$stmt->execute();
$books = $stmt->fetchAll();
?>

<table class="table table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th><th>Title</th><th>Author</th><th>ISBN</th><th>Category</th>
            <th>Total</th><th>Available</th><th>Damaged</th><th>Lost</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($books as $i => $b): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><?= htmlspecialchars($b['isbn']) ?></td>
            <td><?= htmlspecialchars($b['category_name'] ?? 'None') ?></td>
            <td><?= $b['total_quantity'] ?></td>
            <td><span class="badge bg-success"><?= $b['available_quantity'] ?></span></td>
            <td><span class="badge bg-warning"><?= $b['damaged_quantity'] ?></span></td>
            <td><span class="badge bg-danger"><?= $b['lost_quantity'] ?></span></td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editBook(<?= $b['book_id'] ?>)">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteBook(<?= $b['book_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>