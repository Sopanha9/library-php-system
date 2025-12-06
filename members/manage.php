<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get stats
$total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$active_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'")->fetchColumn();
$new_this_month = $pdo->query("SELECT COUNT(*) FROM members WHERE MONTH(join_date) = MONTH(CURDATE()) AND YEAR(join_date) = YEAR(CURDATE())")->fetchColumn();
$suspended_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'suspended'")->fetchColumn();

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
            <i class="fas fa-users text-indigo-600 mr-3"></i>
            Member Management
          </h1>
          <p class="text-gray-600 mt-1">Manage your library members</p>
        </div>
        <button onclick="openSlideOver('add')" class="btn-ios text-white px-6 py-3 rounded-xl font-semibold">
          <i class="fas fa-user-plus mr-2"></i>Add New Member
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Members</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_members) ?></h3>
            </div>
            <div class="w-14 h-14 glass-blue rounded-xl flex items-center justify-center">
              <i class="fas fa-users text-2xl text-blue-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Active Members</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($active_members) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(34, 197, 94, 0.3);">
              <i class="fas fa-user-check text-2xl text-green-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">New This Month</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($new_this_month) ?></h3>
            </div>
            <div class="w-14 h-14 glass-blue rounded-xl flex items-center justify-center">
              <i class="fas fa-user-plus text-2xl text-blue-600"></i>
            </div>
          </div>
        </div>

        <div class="glass card-hover rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Suspended</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($suspended_members) ?></h3>
            </div>
            <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(239, 68, 68, 0.3);">
              <i class="fas fa-user-slash text-2xl text-red-600"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Filter Section -->
      <div class="glass rounded-2xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative md:col-span-2">
            <input type="text" id="search" placeholder="Search by name, email, or phone..." 
                   class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>
          <div>
            <select id="status_filter" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
          <div>
            <select id="membership_filter" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="">All Membership Types</option>
              <option value="standard">Standard</option>
              <option value="premium">Premium</option>
              <option value="student">Student</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Members Table -->
      <div class="glass rounded-2xl overflow-hidden">
        <div id="membersTable">
          <div class="flex items-center justify-center py-20">
            <div class="text-center">
              <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
              <p class="text-gray-600">Loading members...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Slide-Over Panel -->
<div id="slideOverBackdrop" class="hidden fixed inset-0 bg-black/50 z-40 transition-opacity" onclick="closeSlideOver()"></div>
<div id="slideOverPanel" class="hidden fixed top-0 right-0 h-full w-full md:w-[700px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 overflow-y-auto">
  <div class="sticky top-0 glass-blue px-6 py-4 z-10 border-b border-blue-200/30">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold flex items-center text-gray-900">
        <i class="fas fa-user mr-3 text-blue-600"></i>
        <span id="slideOverTitle">Add New Member</span>
      </h2>
      <button onclick="closeSlideOver()" class="w-10 h-10 rounded-lg hover:bg-white/50 transition-all">
        <i class="fas fa-times text-xl text-gray-700"></i>
      </button>
    </div>
  </div>

  <form id="memberForm" class="p-6 space-y-6" enctype="multipart/form-data">
    <input type="hidden" id="member_id" name="member_id">
    <input type="hidden" id="current_photo" name="current_photo">
    
    <!-- Profile Photo -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-camera text-indigo-600 mr-2"></i>
        Profile Photo
      </h3>
      <div class="flex items-start space-x-4">
        <div id="photoPreviewContainer" class="flex-shrink-0">
          <div id="photoPreview" class="w-32 h-32 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center overflow-hidden">
            <i class="fas fa-user text-white text-4xl"></i>
          </div>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-2">Upload Photo</label>
          <input type="file" name="profile_photo" id="profile_photo" accept="image/*" 
                 onchange="previewPhoto(event)"
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
          <p class="text-xs text-gray-500 mt-2">Recommended: Square image, Max 2MB</p>
        </div>
      </div>
    </div>
    
    <!-- Personal Information -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-id-card text-indigo-600 mr-2"></i>
        Personal Information
      </h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
          <input type="text" name="full_name" id="full_name" required 
                 class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                 placeholder="Enter full name">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input type="email" name="email" id="email" required 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="email@example.com">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
            <input type="tel" name="phone" id="phone" required 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="012345678">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
          <textarea name="address" id="address" rows="2"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                    placeholder="Street address, city"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
            <input type="text" name="id_number" id="id_number" 
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="ID number">
          </div>
        </div>
      </div>
    </div>

    <!-- Membership Details -->
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-crown text-indigo-600 mr-2"></i>
        Membership Details
      </h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Membership Type *</label>
          <select name="membership_type" id="membership_type" required 
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
            <option value="standard">📚 Standard</option>
            <option value="premium">⭐ Premium</option>
            <option value="student">🎓 Student</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
          <select name="status" id="status" required 
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
            <option value="active">✅ Active</option>
            <option value="inactive">⚪ Inactive</option>
            <option value="suspended">🚫 Suspended</option>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Max Books Allowed</label>
        <input type="number" name="max_books_allowed" id="max_books_allowed" min="1" max="20" value="5"
               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
        <textarea name="notes" id="notes" rows="2"
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                  placeholder="Additional notes..."></textarea>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 pt-4 border-t border-gray-200">
      <button type="submit" class="flex-1 btn-ios text-white py-3 rounded-xl font-semibold">
        <i class="fas fa-save mr-2"></i>Save Member
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

