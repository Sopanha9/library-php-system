<nav class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-40">
  <div class="px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Search Bar -->
      <div class="flex-1 max-w-xl">
        <div class="relative">
          <input type="text" placeholder="Search books, members, or transactions..." 
                 class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none">
          <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center space-x-4 ml-6">
        <!-- Notifications -->
        <button class="relative p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
          <i class="fas fa-bell text-xl"></i>
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <!-- Settings -->
        <button class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
          <i class="fas fa-cog text-xl"></i>
        </button>

        <!-- User Menu -->
        <div class="flex items-center space-x-3 pl-4 border-l border-gray-200">
          <div class="text-right">
            <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></p>
            <p class="text-xs text-gray-500"><?= ucfirst($_SESSION['role'] ?? 'guest') ?></p>
          </div>
          <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
            <span class="text-white font-bold text-sm"><?= strtoupper(substr($_SESSION['username'] ?? 'G', 0, 1)) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
