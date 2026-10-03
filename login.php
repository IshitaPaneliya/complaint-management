<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (isset($_SESSION['user_id'])) {
    redirect('/complaint-management/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] == 'inactive') {
                $error = 'Your account has been blocked. Contact admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_image'] = $user['profile_image'];
                
                redirect('/complaint-management/index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRMS</title>
    <link rel="stylesheet" href="/complaint-management/assets/css/style.css">
    <style>
        body { justify-content: center; align-items: center; background-color: var(--sidebar-bg); }
        .auth-container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h2 { color: var(--secondary-color); margin-bottom: 10px; }
        .password-container { position: relative; }
        .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted); background: none; border: none; }
        .btn-block { width: 100%; padding: 12px; margin-top: 10px; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.9rem; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="fas fa-hands-helping text-primary"></i> CRMS</h2>
            <p>Login to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="password" class="form-control" required>
                    <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
    <script src="/complaint-management/assets/js/script.js"></script>
</body>
</html>
