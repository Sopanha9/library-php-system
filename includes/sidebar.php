<aside class="w-72 glass min-h-screen shadow-glass-lg fixed left-0 top-0 overflow-y-auto border-r border-white/30">
  <!-- Logo Section -->
  <div class="p-6 border-b border-gray-200/50">
    <div class="flex items-center space-x-3">
      <div class="w-12 h-12 glass-blue rounded-2xl flex items-center justify-center shadow-glass">
        <i class="fas fa-book-open text-2xl text-gradient"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900">Library</h1>
        <p class="text-xs text-gray-600">Management System</p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="p-4 space-y-2">
    <?php
    // Get the base path for the application
    $base_path = '/library-php-system';
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    
    // Determine dashboard URL based on user role
    $role = $_SESSION['role'] ?? 'member';
    $dashboard_url = $base_path . '/' . $role . '/dashboard.php';
    
    $menu_items = [
      ['icon' => 'fa-grid-2', 'label' => 'Dashboard', 'url' => $dashboard_url, 'page' => 'dashboard.php'],
      ['icon' => 'fa-book', 'label' => 'Manage Books', 'url' => $base_path . '/books/manage.php', 'page' => 'manage.php', 'dir' => 'books'],
      ['icon' => 'fa-users', 'label' => 'Manage Members', 'url' => $base_path . '/members/manage.php', 'page' => 'manage.php', 'dir' => 'members'],
      ['icon' => 'fa-arrow-right-arrow-left', 'label' => 'Issue Book', 'url' => $base_path . '/circulation/issue_book.php', 'page' => 'issue_book.php', 'dir' => 'circulation'],
      ['icon' => 'fa-rotate-left', 'label' => 'Return Book', 'url' => $base_path . '/circulation/return_book.php', 'page' => 'return_book.php', 'dir' => 'circulation'],
      ['icon' => 'fa-triangle-exclamation', 'label' => 'Overdue & Fines', 'url' => $base_path . '/circulation/overdue.php', 'page' => 'overdue.php', 'dir' => 'circulation'],
    ];
    
    foreach ($menu_items as $item):
      // Check if current page matches
      $is_active = false;
      if (isset($item['dir'])) {
        $is_active = ($current_dir === $item['dir'] && $current_page === $item['page']);
      } else {
        $is_active = ($current_page === $item['page']);
      }
    ?>
    <a href="<?= $item['url'] ?>" class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 <?= $is_active ? 'glass-blue shadow-glass' : 'hover:glass-blue' ?>">
      <i class="fas <?= $item['icon'] ?> <?= $is_active ? 'text-blue-600' : 'text-gray-700 group-hover:text-blue-600' ?> w-5 transition-colors"></i>
      <span class="text-sm font-medium <?= $is_active ? 'text-gray-900' : 'text-gray-700 group-hover:text-gray-900' ?> transition-colors"><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>

    <div class="pt-4 mt-4 border-t border-gray-200/50">
      <a href="<?= $base_path ?>/auth/logout.php" class="group flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-red-50/50 transition-all duration-300">
        <i class="fas fa-right-from-bracket text-red-500 group-hover:text-red-600 w-5 transition-colors"></i>
        <span class="text-sm font-medium text-red-500 group-hover:text-red-600 transition-colors">Logout</span>
      </a>
    </div>
  </nav>

  <!-- User Profile -->
  <div class="absolute bottom-0 left-0 right-0 p-4 glass-blue border-t border-blue-200/30 backdrop-blur-xl">
    <div class="flex items-center space-x-3">
      <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-glass">
        <i class="fas fa-user text-white text-sm"></i>
      </div>
      <div class="flex-1 overflow-hidden">
        <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></p>
        <p class="text-xs text-gray-600 truncate"><?= ucfirst($_SESSION['role'] ?? 'guest') ?></p>
      </div>
    </div>
  </div>
</aside>