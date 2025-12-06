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

$default_borrow_days = $settings['default_borrow_days'] ?? 14;
$max_books = $settings['max_books_per_member'] ?? 5;

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
          <i class="fas fa-arrow-right-arrow-left text-indigo-600 mr-3"></i>
          Issue Book
        </h1>
        <p class="text-gray-600 mt-1">Issue books to library members</p>
      </div>

      <!-- Issue Book Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column - Form -->
        <div class="lg:col-span-2">
          <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
              <i class="fas fa-edit text-blue-600 mr-2"></i>
              Issue Details
            </h2>

            <form id="issueForm" class="space-y-6">
              
              <!-- Member Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-user text-indigo-600 mr-1"></i>
                  Select Member *
                </label>
                <div class="relative">
                  <input type="text" id="memberSearch" placeholder="Search member by name, email, or phone..." 
                         class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                         autocomplete="off">
                  <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  
                  <!-- Member Search Results -->
                  <div id="memberResults" class="hidden absolute z-10 w-full mt-2 bg-white rounded-lg shadow-xl border max-h-64 overflow-y-auto"></div>
                </div>
                
                <!-- Selected Member Display -->
                <div id="selectedMember" class="hidden mt-4 p-4 glass-blue rounded-xl border-2 border-blue-200/50">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <div id="memberPhoto" class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold overflow-hidden"></div>
                      <div>
                        <h4 id="memberName" class="font-semibold text-gray-800"></h4>
                        <p id="memberInfo" class="text-sm text-gray-600"></p>
                        <p id="memberBorrows" class="text-xs text-indigo-600 font-medium mt-1"></p>
                      </div>
                    </div>
                    <button type="button" onclick="clearMember()" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded-full transition-all">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <input type="hidden" id="member_id" name="member_id">
              </div>

              <!-- Book Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-book text-indigo-600 mr-1"></i>
                  Select Book *
                </label>
                <div class="relative">
                  <input type="text" id="bookSearch" placeholder="Search book by title, author, or ISBN..." 
                         class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                         autocomplete="off">
                  <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  
                  <!-- Book Search Results -->
                  <div id="bookResults" class="hidden absolute z-10 w-full mt-2 bg-white rounded-lg shadow-xl border max-h-64 overflow-y-auto"></div>
                </div>
                
                <!-- Selected Book Display -->
                <div id="selectedBook" class="hidden mt-4 p-4 glass rounded-xl border-2 border-blue-200/50">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <div id="bookCover" class="w-16 h-20 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white overflow-hidden">
                        <i class="fas fa-book text-2xl"></i>
                      </div>
                      <div>
                        <h4 id="bookTitle" class="font-semibold text-gray-800"></h4>
                        <p id="bookAuthor" class="text-sm text-gray-600"></p>
                        <p id="bookISBN" class="text-xs text-blue-600 font-medium mt-1"></p>
                      </div>
                    </div>
                    <button type="button" onclick="clearBook()" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded-full transition-all">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <input type="hidden" id="book_id" name="book_id">
              </div>

              <!-- Date Information -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar text-indigo-600 mr-1"></i>
                    Issue Date *
                  </label>
                  <input type="date" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>" required
                         class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-check text-indigo-600 mr-1"></i>
                    Due Date *
                  </label>
                  <input type="date" id="due_date" name="due_date" required
                         class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-note-sticky text-indigo-600 mr-1"></i>
                  Notes (Optional)
                </label>
                <textarea id="notes" name="notes" rows="3" 
                          class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                          placeholder="Any special notes..."></textarea>
              </div>

              <!-- Submit Button -->
              <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 btn-ios text-white py-3 rounded-xl font-semibold">
                  <i class="fas fa-check mr-2"></i>Issue Book
                </button>
                <button type="button" onclick="resetForm()" class="px-6 py-3 glass hover:bg-white/80 text-gray-700 rounded-xl font-semibold transition-all">
                  <i class="fas fa-redo mr-2"></i>Reset
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Right Column - Info & Recent Issues -->
        <div class="space-y-6">
          
          <!-- Info Card -->
          <div class="glass-blue rounded-2xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center text-gray-900">
              <i class="fas fa-info-circle mr-2 text-blue-600"></i>
              Library Rules
            </h3>
            <ul class="space-y-2 text-sm text-gray-700">
              <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2"></i>
                <span>Default borrow period: <strong><?= $default_borrow_days ?> days</strong></span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2"></i>
                <span>Max books per member: <strong><?= $max_books ?> books</strong></span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2"></i>
                <span>Fine: <strong><?= $settings['fine_per_day'] ?? '1.00' ?> Riel/day</strong></span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2"></i>
                <span>Max fine: <strong><?= $settings['max_fine_amount'] ?? '50.00' ?> Riel</strong></span>
              </li>
            </ul>
          </div>

          <!-- Recent Issues -->
          <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-clock text-blue-600 mr-2"></i>
              Recent Issues
            </h3>
            <div id="recentIssues" class="space-y-3">
              <div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p class="text-sm">No recent issues</p>
              </div>
            </div>
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
const defaultBorrowDays = <?= $default_borrow_days ?>;
let selectedMemberId = null;
let selectedBookId = null;
let searchTimeout = null;

// Auto-calculate due date
document.getElementById('issue_date').addEventListener('change', function() {
  const issueDate = new Date(this.value);
  const dueDate = new Date(issueDate);
  dueDate.setDate(dueDate.getDate() + defaultBorrowDays);
  document.getElementById('due_date').value = dueDate.toISOString().split('T')[0];
});

// Initialize due date
const today = new Date();
const defaultDueDate = new Date(today);
defaultDueDate.setDate(defaultDueDate.getDate() + defaultBorrowDays);
document.getElementById('due_date').value = defaultDueDate.toISOString().split('T')[0];

