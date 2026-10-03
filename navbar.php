<?php
$user_id = $_SESSION['user_id'] ?? null;
$unread_count = 0;
if ($user_id && isset($pdo)) {
    $unread_count = get_unread_notifications_count($pdo, $user_id);
}
?>
<nav class="navbar">
    <div class="nav-left">
        <button class="hamburger" id="hamburger-menu">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Page title can go here dynamically if needed -->
    </div>
    
    <div class="nav-right">
        <?php if($user_id): ?>
        <a href="/complaint-management/user/notifications.php" class="notification-bell">
            <i class="fas fa-bell"></i>
            <?php if($unread_count > 0): ?>
                <span class="badge-count"><?= $unread_count ?></span>
            <?php endif; ?>
        </a>
        <div class="user-profile" onclick="window.location.href='/complaint-management/user/profile.php'" title="Profile">
            <?php $first_letter = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
            <div class="letter-avatar" style="width: 35px; height: 35px; font-size: 1rem;"><?= htmlspecialchars($first_letter) ?></div>
            <span class="profile-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
        </div>
        <?php else: ?>
            <a href="/complaint-management/auth/login.php" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
    </div>
</nav>
