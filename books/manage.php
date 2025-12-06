<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get categories
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get stats
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$available_books = $pdo->query("SELECT SUM(available_quantity) FROM books")->fetchColumn();
$damaged_books = $pdo->query("SELECT SUM(damaged_quantity) FROM books")->fetchColumn();
$lost_books = $pdo->query("SELECT SUM(lost_quantity) FROM books")->fetchColumn();

include '../includes/header.php';
?>

<div class="flex">
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-72">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="p-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-book text-indigo-600 mr-3"></i>
            Book Management
          </h1>
          <p class="text-gray-600 mt-1">Manage your library's book collection</p>
        </div>
        <button onclick="openSlideOver('add')" class="btn-ios text-white px-6 py-3 rounded-xl font-semibold">
          <i class="fas fa-plus mr-2"></i>Add New Book
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Books</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_books) ?></h3>
            </div>
            <div class="w-14 h-14 glass-blue rounded-xl flex items-center justify-center">
              <i class="fas fa-book text-2xl text-blue-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Available</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($available_books ?? 0) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(34, 197, 94, 0.3);">
              <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Damaged</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($damaged_books ?? 0) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(251, 146, 60, 0.15) 0%, rgba(249, 115, 22, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(251, 146, 60, 0.3);">
              <i class="fas fa-triangle-exclamation text-2xl text-orange-500"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Lost</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($lost_books ?? 0) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(239, 68, 68, 0.3);">
              <i class="fas fa-ban text-2xl text-red-600"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Filter Section -->
      <div class="glass rounded-2xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="relative md:col-span-2">
            <input type="text" id="search" placeholder="Search by title, author, or ISBN..." 
                   class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>
          <div>
            <select id="category_filter" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="">All Categories</option>
              <?php foreach ($cats as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Books Table -->
      <div class="glass rounded-2xl overflow-hidden">
        <div id="booksTable">
          <div class="flex items-center justify-center py-20">
            <div class="text-center">
              <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
              <p class="text-gray-600">Loading books...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Slide-Over Panel -->
<div id="slideOverBackdrop" class="hidden fixed inset-0 bg-black/50 z-40 transition-opacity" onclick="closeSlideOver()"></div>
<div id="slideOverPanel" class="hidden fixed top-0 right-0 h-full w-full md:w-[600px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 overflow-y-auto">
  <div class="sticky top-0 glass-blue px-6 py-4 z-10 border-b border-blue-200/30">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold flex items-center text-gray-900">
        <i class="fas fa-book mr-3 text-blue-600"></i>
        <span id="slideOverTitle">Add New Book</span>
      </h2>
      <button onclick="closeSlideOver()" class="w-10 h-10 rounded-lg hover:bg-white/50 transition-all">
        <i class="fas fa-times text-xl text-gray-700"></i>
      </button>
    </div>
  </div>

  <form id="bookForm" class="p-6 space-y-6" enctype="multipart/form-data">
    <input type="hidden" id="book_id" name="book_id">
    <input type="hidden" id="current_image" name="current_image">
    
    <!-- Book Cover Image -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-image text-indigo-600 mr-2"></i>
        Book Cover
      </h3>
      <div class="flex items-start space-x-4">
        <div id="imagePreviewContainer" class="flex-shrink-0">
          <div id="imagePreview" class="w-32 h-44 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center overflow-hidden">
            <i class="fas fa-book text-white text-4xl"></i>
          </div>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-2">Upload Book Cover</label>
          <input type="file" name="book_image" id="book_image" accept="image/*" 
                 onchange="previewImage(event)"
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
          <p class="text-xs text-gray-500 mt-2">Recommended: 300x400px, Max 2MB (JPG, PNG)</p>
        </div>
      </div>
    </div>
    
    <!-- Book Information -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
        Book Information
      </h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
          <input type="text" name="title" id="title" required 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="Enter book title">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Author *</label>
          <input type="text" name="author" id="author" required 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="Enter author name">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
            <input type="text" name="isbn" id="isbn" 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="978-XXX">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Published Year</label>
            <input type="number" name="published_year" id="published_year" 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="2024">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
            <select name="category_id" id="category_id" required 
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="">Select category</option>
              <?php foreach ($cats as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Publisher</label>
            <input type="text" name="publisher" id="publisher" 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="Publisher name">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Price (៛)</label>
          <input type="number" name="price" id="price" step="0.01" 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="0.00">
        </div>
      </div>
    </div>

    <!-- Inventory -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-boxes text-indigo-600 mr-2"></i>
        Inventory
      </h3>
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Total Copies *</label>
          <input type="number" name="total_quantity" id="total_quantity" required min="1" 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="0">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Damaged</label>
          <input type="number" name="damaged_quantity" id="damaged_quantity" min="0" value="0" 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="0">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Lost</label>
          <input type="number" name="lost_quantity" id="lost_quantity" min="0" value="0" 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="0">
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 pt-4 border-t border-gray-200">
      <button type="submit" class="flex-1 btn-ios text-white py-3 rounded-xl font-semibold">
        <i class="fas fa-save mr-2"></i>Save Book
      </button>
      <button type="button" onclick="closeSlideOver()" class="px-6 py-3 glass hover:bg-white/80 text-gray-700 rounded-xl font-semibold transition-all">
        Cancel
      </button>
    </div>
  </form>
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
let currentMode = 'add';

// Image Preview
function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('imagePreview');
  
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Book cover">`;
    };
    reader.readAsDataURL(file);
  }
}

