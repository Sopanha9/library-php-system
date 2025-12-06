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
    
    * { font-family: 'Inter', sans-serif; }
    
    body {
      background: linear-gradient(135deg, #e8eef5 0%, #f5f7fa 50%, #dfe7f0 100%);
      position: relative;
      overflow: hidden;
    }
    
    /* Subtle animated background shapes */
    .bg-shape {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.15;
      animation: float 25s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translate(0, 0) scale(1); }
      33% { transform: translate(50px, -50px) scale(1.1); }
      66% { transform: translate(-50px, 50px) scale(0.9); }
    }
    
    /* Glassmorphism card */
    .glass-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(30px) saturate(180%);
      -webkit-backdrop-filter: blur(30px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.5);
      box-shadow: 0 20px 60px rgba(10, 132, 255, 0.15);
    }
    
    .glass-header {
      background: rgba(10, 132, 255, 0.08);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(10, 132, 255, 0.1);
    }
    
    .input-glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(10, 132, 255, 0.1);
      transition: all 0.3s ease;
    }
    
    .input-glass:focus {
      background: rgba(255, 255, 255, 0.9);
      border-color: rgba(10, 132, 255, 0.4);
      box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.1);
    }
    
    .btn-glass {
      background: linear-gradient(135deg, #0A84FF 0%, #5E5CE6 100%);
      box-shadow: 0 8px 20px rgba(10, 132, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .btn-glass:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(10, 132, 255, 0.4);
    }
    
    .info-glass {
      background: rgba(10, 132, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(10, 132, 255, 0.15);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
  
  <!-- Subtle Background Shapes -->
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="bg-shape w-96 h-96 bg-blue-400 top-0 -left-20" style="animation-delay: 0s;"></div>
    <div class="bg-shape w-80 h-80 bg-indigo-400 bottom-0 -right-20" style="animation-delay: 8s;"></div>
    <div class="bg-shape w-72 h-72 bg-purple-300 top-1/3 left-1/2" style="animation-delay: 16s;"></div>
  </div>

  <div class="relative w-full max-w-md z-10">
    <!-- Logo Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-20 h-20 glass-card rounded-3xl mb-4 transform hover:scale-110 transition-transform duration-300">
        <i class="fas fa-book-open text-4xl bg-gradient-to-br from-blue-500 to-indigo-600 bg-clip-text text-transparent"></i>
      </div>
      <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">Library System</h1>
      <p class="text-gray-600 font-medium">Sign in to manage your library</p>
    </div>

    <!-- Login Card -->
    <div class="glass-card rounded-3xl overflow-hidden">
      <!-- Header -->
      <div class="glass-header px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
          <i class="fas fa-sign-in-alt mr-3 text-blue-600"></i>
          Welcome Back
        </h2>
        <p class="text-gray-600 text-sm mt-1">Enter your credentials to continue</p>
      </div>

      <!-- Body -->
      <div class="p-8">
        <?php if(isset($_GET['error'])): ?>
          <div class="mb-6 bg-red-50/80 backdrop-blur-sm border-l-4 border-red-500 rounded-xl p-4 flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
            <div>
              <h3 class="font-semibold text-red-800">Login Failed</h3>
              <p class="text-sm text-red-700">Invalid username, password, or role. Please try again.</p>
            </div>
          </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['registered'])): ?>
          <div class="mb-6 bg-green-50/80 backdrop-blur-sm border-l-4 border-green-500 rounded-xl p-4 flex items-start space-x-3">
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
              <i class="fas fa-user text-blue-500 mr-2"></i>Username
            </label>
            <input type="text" name="username" required autofocus
                   class="input-glass w-full px-4 py-3 rounded-xl outline-none"
                   placeholder="Enter your username">
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-lock text-blue-500 mr-2"></i>Password
            </label>
            <div class="relative">
              <input type="password" name="password" id="password" required
                     class="input-glass w-full px-4 py-3 rounded-xl outline-none pr-12"
                     placeholder="Enter your password">
              <button type="button" onclick="togglePassword()" 
                      class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                <i class="fas fa-eye" id="toggleIcon"></i>
              </button>
            </div>
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-user-tag text-blue-500 mr-2"></i>Login as
            </label>
            <select name="role" required
                    class="input-glass w-full px-4 py-3 rounded-xl outline-none cursor-pointer">
              <option value="admin">👑 Administrator</option>
              <option value="librarian">📚 Librarian</option>
              <option value="member">👤 Member</option>
            </select>
          </div>

          <!-- Submit Button -->
          <button type="submit" 
                  class="btn-glass w-full text-white font-semibold py-3.5 rounded-xl">
            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
          </button>
        </form>

        <!-- Default Credentials -->
        <div class="mt-6 p-4 info-glass rounded-xl">
          <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fas fa-info-circle mr-2 text-blue-500"></i>Default Test Credentials
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-600">Username:</span>
              <code class="ml-1 font-bold text-blue-600">admin</code>
            </div>
            <div>
              <span class="text-gray-600">Password:</span>
              <code class="ml-1 font-bold text-blue-600">password</code>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-gray-600 text-sm mt-6">
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