// Member Search
document.getElementById('memberSearch').addEventListener('input', function() {
  const query = this.value.trim();
  
  clearTimeout(searchTimeout);
  
  if (query.length < 2) {
    document.getElementById('memberResults').classList.add('hidden');
    return;
  }
  
  searchTimeout = setTimeout(() => {
    fetch(`search_members.php?q=${encodeURIComponent(query)}`)
      .then(r => r.json())
      .then(data => {
        const resultsDiv = document.getElementById('memberResults');
        
        if (data.length === 0) {
          resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">No members found</div>';
        } else {
          resultsDiv.innerHTML = data.map(member => `
            <div onclick='selectMember(${JSON.stringify(member)})' 
                 class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 transition-colors">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                  ${member.full_name.charAt(0)}
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800">${member.full_name}</h4>
                  <p class="text-xs text-gray-600">${member.email} • ${member.phone}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded ${member.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                      ${member.status}
                    </span>
                    <span class="text-xs text-gray-500">Books: ${member.current_borrows}/${member.max_books_allowed}</span>
                  </div>
                </div>
              </div>
            </div>
          `).join('');
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
    fetch(`search_books.php?q=${encodeURIComponent(query)}`)
      .then(r => r.json())
      .then(data => {
        const resultsDiv = document.getElementById('bookResults');
        
        if (data.length === 0) {
          resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">No available books found</div>';
        } else {
          resultsDiv.innerHTML = data.map(book => `
            <div onclick='selectBook(${JSON.stringify(book)})' 
                 class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 transition-colors">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-16 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs overflow-hidden">
                  ${book.cover_image ? `<img src="../uploads/books/${book.cover_image}" class="w-full h-full object-cover">` : '<i class="fas fa-book"></i>'}
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800">${book.title}</h4>
                  <p class="text-xs text-gray-600">${book.author}</p>
                  <p class="text-xs text-blue-600 mt-1">ISBN: ${book.isbn} • Available: ${book.quantity}</p>
                </div>
              </div>
            </div>
          `).join('');
        }
        
        resultsDiv.classList.remove('hidden');
      });
  }, 300);
});

// Select Member
function selectMember(member) {
  if (member.status !== 'active') {
    showToast('error', 'Cannot Issue', 'This member is not active');
    return;
  }
  
  if (member.current_borrows >= member.max_books_allowed) {
    showToast('error', 'Limit Reached', `Member has reached maximum borrow limit (${member.max_books_allowed} books)`);
    return;
  }
  
  selectedMemberId = member.member_id;
  document.getElementById('member_id').value = member.member_id;
  document.getElementById('memberSearch').value = '';
  document.getElementById('memberResults').classList.add('hidden');
  
  document.getElementById('memberPhoto').textContent = member.full_name.charAt(0);
  document.getElementById('memberName').textContent = member.full_name;
  document.getElementById('memberInfo').textContent = `${member.email} • ${member.phone}`;
  document.getElementById('memberBorrows').textContent = `Currently borrowed: ${member.current_borrows}/${member.max_books_allowed} books`;
  
  document.getElementById('selectedMember').classList.remove('hidden');
}

// Select Book
function selectBook(book) {
  if (book.quantity < 1) {
    showToast('error', 'Not Available', 'This book is currently not available');
    return;
  }
  
  selectedBookId = book.book_id;
  document.getElementById('book_id').value = book.book_id;
  document.getElementById('bookSearch').value = '';
  document.getElementById('bookResults').classList.add('hidden');
  
  if (book.cover_image) {
    document.getElementById('bookCover').innerHTML = `<img src="../uploads/books/${book.cover_image}" class="w-full h-full object-cover">`;
  }
  document.getElementById('bookTitle').textContent = book.title;
  document.getElementById('bookAuthor').textContent = book.author;
  document.getElementById('bookISBN').textContent = `ISBN: ${book.isbn}`;
  
  document.getElementById('selectedBook').classList.remove('hidden');
}

// Clear selections
function clearMember() {
  selectedMemberId = null;
  document.getElementById('member_id').value = '';
  document.getElementById('selectedMember').classList.add('hidden');
}

function clearBook() {
  selectedBookId = null;
  document.getElementById('book_id').value = '';
  document.getElementById('selectedBook').classList.add('hidden');
}

// Form Submit
document.getElementById('issueForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  if (!selectedMemberId || !selectedBookId) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Information',
      text: 'Please select both member and book before issuing.',
      confirmButtonText: 'OK'
    });
    return;
  }
  
  const formData = new FormData(this);
  formData.append('issued_by', <?= $_SESSION['user_id'] ?>);
  
  fetch('process_issue.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Book Issued Successfully!',
        text: data.message,
        confirmButtonText: 'Great!'
      }).then(() => {
        resetForm();
        loadRecentIssues();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Issue Failed',
        text: data.message,
        confirmButtonText: 'OK'
      });
    }
  });
});

// Reset Form
function resetForm() {
  document.getElementById('issueForm').reset();
  clearMember();
  clearBook();
  const today = new Date();
  const defaultDueDate = new Date(today);
  defaultDueDate.setDate(defaultDueDate.getDate() + defaultBorrowDays);
  document.getElementById('issue_date').value = today.toISOString().split('T')[0];
  document.getElementById('due_date').value = defaultDueDate.toISOString().split('T')[0];
}

// Load Recent Issues
function loadRecentIssues() {
  fetch('get_recent_issues.php')
    .then(r => r.text())
    .then(html => document.getElementById('recentIssues').innerHTML = html);
}

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

// Load recent issues on page load
loadRecentIssues();
</script>

<?php include '../includes/footer.php'; ?>
