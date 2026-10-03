<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'] ?? null;

if (!$request_id) redirect('requests.php');

try {
    // Fetch details
    $stmt = $pdo->prepare("SELECT r.*, cat.category_name, u.name as staff_name FROM requests r 
                           JOIN request_categories cat ON r.category_id = cat.id 
                           LEFT JOIN users u ON r.assigned_staff_id = u.id
                           WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$request_id, $user_id]);
    $req = $stmt->fetch();

    if (!$req) {
        set_flash_message('danger', 'Request not found.');
        redirect('requests.php');
        exit();
    }

    // Fetch comments
    $stmt = $pdo->prepare("SELECT c.*, u.name, u.role, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.request_id = ? ORDER BY c.created_at ASC");
    $stmt->execute([$request_id]);
    $comments = $stmt->fetchAll();

    // Fetch status history
    $stmt = $pdo->prepare("SELECT h.*, u.name FROM status_history h JOIN users u ON h.changed_by = u.id WHERE h.request_id = ? ORDER BY h.changed_at ASC");
    $stmt->execute([$request_id]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    set_flash_message('danger', 'A database error occurred while fetching details. Please try again later.');
    redirect('requests.php');
    exit();
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = sanitize($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO comments (request_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$request_id, $user_id, $message]);
        redirect("request_details.php?id=$request_id");
    }
}

// Handle Ticket Closure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['close_ticket']) && $req['status'] == 'Resolved') {
    $pdo->prepare("UPDATE requests SET status = 'Closed' WHERE id = ?")->execute([$request_id]);
    $pdo->prepare("INSERT INTO status_history (request_id, new_status, changed_by, remark) VALUES (?, 'Closed', ?, 'User confirmed resolution and closed ticket')")->execute([$request_id, $user_id]);
    set_flash_message('success', 'The request is now closed.');
    redirect("request_details.php?id=$request_id");
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">Request Details - <?= $req['request_number'] ?></h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 300px;">
                <div class="card" style="display: block; margin-bottom: 20px; border-left-color: var(--info-color);">
                    <h3><?= htmlspecialchars($req['title']) ?></h3>
                    <hr style="margin: 10px 0;">
                    <p><strong>Category:</strong> <?= get_display_category($req['category_name'], $req['custom_category']) ?></p>
                    <p><strong>Priority:</strong> <?= get_status_badge($req['priority']) ?></p>
                    <p><strong>Status:</strong> <?= get_status_badge($req['status']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($req['location']) ?></p>
                    <p><strong>Date Submitted:</strong> <?= date('d M Y, h:i A', strtotime($req['created_at'])) ?></p>
                    <?php if($req['assigned_staff_id']): ?>
                        <p><strong>Assigned To:</strong> <?= htmlspecialchars($req['staff_name']) ?></p>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px;">
                        <strong>Description:</strong>
                        <p style="background: var(--bg-color); padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <?= nl2br(htmlspecialchars($req['description'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Close Ticket Section if Resolved -->
                <?php if($req['status'] == 'Resolved'): ?>
                    <div class="card" style="display: block; margin-bottom: 20px; border-left-color: var(--success-color);">
                        <h4><i class="fas fa-check-circle text-success"></i> Close Ticket</h4>
                        <p class="text-muted mb-3">Your request has been marked as resolved. If you are satisfied, please confirm to close this ticket.</p>
                        <form method="POST">
                            <input type="hidden" name="close_ticket" value="1">
                            <button type="submit" class="btn btn-success confirm-action">Confirm & Close Ticket</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="comments-section">
                    <h3>Discussion</h3>
                    <hr>
                    <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
                        <?php foreach($comments as $c): ?>
                            <div class="comment">
                                <?php $c_letter = strtoupper(substr($c['name'], 0, 1)); ?>
                                <div class="letter-avatar" style="flex-shrink: 0; margin-right: 15px;"><?= htmlspecialchars($c_letter) ?></div>
                                <div class="comment-body <?= $c['role'] != 'user' ? 'admin-reply' : '' ?>">
                                    <div class="comment-header">
                                        <h4><?= htmlspecialchars($c['name']) ?></h4>
                                        <span><?= time_elapsed_string($c['created_at']) ?></span>
                                    </div>
                                    <p><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if($req['status'] != 'Closed' && $req['status'] != 'Rejected'): ?>
                        <form method="POST">
                            <div class="form-group"><textarea name="message" class="form-control" required style="min-height: 80px;"></textarea></div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div class="card" style="display: block;">
                    <h3>Timeline</h3>
                    <hr>
                    <div class="timeline">
                        <?php foreach($history as $h): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <strong><?= $h['new_status'] ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0;"><?= htmlspecialchars($h['remark']) ?></p>
                                    <small class="text-muted"><?= date('d M', strtotime($h['changed_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
