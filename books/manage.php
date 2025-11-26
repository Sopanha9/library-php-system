<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <h2><i class="fas fa-book"></i> Manage Books</h2>
        <hr>

        <!-- Add Book Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fas fa-plus"></i> Add New Book
        </button>

        <!-- Search & Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="Search by title/author/ISBN...">
                    </div>
                    <div class="col-md-3">
                        <select id="category_filter" class="form-select">
                            <option value="">All Categories</option>
                            <?php
                            $cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                            foreach ($cats as $cat) {
                                echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Table (loaded by AJAX) -->
        <div id="booksTable">
            <?php include 'get_books.php'; ?>
        </div>
    </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Book</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBookForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="title" class="form-control" placeholder="Title" required></div>
                        <div class="col-md-6"><input type="text" name="author" class="form-control" placeholder="Author" required></div>
                        <div class="col-md-4"><input type="text" name="isbn" class="form-control" placeholder="ISBN"></div>
                        <div class="col-md-4">
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($cats as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><input type="text" name="publisher" class="form-control" placeholder="Publisher"></div>
                        <div class="col-md-4"><input type="number" name="price" class="form-control" placeholder="Price" step="0.01"></div>
                        <div class="col-md-4"><input type="number" name="total_quantity" class="form-control" placeholder="Total Copies" required min="1"></div>
                        <div class="col-md-4"><input type="number" name="damaged_quantity" class="form-control" placeholder="Damaged (0)" min="0" value="0"></div>
                        <div class="col-md-4"><input type="number" name="lost_quantity" class="form-control" placeholder="Lost (0)" min="0" value="0"></div>
                        <div class="col-md-4"><input type="number" name="published_year" class="form-control" placeholder="Year"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load books with search & filter
function loadBooks() {
    const search = document.getElementById('search').value;
    const cat = document.getElementById('category_filter').value;
    fetch(`get_books.php?search=${search}&category=${cat}`)
        .then(r => r.text())
        .then(html => document.getElementById('booksTable').innerHTML = html);
}

// Live search
document.getElementById('search').addEventListener('input', loadBooks);
document.getElementById('category_filter').addEventListener('change', loadBooks);

// Add Book AJAX
document.getElementById('addBookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('add_book.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        alert(data.success ? 'Book added!' : data.message);
        if (data.success) {
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('addBookModal')).hide();
            loadBooks();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>