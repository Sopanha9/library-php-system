<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library System - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .animate-float {
      animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    .glass-effect {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen flex items-center justify-center p-4">
  
  <!-- Animated Background Elements -->
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
    <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-pink-400/10 rounded-full blur-3xl animate-float" style="animation-delay: 4s;"></div>
  </div>

  <div class="relative w-full max-w-md">
    <!-- Logo Section -->
    <div class="text-center mb-8 animate-float">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
        <i class="fas fa-book-open text-4xl text-indigo-600"></i>
      </div>
      <h1 class="text-4xl font-bold text-white mb-2">Library System</h1>
      <p class="text-indigo-100">Sign in to manage your library</p>
    </div>

    <!-- Login Card -->
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
        <h2 class="text-2xl font-bold flex items-center">
          <i class="fas fa-sign-in-alt mr-3"></i>
          Welcome Back
        </h2>
        <p class="text-indigo-100 text-sm mt-1">Enter your credentials to continue</p>
      </div>

      <!-- Body -->
      <div class="p-8">
        <?php if(isset($_GET['error'])): ?>
          <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
            <div>
              <h3 class="font-semibold text-red-800">Login Failed</h3>
              <p class="text-sm text-red-700">Invalid username, password, or role. Please try again.</p>
            </div>
          </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['registered'])): ?>
          <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 flex items-start space-x-3">
            <i class="fas fa-check-circle text-green-500 text-xl mt-0.5"></i>
            <div>
              <h3 class="font-semibold text-green-800">Success!</h3>
              <p class="text-sm text-green-700">Registration successful! Please login with your credentials.</p>
            </div>
          </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST" class="space-y-5">
          <!-- Username -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-user text-indigo-500 mr-2"></i>Username
            </label>
            <input type="text" name="username" required autofocus
                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                   placeholder="Enter your username">
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-lock text-indigo-500 mr-2"></i>Password
            </label>
            <div class="relative">
              <input type="password" name="password" id="password" required
                     class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none"
                     placeholder="Enter your password">
              <button type="button" onclick="togglePassword()" 
                      class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-eye" id="toggleIcon"></i>
              </button>
            </div>
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-user-tag text-indigo-500 mr-2"></i>Login as
            </label>
            <select name="role" required
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
              <option value="admin">👑 Administrator</option>
              <option value="librarian">📚 Librarian</option>
              <option value="member">👤 Member</option>
            </select>
          </div>

          <!-- Submit Button -->
          <button type="submit" 
                  class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3.5 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
          </button>
        </form>

        <!-- Default Credentials -->
        <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-indigo-100">
          <p class="text-xs font-semibold text-indigo-900 mb-2 flex items-center">
            <i class="fas fa-info-circle mr-2"></i>Default Test Credentials
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-600">Username:</span>
              <code class="ml-1 font-bold text-indigo-700">admin</code>
            </div>
            <div>
              <span class="text-gray-600">Password:</span>
              <code class="ml-1 font-bold text-indigo-700">password</code>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-white/80 text-sm mt-6">
      <i class="fas fa-shield-alt mr-2"></i>
      Secure library management system © 2025
    </p>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>