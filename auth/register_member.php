<?php
session_start();
require_once '../config/db.php';

// Only admin or librarian can register members
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register New Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { max-width: 600px; margin: 50px auto; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h4>Register New Member</h4>
        </div>
        <div class="card-body">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $full_name   = trim($_POST['full_name']);
                $gender      = $_POST['gender'];
                $phone       = trim($_POST['phone']);
                $email       = trim($_POST['email']);
                $address     = trim($_POST['address']);
                $expiry_date = $_POST['expiry_date'];

                // Auto generate username = first part of name + random 4 digits
                $username = strtolower(str_replace(' ', '', explode(' ', $full_name)[0])) . rand(1000, 9999);
                $raw_pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8); // random 8-char password
                $password_hash = password_hash($raw_pass, PASSWORD_DEFAULT);

                try {
                    $pdo->beginTransaction();

                    // 1. Insert into members
                    $stmt = $pdo->prepare("INSERT INTO members (full_name, gender, phone, email, address, expiry_date) 
                                          VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $gender, $phone, $email, $address, $expiry_date]);
                    $member_id = $pdo->lastInsertId();

                    // 2. Insert into users (create login account)
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, member_id) 
                                          VALUES (?, ?, 'member', ?)");
                    $stmt->execute([$username, $password_hash, $member_id]);

                    $pdo->commit();

                    // Success → show credentials
                    echo "<div class='alert alert-success text-center'>
                            <h5>Member Registered Successfully!</h5>
                            <p><strong>Full Name:</strong> $full_name</p>
                            <p><strong>Username:</strong> <code>$username</code></p>
                            <p><strong>Password:</strong> <code>$raw_pass</code></p>
                            <hr>
                            <small>Give this username & password to the member so they can login</small>
                          </div>";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
            }
            ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Membership Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Register Member</button>
                    <a href="../admin/dashboard.php" class="btn btn-secondary btn-lg">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>