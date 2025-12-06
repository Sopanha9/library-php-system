<?php
session_start();
require_once '../config/db.php';

// Protection
// if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
//     header("Location: ../auth/login.php");
//     exit();
// }

include '../includes/header.php';

// Real-time stats
$total_books     = $pdo->query("SELECT SUM(total_quantity) FROM books")->fetchColumn();
$available_books = $pdo->query("SELECT SUM(available_quantity) FROM books")->fetchColumn();
$total_members   = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$issued_today    = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE issue_date = CURDATE()")->fetchColumn();
$overdue         = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE due_date < CURDATE() AND status = 'Issued'")->fetchColumn();
$total_fine      = $pdo->query("SELECT SUM(fine_amount) FROM issued_books WHERE fine_amount > 0")->fetchColumn(0);
?>

<div class="flex">
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-72 ">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="p-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
          Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></span>! 👋
        </h1>
        <p class="text-gray-600">Here's what's happening with your library today</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Total Books Card -->
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-book text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-3xl font-bold mb-1"><?= number_format($total_books ?? 0) ?></h3>
            <p class="text-blue-100 text-sm">Total Books</p>
          </div>
        </div>

        <!-- Available Books Card -->
        <div class="stat-card bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-check-circle text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Available</span>
            </div>
            <h3 class="text-3xl font-bold mb-1"><?= number_format($available_books ?? 0) ?></h3>
            <p class="text-green-100 text-sm">Available Books</p>
          </div>
        </div>

        <!-- Total Members Card -->
        <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-users text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Active</span>
            </div>
            <h3 class="text-3xl font-bold mb-1"><?= number_format($total_members ?? 0) ?></h3>
            <p class="text-purple-100 text-sm">Total Members</p>
          </div>
        </div>

        <!-- Issued Today Card -->
        <div class="stat-card bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-calendar-check text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Today</span>
            </div>
            <h3 class="text-3xl font-bold mb-1"><?= number_format($issued_today ?? 0) ?></h3>
            <p class="text-amber-100 text-sm">Issued Today</p>
          </div>
        </div>

        <!-- Overdue Books Card -->
        <div class="stat-card bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Alert</span>
            </div>
            <h3 class="text-3xl font-bold mb-1"><?= number_format($overdue ?? 0) ?></h3>
            <p class="text-red-100 text-sm">Overdue Books</p>
          </div>
        </div>

        <!-- Total Fine Card -->
        <div class="stat-card bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl shadow-lg hover:shadow-2xl p-6 text-white relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-lg">
                <i class="fas fa-coins text-2xl"></i>
              </div>
              <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full">Riel</span>
            </div>
            <h3 class="text-3xl font-bold mb-1">៛<?= number_format($total_fine ?? 0) ?></h3>
            <p class="text-slate-200 text-sm">Total Fine Amount</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-bolt text-yellow-500 mr-3"></i>
          Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <a href="../books/manage.php" class="group flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 border-2 border-blue-200 transition-all duration-300 hover:scale-105">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
              <i class="fas fa-plus text-white text-xl"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">Add Book</h3>
              <p class="text-xs text-gray-600">New entry</p>
            </div>
          </a>

          <a href="../members/manage.php" class="group flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 border-2 border-green-200 transition-all duration-300 hover:scale-105">
            <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
              <i class="fas fa-user-plus text-white text-xl"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">Register Member</h3>
              <p class="text-xs text-gray-600">New member</p>
            </div>
          </a>

          <a href="../circulation/issue_book.php" class="group flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-br from-purple-50 to-violet-50 hover:from-purple-100 hover:to-violet-100 border-2 border-purple-200 transition-all duration-300 hover:scale-105">
            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
              <i class="fas fa-arrow-right-arrow-left text-white text-xl"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">Issue Book</h3>
              <p class="text-xs text-gray-600">Lend to member</p>
            </div>
          </a>

          <a href="../circulation/overdue.php" class="group flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-br from-red-50 to-rose-50 hover:from-red-100 hover:to-rose-100 border-2 border-red-200 transition-all duration-300 hover:scale-105">
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
              <i class="fas fa-bell text-white text-xl"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">View Overdue</h3>
              <p class="text-xs text-gray-600">Check alerts</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Recent Activity & Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-clock-rotate-left text-indigo-500 mr-3"></i>
            Recent Activity
          </h2>
          <div class="space-y-4">
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-book text-blue-600"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">New book added</p>
                <p class="text-xs text-gray-500">2 hours ago</p>
              </div>
            </div>
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-green-600"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Member registered</p>
                <p class="text-xs text-gray-500">5 hours ago</p>
              </div>
            </div>
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
              <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exchange text-purple-600"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Book issued</p>
                <p class="text-xs text-gray-500">1 day ago</p>
              </div>
            </div>
          </div>
        </div>

        <!-- System Info -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
          <h2 class="text-xl font-bold mb-4 flex items-center">
            <i class="fas fa-chart-line mr-3"></i>
            System Overview
          </h2>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-indigo-100">Books Utilization</span>
              <span class="font-bold"><?= $total_books > 0 ? round((($total_books - $available_books) / $total_books) * 100) : 0 ?>%</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-2">
              <div class="bg-white h-2 rounded-full" style="width: <?= $total_books > 0 ? round((($total_books - $available_books) / $total_books) * 100) : 0 ?>%"></div>
            </div>
            
            <div class="flex justify-between items-center mt-6">
              <span class="text-indigo-100">Active Members</span>
              <span class="font-bold"><?= $total_members ?? 0 ?></span>
            </div>
            
            <div class="flex justify-between items-center">
              <span class="text-indigo-100">Overdue Rate</span>
              <span class="font-bold text-yellow-300"><?= $overdue ?? 0 ?> books</span>
            </div>
            
            <div class="mt-6 p-4 bg-white/10 rounded-xl backdrop-blur-lg">
              <p class="text-sm text-indigo-100">📊 System running smoothly</p>
              <p class="text-xs text-indigo-200 mt-1">Last updated: <?= date('F j, Y, g:i a') ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>