// Photo Preview
function previewPhoto(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('photoPreview');
  
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Member photo">`;
    };
    reader.readAsDataURL(file);
  }
}

// Reset Photo Preview
function resetPhotoPreview() {
  document.getElementById('photoPreview').innerHTML = '<i class="fas fa-user text-white text-4xl"></i>';
}

// Open Slide-Over
function openSlideOver(mode, memberId = null) {
  currentMode = mode;
  const panel = document.getElementById('slideOverPanel');
  const backdrop = document.getElementById('slideOverBackdrop');
  const title = document.getElementById('slideOverTitle');
  
  if (mode === 'add') {
    title.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Add New Member';
    document.getElementById('memberForm').reset();
    document.getElementById('member_id').value = '';
    document.getElementById('current_photo').value = '';
    resetPhotoPreview();
  } else if (mode === 'edit') {
    title.innerHTML = '<i class="fas fa-user-edit mr-2"></i>Edit Member';
    loadMemberData(memberId);
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
    resetPhotoPreview();
  }, 300);
}

// Load Member Data for Edit
function loadMemberData(memberId) {
  fetch(`get_member_details.php?id=${memberId}`)
    .then(r => r.json())
    .then(member => {
      document.getElementById('member_id').value = member.member_id;
      document.getElementById('full_name').value = member.full_name;
      document.getElementById('email').value = member.email;
      document.getElementById('phone').value = member.phone;
      document.getElementById('address').value = member.address || '';
      document.getElementById('date_of_birth').value = member.date_of_birth || '';
      document.getElementById('id_number').value = member.id_number || '';
      document.getElementById('membership_type').value = member.membership_type;
      document.getElementById('status').value = member.status;
      document.getElementById('max_books_allowed').value = member.max_books_allowed;
      document.getElementById('notes').value = member.notes || '';
      document.getElementById('current_photo').value = member.profile_photo || '';
      
      // Show current photo
      const preview = document.getElementById('photoPreview');
      if (member.profile_photo) {
        preview.innerHTML = `<img src="../uploads/members/${member.profile_photo}" class="w-full h-full object-cover" alt="Member photo">`;
      } else {
        resetPhotoPreview();
      }
    });
}

// Load Members
function loadMembers() {
  const search = document.getElementById('search').value;
  const status = document.getElementById('status_filter').value;
  const membership = document.getElementById('membership_filter').value;
  
  fetch(`get_members.php?search=${encodeURIComponent(search)}&status=${status}&membership=${membership}`)
    .then(r => r.text())
    .then(html => document.getElementById('membersTable').innerHTML = html);
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

// Edit Member
function editMember(memberId) {
  openSlideOver('edit', memberId);
}

// Delete Member
function deleteMember(memberId) {
  Swal.fire({
    title: 'Delete Member?',
    text: 'This action cannot be undone. All member data will be permanently removed.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('delete_member.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ member_id: memberId })
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
          loadMembers();
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

// Toggle Member Status
function toggleStatus(memberId, currentStatus) {
  const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
  const action = newStatus === 'suspended' ? 'suspend' : 'activate';
  
  Swal.fire({
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Member?`,
    text: `Are you sure you want to ${action} this member?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: `Yes, ${action}!`,
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('toggle_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ member_id: memberId, status: newStatus })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Toast.fire({
            icon: 'success',
            title: 'Status Updated!',
            text: data.message
          });
          loadMembers();
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
document.getElementById('memberForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const url = currentMode === 'add' ? 'add_member.php' : 'update_member.php';
  
  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    console.log('Response:', data);
    if (data.success) {
      Toast.fire({
        icon: 'success',
        title: currentMode === 'add' ? 'Member Added!' : 'Member Updated!',
        text: data.message
      });
      closeSlideOver();
      loadMembers();
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
    console.error('Error:', err);
    showToast('error', 'Error', 'Something went wrong!');
  });
});

// Search & Filter
document.getElementById('search').addEventListener('input', loadMembers);
document.getElementById('status_filter').addEventListener('change', loadMembers);
document.getElementById('membership_filter').addEventListener('change', loadMembers);

// Load members on page load
loadMembers();
</script>

<?php include '../includes/footer.php'; ?>
