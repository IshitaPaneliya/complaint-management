<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?")->execute([$user_id]);
    redirect('notifications.php');
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-3">
            <h2 class="page-title" style="margin: 0;">Notifications</h2>
            <?php if(count($notifications) > 0): ?>
                <a href="?mark_all_read=1" class="btn btn-sm btn-primary">Mark All as Read</a>
            <?php endif; ?>
        </div>
        
        <div class="card" style="display: block;">
            <?php if(count($notifications) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach($notifications as $n): ?>
                        <div style="padding: 15px; border-radius: 5px; background: <?= $n['is_read'] ? 'var(--bg-color)' : '#e0f2fe' ?>; border-left: 4px solid <?= $n['is_read'] ? 'var(--secondary-color)' : 'var(--primary-color)' ?>;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <strong><?= htmlspecialchars($n['title']) ?></strong>
                                <small class="text-muted"><?= time_elapsed_string($n['created_at']) ?></small>
                            </div>
                            <p style="margin: 0; color: var(--text-main);"><?= htmlspecialchars($n['message']) ?></p>
                            
                            <?php if($n['related_id']): ?>
                                <a href="<?= $n['type'] == 'complaint' ? 'complaint_details.php' : 'request_details.php' ?>?id=<?= $n['related_id'] ?>" class="btn btn-sm btn-info mt-3">View Item</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">You have no notifications.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
