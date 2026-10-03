<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$name, $phone, $address, $user_id])) {
        $_SESSION['user_name'] = $name;
        set_flash_message('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
            set_flash_message('success', 'Password changed successfully.');
            redirect('profile.php');
        } else {
            set_flash_message('danger', 'Passwords do not match or are less than 6 characters.');
        }
    } else {
        set_flash_message('danger', 'Current password is incorrect.');
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">My Profile</h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="card" style="flex: 1; min-width: 300px; display: block;">
                <h3>Update Profile Information</h3>
                <hr style="margin: 10px 0;">
                <form action="" method="POST">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <?php $first_letter = strtoupper(substr($user['name'], 0, 1)); ?>
                        <div class="letter-avatar-large" style="margin: 0 auto;"><?= htmlspecialchars($first_letter) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email (Cannot be changed)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="card" style="flex: 1; min-width: 300px; display: block; align-self: flex-start;">
                <h3>Change Password</h3>
                <hr style="margin: 10px 0;">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
