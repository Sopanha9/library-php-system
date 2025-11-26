<?php
session_start();
require_once '../config/db.php';

// Protection
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/header.php';

// Real-time stats
$total_books     = $pdo->query("SELECT SUM(total_quantity) FROM books")->fetchColumn();
$available_books = $pdo->query("SELECT SUM(available_quantity) FROM books")->fetchColumn();
$total_members   = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$issued_today    = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE issue_date = CURDATE()")->fetchColumn();
$overdue         = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE due_date < CURDATE() AND status = 'Issued'")->fetchColumn();
$total_fine      = $pdo->query("SELECT SUM(fine_amount) FROM issued_books WHERE fine_amount > 0")->fetchColumn(0);
?>

<div class="d-flex">
  <?php include '../includes/sidebar.php'; ?>

  <div class="flex-grow-1 p-4">
    <h2>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p class="text-muted">Role: <strong><?= ucfirst($_SESSION['role']) ?></strong></p>
    <hr>

    <!-- Stats Cards -->
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card stat-card bg-primary text-white">
          <div class="card-body">
            <h5><i class="fas fa-book"></i> Total Books</h5>
            <h2><?= $total_books ?? 0 ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card bg-success text-white">
          <div class="card-body">
            <h5><i class="fas fa-check-circle"></i> Available</h5>
            <h2><?= $available_books ?? 0 ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card bg-info text-white">
          <div class="card-body">
            <h5><i class="fas fa-users"></i> Total Members</h5>
            <h2><?= $total_members ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card bg-warning text-white">
          <div class="card-body">
            <h5><i class="fas fa-clock"></i> Issued Today</h5>
            <h2><?= $issued_today ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card bg-danger text-white">
          <div class="card-body">
            <h5><i class="fas fa-exclamation"></i> Overdue Books</h5>
            <h2><?= $overdue ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card bg-dark text-white">
          <div class="card-body">
            <h5><i class="fas fa-dollar-sign"></i> Total Fine (Riel)</h5>
            <h2><?= number_format($total_fine ?? 0) ?></h2>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <h3>Quick Actions</h3>
      <div class="row g-3">
        <div class="col-md-3"><a href="../books/manage.php" class="btn btn-primary w-100 p-3"><i class="fas fa-plus"></i> Add New Book</a></div>
        <div class="col-md-3"><a href="../members/register.php" class="btn btn-success w-100 p-3"><i class="fas fa-user-plus"></i> Register Member</a></div>
        <div class="col-md-3"><a href="../issue/issue_book.php" class="btn btn-warning w-100 p-3"><i class="fas fa-exchange-alt"></i> Issue Book Now</a></div>
        <div class="col-md-3"><a href="../reports/overdue.php" class="btn btn-danger w-100 p-3"><i class="fas fa-bell"></i> View Overdue</a></div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>