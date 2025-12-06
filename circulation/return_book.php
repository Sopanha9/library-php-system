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
$grace_period = $settings['grace_period_days'] ?? 0;
$max_fine = $settings['max_fine_amount'] ?? 50.00;

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
          <i class="fas fa-rotate-left text-indigo-600 mr-3"></i>
          Return Book
        </h1>
        <p class="text-gray-600 mt-1">Process book returns and calculate fines</p>
      </div>

      <!-- Search Section -->
      <div class="glass rounded-2xl p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-search text-blue-600 mr-2"></i>
          Search Borrowed Books
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search by Member -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-user text-indigo-600 mr-1"></i>
              Search by Member
            </label>
            <div class="relative">
              <input type="text" id="memberSearch" placeholder="Enter member name, email, or phone..." 
                     class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                     autocomplete="off">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              
              <!-- Member Search Results -->
              <div id="memberResults" class="hidden absolute z-10 w-full mt-2 bg-white rounded-lg shadow-xl border max-h-64 overflow-y-auto"></div>
            </div>
          </div>

          <!-- Search by Book -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-book text-indigo-600 mr-1"></i>
              Search by Book
            </label>
            <div class="relative">
              <input type="text" id="bookSearch" placeholder="Enter book title or ISBN..." 
                     class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                     autocomplete="off">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              
              <!-- Book Search Results -->
              <div id="bookResults" class="hidden absolute z-10 w-full mt-2 bg-white rounded-lg shadow-xl border max-h-64 overflow-y-auto"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Borrows Display -->
      <div id="activeBorrowsSection" class="hidden">
        <div class="glass-blue rounded-2xl border-2 border-blue-200/50 p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div id="selectedMemberPhoto" class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold"></div>
              <div>
                <h3 id="selectedMemberName" class="text-lg font-bold text-gray-800"></h3>
                <p id="selectedMemberInfo" class="text-sm text-gray-600"></p>
              </div>
            </div>
            <button onclick="clearSearch()" class="text-gray-600 hover:bg-white/50 px-4 py-2 rounded-lg transition-all">
              <i class="fas fa-times mr-2"></i>Clear
            </button>
          </div>
        </div>

        <!-- Borrowed Books List -->
        <div class="glass rounded-2xl overflow-hidden">
          <div class="glass-blue px-6 py-4 border-b border-blue-200/30">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
              <i class="fas fa-list mr-2 text-blue-600"></i>
              Active Borrows
            </h3>
          </div>
          <div id="borrowedBooksList" class="p-6">
            <div class="text-center py-8 text-gray-400">
              <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
              <p>Loading borrowed books...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Fine Calculation Info -->
      <div class="mt-6 glass rounded-2xl p-6 border-2 border-orange-200/50">
        <h3 class="text-lg font-bold mb-4 flex items-center text-gray-900">
          <i class="fas fa-calculator mr-2 text-orange-500"></i>
          Fine Calculation Rules
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
          <div class="flex items-center">
            <i class="fas fa-money-bill-wave text-2xl mr-3 opacity-80"></i>
            <div>
              <p class="font-semibold">Fine Per Day</p>
              <p class="text-black-100"><?= number_format($fine_per_day, 2) ?> Riel</p>
            </div>
          </div>
          <div class="flex items-center">
            <i class="fas fa-clock text-2xl mr-3 opacity-80"></i>
            <div>
              <p class="font-semibold">Grace Period</p>
              <p class="text-black-100"><?= $grace_period ?> days</p>
            </div>
          </div>
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-2xl mr-3 opacity-80"></i>
            <div>
              <p class="font-semibold">Maximum Fine</p>
              <p class="text-black-100"><?= number_format($max_fine, 2) ?> Riel</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Return Modal -->
