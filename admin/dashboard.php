<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: " . url('auth/login.php'));
    exit();
}

// Your stats queries here (same as before)
$total_books = $pdo->query("SELECT SUM(total_quantity) FROM books")->fetchColumn() ?? 0;
$available   = $pdo->query("SELECT SUM(available_quantity) FROM books")->fetchColumn() ?? 0;
$members     = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$issued_today = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE DATE(issue_date) = CURDATE()")->fetchColumn();
$overdue     = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE due_date < CURDATE() AND status='Issued'")->fetchColumn();
$total_fine  = $pdo->query("SELECT SUM(fine_amount) FROM issued_books")->fetchColumn() ?? 0;
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="ml-64 p-8 flex-1">
  <div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p class="text-xl text-indigo-600 font-semibold mt-2">Role: <?= ucfirst($_SESSION['role']) ?></p>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-blue-100 text-lg">Total Books</p>
          <p class="text-5xl font-extrabold mt-3"><?= $total_books ?></p>
        </div>
        <i class="fas fa-book-open text-7xl opacity-30"></i>
      </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <p class="text-green-100 text-lg">Available</p>
      <p class="text-5xl font-extrabold mt-3"><?= $available ?></p>
      <i class="fas fa-check-circle text-7xl opacity-30"></i>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-pink-600 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <p class="text-purple-100 text-lg">Total Members</p>
      <p class="text-5xl font-extrabold mt-3"><?= $members ?></p>
      <i class="fas fa-users text-7xl opacity-30"></i>
    </div>

    <div class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <p class="text-yellow-100 text-lg">Issued Today</p>
      <p class="text-5xl font-extrabold mt-3"><?= $issued_today ?></p>
      <i class="fas fa-clock text-7xl opacity-30"></i>
    </div>

    <div class="bg-gradient-to-br from-red-500 to-rose-600 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <p class="text-red-100 text-lg">Overdue Books</p>
      <p class="text-5xl font-extrabold mt-3"><?= $overdue ?></p>
      <i class="fas fa-bell text-7xl opacity-30"></i>
    </div>

    <div class="bg-gradient-to-br from-gray-700 to-gray-900 text-white p-8 rounded-2xl shadow-xl transform hover:scale-105 transition">
      <p class="text-gray-300 text-lg">Total Fine</p>
      <p class="text-5xl font-extrabold mt-3"><?= number_format($total_fine) ?> ៛</p>
      <i class="fas fa-coins text-7xl opacity-30"></i>
    </div>
  </div>

  <!-- Quick Actions -->
  <h2 class="text-3xl font-bold text-gray-800 mb-6">Quick Actions</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <a href="<?= url('books/manage.php') ?>" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white p-10 rounded-2xl text-center shadow-2xl transform hover:scale-110 transition">
      <i class="fas fa-plus text-5xl mb-4"></i>
      <p class="text-xl font-bold">Add Book</p>
    </a>
    <a href="<?= url('auth/register_member.php') ?>" class="bg-gradient-to-r from-green-600 to-teal-600 hover:from-teal-700 hover:to-green-700 text-white p-10 rounded-2xl text-center shadow-2xl transform hover:scale-110 transition">
      <i class="fas fa-user-plus text-5xl mb-4"></i>
      <p class="text-xl font-bold">New Member</p>
    </a>
    <a href="<?= url('issue/issue_book.php') ?>" class="bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-orange-700 hover:to-red-600 text-white p-10 rounded-2xl text-center shadow-2xl transform hover:scale-110 transition">
      <i class="fas fa-exchange-alt text-5xl mb-4"></i>
      <p class="text-xl font-bold">Issue Book</p>
    </a>
    <a href="<?= url('reports/overdue.php') ?>" class="bg-gradient-to-r from-red-600 to-rose-700 hover:from-rose-700 hover:to-pink-700 text-white p-10 rounded-2xl text-center shadow-2xl transform hover:scale-110 transition">
      <i class="fas fa-exclamation-circle text-5xl mb-4"></i>
      <p class="text-xl font-bold">Overdue List</p>
    </a>
  </div>
</main>

<?php include '../includes/footer.php'; ?>