// Reset Image Preview
function resetImagePreview() {
  document.getElementById('imagePreview').innerHTML = '<i class="fas fa-book text-white text-4xl"></i>';
}

// Open Slide-Over
function openSlideOver(mode, bookId = null) {
  currentMode = mode;
  const panel = document.getElementById('slideOverPanel');
  const backdrop = document.getElementById('slideOverBackdrop');
  const title = document.getElementById('slideOverTitle');
  
  if (mode === 'add') {
    title.innerHTML = '<i class="fas fa-plus mr-2"></i>Add New Book';
    document.getElementById('bookForm').reset();
    document.getElementById('book_id').value = '';
    document.getElementById('current_image').value = '';
    resetImagePreview();
  } else if (mode === 'edit') {
    title.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Book';
    loadBookData(bookId);
  }
  
  backdrop.classList.remove('hidden');
  panel.classList.remove('hidden');
  setTimeout(() => {
    panel.classList.remove('translate-x-full');
  }, 10);
}

// Close Slide-Over
function closeSlideOver() {
  const panel = document.getElementById('slideOverPanel');
  const backdrop = document.getElementById('slideOverBackdrop');
  
  panel.classList.add('translate-x-full');
  setTimeout(() => {
    panel.classList.add('hidden');
    backdrop.classList.add('hidden');
    resetImagePreview();
  }, 300);
}

// Load Book Data for Edit
function loadBookData(bookId) {
  fetch(`get_book_details.php?id=${bookId}`)
    .then(r => r.json())
    .then(book => {
      document.getElementById('book_id').value = book.book_id;
      document.getElementById('title').value = book.title;
      document.getElementById('author').value = book.author;
      document.getElementById('isbn').value = book.isbn || '';
      document.getElementById('category_id').value = book.category_id || '';
      document.getElementById('publisher').value = book.publisher || '';
      document.getElementById('price').value = book.price || '';
      document.getElementById('total_quantity').value = book.total_quantity;
      document.getElementById('damaged_quantity').value = book.damaged_quantity;
      document.getElementById('lost_quantity').value = book.lost_quantity;
      document.getElementById('published_year').value = book.published_year || '';
      document.getElementById('current_image').value = book.cover_image || '';
      
      // Show current image
      const preview = document.getElementById('imagePreview');
      if (book.cover_image) {
        preview.innerHTML = `<img src="../uploads/books/${book.cover_image}" class="w-full h-full object-cover" alt="Book cover">`;
      } else {
        resetImagePreview();
      }
    });
}

// Load Books
function loadBooks() {
  const search = document.getElementById('search').value;
  const cat = document.getElementById('category_filter').value;
  
  fetch(`get_books.php?search=${encodeURIComponent(search)}&category=${cat}`)
    .then(r => r.text())
    .then(html => document.getElementById('booksTable').innerHTML = html);
}

// Show Toast
function showToast(type, title, message) {
  const toast = document.getElementById('toast');
  const icon = document.getElementById('toastIcon');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');
  
  const types = {
    success: { bg: 'bg-green-100', iconBg: 'bg-green-500', icon: 'fa-check' },
    error: { bg: 'bg-red-100', iconBg: 'bg-red-500', icon: 'fa-times' },
    info: { bg: 'bg-blue-100', iconBg: 'bg-blue-500', icon: 'fa-info' }
  };
  
  const config = types[type] || types.info;
  icon.className = `w-10 h-10 rounded-full flex items-center justify-center ${config.iconBg}`;
  icon.innerHTML = `<i class="fas ${config.icon} text-white"></i>`;
  toastTitle.textContent = title;
  toastMessage.textContent = message;
  
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Edit Book
function editBook(bookId) {
  openSlideOver('edit', bookId);
}

// Delete Book
function deleteBook(bookId) {
  Swal.fire({
    title: 'Delete Book?',
    text: 'This action cannot be undone. The book will be permanently removed from the system.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('delete_book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ book_id: bookId })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: data.message,
            confirmButtonText: 'OK'
          });
          loadBooks();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
}

// Form Submit
document.getElementById('bookForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const url = currentMode === 'add' ? 'add_book.php' : 'update_book.php';
  
  // Debug: Log FormData contents
  console.log('Form submission:', {
    mode: currentMode,
    url: url,
    hasFile: formData.get('book_image') ? true : false,
    fileName: formData.get('book_image')?.name || 'No file'
  });
  
  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    // Debug: Log response
    console.log('Server response:', data);
    
    if (data.debug) {
      console.log('Upload debug info:', data.debug);
    }
    
    if (data.success) {
      Toast.fire({
        icon: 'success',
        title: currentMode === 'add' ? 'Book Added!' : 'Book Updated!',
        text: data.message
      });
      closeSlideOver();
      loadBooks();
      this.reset();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message,
        confirmButtonText: 'OK'
      });
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Something went wrong! Please try again.',
      confirmButtonText: 'OK'
    });
  });
});

// Search & Filter
document.getElementById('search').addEventListener('input', loadBooks);
document.getElementById('category_filter').addEventListener('change', loadBooks);

// Load books on page load
loadBooks();
</script>

<?php include '../includes/footer.php'; ?>