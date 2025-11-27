<aside class="w-64 bg-gradient-to-b from-indigo-900 to-purple-900 text-white min-h-screen fixed top-0 left-0 flex flex-col shadow-2xl z-50">
  <div class="p-6 border-b border-indigo-800">
    <h1 class="text-2xl font-bold flex items-center gap-3">
      <i class="fas fa-book-open text-indigo-300 text-3xl"></i>
      Library System
    </h1>
  </div>

  <nav class="flex-1 px-4 py-6 space-y-2">
    <a href="<?= url('admin/dashboard.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-800' ?>">
      <i class="fas fa-tachometer-alt text-xl"></i>
      <span class="font-medium">Dashboard</span>
    </a>

    <a href="<?= url('books/manage.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition hover:bg-indigo-800">
      <i class="fas fa-book text-xl"></i>
      <span class="font-medium">Manage Books</span>
    </a>

    <a href="<?= url('members/manage.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition hover:bg-indigo-800">
      <i class="fas fa-users text-xl"></i>
      <span class="font-medium">Manage Members</span>
    </a>

    <a href="<?= url('issue/issue_book.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition hover:bg-indigo-800">
      <i class="fas fa-exchange-alt text-xl"></i>
      <span class="font-medium">Issue Book</span>
    </a>

    <a href="<?= url('issue/return_book.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition hover:bg-indigo-800">
      <i class="fas fa-undo text-xl"></i>
      <span class="font-medium">Return Book</span>
    </a>

    <a href="<?= url('reports/overdue.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg transition hover:bg-indigo-800">
      <i class="fas fa-exclamation-triangle text-xl"></i>
      <span class="font-medium">Overdue & Fines</span>
    </a>
  </nav>

  <div class="p-4 border-t border-indigo-800">
    <a href="<?= url('auth/logout.php') ?>" 
       class="flex items-center gap-4 px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition font-medium">
      <i class="fas fa-sign-out-alt"></i>
      Logout
    </a>
  </div>
</aside>