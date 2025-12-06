<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get library settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM library_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$fine_per_day = $settings['fine_per_day'] ?? 1.00;
$max_fine = $settings['max_fine_amount'] ?? 50.00;

// Get statistics
$total_overdue = $pdo->query("SELECT COUNT(*) FROM issued_books WHERE return_date IS NULL AND due_date < CURDATE()")->fetchColumn();

$total_fines_query = $pdo->query("SELECT COALESCE(SUM(fine_amount), 0) as total FROM issued_books WHERE fine_amount > 0");
$total_fines = $total_fines_query->fetchColumn();

$collected_fines_query = $pdo->query("SELECT COALESCE(SUM(fine_paid), 0) as total FROM issued_books WHERE fine_paid > 0");
$collected_fines = $collected_fines_query->fetchColumn();

$outstanding_fines = $total_fines - $collected_fines;

include '../includes/header.php';
?>

<div class="flex">
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-72">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="p-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
          <i class="fas fa-triangle-exclamation text-orange-600 mr-3"></i>
          Overdue Books & Fines
        </h1>
        <p class="text-gray-600 mt-1">Track overdue books and manage fine payments</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Overdue</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_overdue) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(239, 68, 68, 0.3);">
              <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Fines</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_fines, 2) ?></h3>
              <p class="text-xs text-gray-500">Riel</p>
            </div>
            <div class="w-14 h-14 glass-blue rounded-xl flex items-center justify-center">
              <i class="fas fa-coins text-2xl text-blue-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Collected</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($collected_fines, 2) ?></h3>
              <p class="text-xs text-gray-500">Riel</p>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(34, 197, 94, 0.3);">
              <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Outstanding</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($outstanding_fines, 2) ?></h3>
              <p class="text-xs text-gray-500">Riel</p>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(251, 146, 60, 0.15) 0%, rgba(249, 115, 22, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(251, 146, 60, 0.3);">
              <i class="fas fa-money-bill-wave text-2xl text-orange-500"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="glass rounded-2xl overflow-hidden mb-6">
        <div class="border-b border-gray-200/50">
          <nav class="flex">
            <button onclick="switchTab('overdue')" id="tab-overdue" class="tab-button active px-6 py-4 font-semibold text-gray-600 border-b-2 border-blue-600 hover:text-blue-600 transition-all">
              <i class="fas fa-exclamation-triangle mr-2"></i>Overdue Books
            </button>
            <button onclick="switchTab('payments')" id="tab-payments" class="tab-button px-6 py-4 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600 transition-all">
              <i class="fas fa-money-bill-wave mr-2"></i>Payment History
            </button>
            <button onclick="switchTab('settings')" id="tab-settings" class="tab-button px-6 py-4 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600 transition-all">
              <i class="fas fa-cog mr-2"></i>Fine Settings
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          
          <!-- Overdue Books Tab -->
          <div id="content-overdue" class="tab-content">
            <div class="mb-4 flex items-center justify-between">
              <div class="relative flex-1 max-w-md">
                <input type="text" id="overdueSearch" placeholder="Search by member or book..." 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              </div>
              <button onclick="loadOverdueBooks()" class="ml-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all">
                <i class="fas fa-sync mr-2"></i>Refresh
              </button>
            </div>
            
            <div id="overdueBooksList">
              <div class="text-center py-20">
                <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                <p class="text-gray-600">Loading overdue books...</p>
              </div>
            </div>
          </div>

          <!-- Payment History Tab -->
          <div id="content-payments" class="tab-content hidden">
            <div class="mb-4 flex items-center justify-between">
              <div class="flex gap-3">
                <input type="date" id="paymentDateFrom" class="px-4 py-2 rounded-lg border-2 border-gray-200 focus:border-indigo-500 outline-none">
                <input type="date" id="paymentDateTo" class="px-4 py-2 rounded-lg border-2 border-gray-200 focus:border-indigo-500 outline-none">
              </div>
              <button onclick="loadPaymentHistory()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all">
                <i class="fas fa-filter mr-2"></i>Filter
              </button>
            </div>
            
            <div id="paymentHistoryList">
              <div class="text-center py-20">
                <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                <p class="text-gray-600">Loading payment history...</p>
              </div>
            </div>
          </div>

          <!-- Settings Tab -->
          <div id="content-settings" class="tab-content hidden">
            <form id="settingsForm" class="max-w-2xl space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-money-bill-wave text-indigo-600 mr-1"></i>
                  Fine Per Day (Riel)
                </label>
                <input type="number" name="fine_per_day" step="0.01" value="<?= $fine_per_day ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-clock text-indigo-600 mr-1"></i>
                  Grace Period (Days)
                </label>
                <input type="number" name="grace_period_days" value="<?= $settings['grace_period_days'] ?? 0 ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-exclamation-triangle text-indigo-600 mr-1"></i>
                  Maximum Fine Per Book (Riel)
                </label>
                <input type="number" name="max_fine_amount" step="0.01" value="<?= $max_fine ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-book text-indigo-600 mr-1"></i>
                  Default Borrow Days
                </label>
                <input type="number" name="default_borrow_days" value="<?= $settings['default_borrow_days'] ?? 14 ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-users text-indigo-600 mr-1"></i>
                  Max Books Per Member
                </label>
                <input type="number" name="max_books_per_member" value="<?= $settings['max_books_per_member'] ?? 5 ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-ban text-indigo-600 mr-1"></i>
                  Fine Threshold for Suspension (Riel)
                </label>
                <input type="number" name="fine_threshold_for_suspension" step="0.01" value="<?= $settings['fine_threshold_for_suspension'] ?? 20.00 ?>"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              </div>

              <button type="submit" class="w-full btn-ios text-white py-3 rounded-xl font-semibold">
                <i class="fas fa-save mr-2"></i>Save Settings
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-20 right-8 bg-white rounded-lg shadow-2xl px-6 py-4 z-50 transform transition-all">
  <div class="flex items-center space-x-3">
    <div id="toastIcon" class="w-10 h-10 rounded-full flex items-center justify-center"></div>
    <div>
      <h4 id="toastTitle" class="font-semibold text-gray-800"></h4>
      <p id="toastMessage" class="text-sm text-gray-600"></p>
    </div>
  </div>
</div>

<script>
// Tab Switching
function switchTab(tabName) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
  document.querySelectorAll('.tab-button').forEach(btn => {
    btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
    btn.classList.add('border-transparent');
  });
  
  // Show selected tab
  document.getElementById(`content-${tabName}`).classList.remove('hidden');
  const activeBtn = document.getElementById(`tab-${tabName}`);
  activeBtn.classList.add('active', 'border-blue-600', 'text-blue-600');
  activeBtn.classList.remove('border-transparent');
  
  // Load data for the tab
  if (tabName === 'overdue') {
    loadOverdueBooks();
  } else if (tabName === 'payments') {
    loadPaymentHistory();
  }
}

