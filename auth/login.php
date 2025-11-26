<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library System - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
    .login-card { max-width: 400px; margin: 100px auto; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .login-header { background: #4e73df; color: white; padding: 20px; text-align: center; }
  </style>
</head>
<body>
<div class="container">
  <div class="card login-card">
    <div class="login-header">
      <h3>Library Management System</h3>
      <p>Sign in to continue</p>
    </div>
    <div class="card-body p-4">
      <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger">WHY? Invalid username or password!</div>
      <?php endif; ?>
      <?php if(isset($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Please login.</div>
      <?php endif; ?>

      <form action="process_login.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Login as</label>
          <select name="role" class="form-select" required>
            <option value="admin">Admin</option>pmyadmin/index.php?route=/database/structure&db=library_db
            <option value="librarian">Librarian</option>
            <option value="member">Member</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <div class="text-center mt-3">
        <small class="text-muted">
          Default Admin → username: <b>admin</b> | password: <b>admin123</b>
        </small>
      </div>
    </div>
  </div>
</div>
</body>
</html>