<div id="returnModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="glass rounded-2xl shadow-glass-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
    <div class="sticky top-0 glass-blue px-6 py-4 z-10 border-b border-blue-200/30">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold flex items-center text-gray-900">
          <i class="fas fa-rotate-left mr-3 text-blue-600"></i>
          Process Return
        </h2>
        <button onclick="closeReturnModal()" class="w-10 h-10 rounded-lg hover:bg-white/50 transition-all">
          <i class="fas fa-times text-xl text-gray-700"></i>
        </button>
      </div>
    </div>

    <form id="returnForm" class="p-6 space-y-6">
      <input type="hidden" id="return_issue_id" name="issue_id">
      
      <!-- Book Info -->
      <div class="glass-blue rounded-xl p-4 border-2 border-blue-200/50">
        <div class="flex items-center space-x-4">
          <div id="returnBookCover" class="w-16 h-20 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white overflow-hidden">
            <i class="fas fa-book text-2xl"></i>
          </div>
          <div class="flex-1">
            <h4 id="returnBookTitle" class="font-bold text-gray-800"></h4>
            <p id="returnBookAuthor" class="text-sm text-gray-600"></p>
            <div class="flex items-center gap-3 mt-2 text-xs">
              <span id="returnIssueDate" class="text-gray-600"></span>
              <span id="returnDueDate" class="font-semibold"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Fine Information -->
      <div id="fineSection" class="hidden glass rounded-xl p-4 border-2 border-orange-300/50">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-gray-800 flex items-center">
            <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
            Overdue Fine
          </h3>
          <span id="daysOverdue" class="px-3 py-1 bg-red-500 text-white rounded-full text-sm font-bold"></span>
        </div>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Fine per day:</span>
            <span class="font-semibold"><?= number_format($fine_per_day, 2) ?> Riel</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Days overdue:</span>
            <span id="overdueDaysCalc" class="font-semibold"></span>
          </div>
          <div class="border-t-2 border-orange-300 pt-2 flex justify-between">
            <span class="font-bold text-gray-800">Total Fine:</span>
            <span id="totalFine" class="text-xl font-bold text-red-600"></span>
          </div>
        </div>
      </div>

      <div id="onTimeSection" class="hidden glass rounded-xl p-4 border-2 border-green-300/50">
        <div class="flex items-center text-green-700">
          <i class="fas fa-check-circle text-2xl mr-3"></i>
          <div>
            <h3 class="font-bold">Returned On Time</h3>
            <p class="text-sm">No fine applicable</p>
          </div>
        </div>
      </div>

      <!-- Return Details -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-calendar text-indigo-600 mr-1"></i>
            Return Date *
          </label>
          <input type="date" id="return_date" name="return_date" value="<?= date('Y-m-d') ?>" required
                 onchange="calculateFine()"
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-star text-indigo-600 mr-1"></i>
            Book Condition *
          </label>
          <select id="book_condition" name="book_condition" required
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
            <option value="Good">✅ Good - No damage</option>
            <option value="Damaged">⚠️ Damaged - Minor damage</option>
            <option value="Lost">❌ Lost - Book lost</option>
          </select>
        </div>

        <!-- Payment Section (shows if fine > 0) -->
        <div id="paymentSection" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-money-bill-wave text-indigo-600 mr-1"></i>
            Payment Amount (Riel) *
          </label>
          <input type="number" id="payment_amount" name="payment_amount" min="0" step="0.01" value="0"
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
          
          <div class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
            <select id="payment_method" name="payment_method"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="cash">💵 Cash</option>
              <option value="card">💳 Card</option>
              <option value="online">🌐 Online Transfer</option>
              <option value="other">📝 Other</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-note-sticky text-indigo-600 mr-1"></i>
            Notes (Optional)
          </label>
          <textarea id="return_notes" name="notes" rows="2"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                    placeholder="Any additional notes..."></textarea>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex gap-3 pt-4 border-t border-gray-200">
        <button type="submit" class="flex-1 btn-ios text-white py-3 rounded-xl font-semibold">
          <i class="fas fa-check mr-2"></i>Process Return
        </button>
        <button type="button" onclick="closeReturnModal()" class="px-6 py-3 glass hover:bg-white/80 text-gray-700 rounded-xl font-semibold transition-all">
          Cancel
        </button>
      </div>
    </form>
  </div>
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
const finePerDay = <?= $fine_per_day ?>;
const gracePeriod = <?= $grace_period ?>;
const maxFine = <?= $max_fine ?>;

