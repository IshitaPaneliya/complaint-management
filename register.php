<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (isset($_SESSION['user_id'])) {
    redirect('/complaint-management/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($cpassword)) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $cpassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
            if ($stmt->execute([$name, $email, $phone, $hashed, $address])) {
                set_flash_message('success', 'Registration successful! Please login.');
                redirect('login.php');
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CRMS</title>
    <link rel="stylesheet" href="/complaint-management/assets/css/style.css">
    <style>
        body { justify-content: center; align-items: center; background-color: var(--sidebar-bg); padding: 20px 0; }
        .auth-container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 600px; margin: auto; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h2 { color: var(--secondary-color); margin-bottom: 10px; }
        .password-container { position: relative; }
        .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted); background: none; border: none; }
        .btn-block { width: 100%; padding: 12px; margin-top: 10px; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .row { display: flex; gap: 20px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 250px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="fas fa-hands-helping text-primary"></i> CRMS</h2>
            <p>Create a new account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="row">
                <div class="col form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="col form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="col form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Password *</label>
                    <div class="password-container">
                        <input type="password" name="password" class="form-control" required>
                        <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="col form-group">
                    <label>Confirm Password *</label>
                    <div class="password-container">
                        <input type="password" name="cpassword" class="form-control" required>
                        <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script src="/complaint-management/assets/js/script.js"></script>
</body>
</html>
