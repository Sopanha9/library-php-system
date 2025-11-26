<div class="sidebar text-white p-3">
  <h4 class="text-center mb-4">
    <i class="fas fa-book"></i> Library System
  </h4>
  <hr class="bg-light">
  <a href="dashboard.php" class="d-block p-3 rounded <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active bg-primary' : '' ?>">
    <i class="fas fa-tachometer-alt"></i> Dashboard
  </a>
  <a href="../books/manage.php" class="d-block p-3 rounded"><i class="fas fa-book"></i> Manage Books</a>
  <a href="../members/manage.php" class="d-block p-3 rounded"><i class="fas fa-users"></i> Manage Members</a>
  <a href="../issue/issue_book.php" class="d-block p-3 rounded"><i class="fas fa-exchange-alt"></i> Issue Book</a>
  <a href="../issue/return_book.php" class="d-block p-3 rounded"><i class="fas fa-undo"></i> Return Book</a>
  <a href="../reports/overdue.php" class="d-block p-3 rounded"><i class="fas fa-exclamation-triangle"></i> Overdue & Fines</a>
  <a href="../auth/logout.php" class="d-block p-3 rounded text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>