let currentIssueData = null;
let searchTimeout = null;

// Member Search
document.getElementById('memberSearch').addEventListener('input', function() {
  const query = this.value.trim();
  
  clearTimeout(searchTimeout);
  
  if (query.length < 2) {
    document.getElementById('memberResults').classList.add('hidden');
    return;
  }
  
  searchTimeout = setTimeout(() => {
    fetch(`search_borrowed_members.php?q=${encodeURIComponent(query)}`)
      .then(r => r.json())
      .then(data => {
        const resultsDiv = document.getElementById('memberResults');
        
        if (data.length === 0) {
          resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">No members with borrowed books found</div>';
        } else {
          resultsDiv.innerHTML = data.map(member => {
            const photoHtml = member.profile_photo 
              ? `<img src="../uploads/members/${member.profile_photo}" class="w-full h-full object-cover" alt="${member.full_name}">`
              : `<div class="w-full h-full flex items-center justify-center font-semibold">${member.full_name.charAt(0)}</div>`;
            
            return `
              <div onclick='loadMemberBorrows(${member.member_id}, "${member.full_name.replace(/'/g, "\\'")}",  "${member.email}", "${member.phone}", "${member.profile_photo || ""}")' 
                   class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 transition-colors">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 overflow-hidden flex items-center justify-center text-white text-sm">
                      ${photoHtml}
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-800">${member.full_name}</h4>
                      <p class="text-xs text-gray-600">${member.email}</p>
                    </div>
                  </div>
                  <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs font-semibold">
                    ${member.borrowed_count} book(s)
                  </span>
                </div>
              </div>
            `;
          }).join('');
        }
        
        resultsDiv.classList.remove('hidden');
      });
  }, 300);
});

// Book Search
document.getElementById('bookSearch').addEventListener('input', function() {
  const query = this.value.trim();
  
  clearTimeout(searchTimeout);
  
  if (query.length < 2) {
    document.getElementById('bookResults').classList.add('hidden');
    return;
  }
  
  searchTimeout = setTimeout(() => {
    fetch(`search_borrowed_books.php?q=${encodeURIComponent(query)}`)
      .then(r => r.json())
      .then(data => {
        const resultsDiv = document.getElementById('bookResults');
        
        if (data.length === 0) {
          resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">No borrowed books found</div>';
        } else {
          resultsDiv.innerHTML = data.map(borrow => `
            <div onclick='loadMemberBorrows(${borrow.member_id}, "${borrow.member_name.replace(/'/g, "\\'")}",  "${borrow.member_email}", "${borrow.member_phone}")' 
                 class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 transition-colors">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-16 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs">
                  <i class="fas fa-book"></i>
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800">${borrow.book_title}</h4>
                  <p class="text-xs text-gray-600">Borrowed by: ${borrow.member_name}</p>
                  <p class="text-xs text-red-600 font-medium mt-1">Due: ${borrow.due_date}</p>
                </div>
              </div>
            </div>
          `).join('');
        }
        
        resultsDiv.classList.remove('hidden');
      });
  }, 300);
});

// Load Member Borrows
function loadMemberBorrows(memberId, name, email, phone, profilePhoto = '') {
  document.getElementById('memberSearch').value = '';
  document.getElementById('bookSearch').value = '';
  document.getElementById('memberResults').classList.add('hidden');
  document.getElementById('bookResults').classList.add('hidden');
  
  const photoDiv = document.getElementById('selectedMemberPhoto');
  if (profilePhoto) {
    photoDiv.innerHTML = `<img src="../uploads/members/${profilePhoto}" class="w-full h-full object-cover rounded-full" alt="${name}">`;
  } else {
    photoDiv.innerHTML = name.charAt(0);
  }
  
  document.getElementById('selectedMemberName').textContent = name;
  document.getElementById('selectedMemberInfo').textContent = `${email} • ${phone}`;
  
  document.getElementById('activeBorrowsSection').classList.remove('hidden');
  
  fetch(`get_member_borrows.php?member_id=${memberId}`)
    .then(r => r.text())
    .then(html => document.getElementById('borrowedBooksList').innerHTML = html);
}