// Load Overdue Books
function loadOverdueBooks() {
  const search = document.getElementById('overdueSearch').value;
  
  fetch(`get_overdue_books.php?search=${encodeURIComponent(search)}`)
    .then(r => r.text())
    .then(html => document.getElementById('overdueBooksList').innerHTML = html);
}

// Load Payment History
function loadPaymentHistory() {
  const dateFrom = document.getElementById('paymentDateFrom').value;
  const dateTo = document.getElementById('paymentDateTo').value;
  
  fetch(`get_payment_history.php?from=${dateFrom}&to=${dateTo}`)
    .then(r => r.text())
    .then(html => document.getElementById('paymentHistoryList').innerHTML = html);
}

// Search on type
document.getElementById('overdueSearch').addEventListener('input', function() {
  clearTimeout(window.searchTimeout);
  window.searchTimeout = setTimeout(loadOverdueBooks, 300);
});

// Settings Form Submit
document.getElementById('settingsForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  fetch('update_settings.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Settings Updated!',
        text: data.message,
        confirmButtonText: 'OK'
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: data.message,
        confirmButtonText: 'OK'
      });
    }
  });
});

// Show Toast
function showToast(type, title, message) {
  const toast = document.getElementById('toast');
  const icon = document.getElementById('toastIcon');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');
  
  const types = {
    success: { bg: 'bg-green-500', icon: 'fa-check' },
    error: { bg: 'bg-red-500', icon: 'fa-times' },
    info: { bg: 'bg-blue-500', icon: 'fa-info' }
  };
  
  const config = types[type] || types.info;
  icon.className = `w-10 h-10 rounded-full flex items-center justify-center ${config.bg}`;
  icon.innerHTML = `<i class="fas ${config.icon} text-white"></i>`;
  toastTitle.textContent = title;
  toastMessage.textContent = message;
  
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Initialize dates for payment filter
const today = new Date();
const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
document.getElementById('paymentDateFrom').value = firstDay.toISOString().split('T')[0];
document.getElementById('paymentDateTo').value = today.toISOString().split('T')[0];

// Load initial data
loadOverdueBooks();
</script>

<?php include '../includes/footer.php'; ?>