// Clear Search
function clearSearch() {
  document.getElementById('memberSearch').value = '';
  document.getElementById('bookSearch').value = '';
  document.getElementById('activeBorrowsSection').classList.add('hidden');
}

// Open Return Modal
function openReturnModal(issueData) {
  currentIssueData = issueData;
  
  document.getElementById('return_issue_id').value = issueData.issue_id;
  document.getElementById('returnBookTitle').textContent = issueData.book_title;
  document.getElementById('returnBookAuthor').textContent = issueData.book_author;
  document.getElementById('returnIssueDate').textContent = `Issued: ${issueData.issue_date}`;
  document.getElementById('returnDueDate').textContent = `Due: ${issueData.due_date}`;
  
  if (issueData.cover_image) {
    document.getElementById('returnBookCover').innerHTML = `<img src="../uploads/books/${issueData.cover_image}" class="w-full h-full object-cover">`;
  }
  
  calculateFine();
  document.getElementById('returnModal').classList.remove('hidden');
}

// Close Return Modal
function closeReturnModal() {
  document.getElementById('returnModal').classList.add('hidden');
  document.getElementById('returnForm').reset();
  currentIssueData = null;
}

// Calculate Fine
function calculateFine() {
  if (!currentIssueData) return;
  
  const returnDate = new Date(document.getElementById('return_date').value);
  const dueDate = new Date(currentIssueData.due_date);
  
  const diffTime = returnDate - dueDate;
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays > gracePeriod) {
    const overdueDays = diffDays - gracePeriod;
    let fine = overdueDays * finePerDay;
    fine = Math.min(fine, maxFine); // Cap at max fine
    
    document.getElementById('fineSection').classList.remove('hidden');
    document.getElementById('onTimeSection').classList.add('hidden');
    document.getElementById('paymentSection').classList.remove('hidden');
    
    document.getElementById('daysOverdue').textContent = `${overdueDays} days overdue`;
    document.getElementById('overdueDaysCalc').textContent = `${overdueDays} days`;
    document.getElementById('totalFine').textContent = `${fine.toFixed(2)} Riel`;
    document.getElementById('payment_amount').value = fine.toFixed(2);
  } else {
    document.getElementById('fineSection').classList.add('hidden');
    document.getElementById('onTimeSection').classList.remove('hidden');
    document.getElementById('paymentSection').classList.add('hidden');
    document.getElementById('payment_amount').value = '0';
  }
}

// Form Submit
document.getElementById('returnForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  formData.append('returned_by', <?= $_SESSION['user_id'] ?>);
  
  fetch('process_return.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const fineAmount = data.fine_amount || 0;
      const paidAmount = data.fine_paid || 0;
      
      let message = data.message;
      if (fineAmount > 0) {
        message += `\\n\\nFine: ${fineAmount.toFixed(2)} Riel`;
        if (paidAmount > 0) {
          message += `\\nPaid: ${paidAmount.toFixed(2)} Riel`;
        }
      }
      
      Swal.fire({
        icon: 'success',
        title: 'Return Processed!',
        text: message,
        confirmButtonText: 'Great!'
      }).then(() => {
        closeReturnModal();
        // Reload the borrows list
        if (currentIssueData) {
          loadMemberBorrows(
            currentIssueData.member_id,
            currentIssueData.member_name,
            currentIssueData.member_email,
            currentIssueData.member_phone
          );
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Return Failed',
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

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) {
    document.getElementById('memberResults').classList.add('hidden');
  }
  if (!e.target.closest('#bookSearch') && !e.target.closest('#bookResults')) {
    document.getElementById('bookResults').classList.add('hidden');
  }
});
</script>

<?php include '../includes/footer.php'; ?>
