<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];
$complaint_id = $_GET['id'] ?? null;

if (!$complaint_id) redirect('complaints.php');

try {
    // Fetch complaint details
    $stmt = $pdo->prepare("SELECT c.*, cat.category_name, u.name as staff_name FROM complaints c 
                           JOIN complaint_categories cat ON c.category_id = cat.id 
                           LEFT JOIN users u ON c.assigned_staff_id = u.id
                           WHERE c.id = ? AND c.user_id = ?");
    $stmt->execute([$complaint_id, $user_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        set_flash_message('danger', 'Complaint not found.');
        redirect('complaints.php');
        exit();
    }

    // Fetch comments
    $stmt = $pdo->prepare("SELECT c.*, u.name, u.role, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.complaint_id = ? ORDER BY c.created_at ASC");
    $stmt->execute([$complaint_id]);
    $comments = $stmt->fetchAll();

    // Fetch status history
    $stmt = $pdo->prepare("SELECT h.*, u.name FROM status_history h JOIN users u ON h.changed_by = u.id WHERE h.complaint_id = ? ORDER BY h.changed_at ASC");
    $stmt->execute([$complaint_id]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    set_flash_message('danger', 'A database error occurred while fetching details. Please try again later.');
    redirect('complaints.php');
    exit();
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = sanitize($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO comments (complaint_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$complaint_id, $user_id, $message]);
        redirect("complaint_details.php?id=$complaint_id");
    }
}

// Handle Ticket Closure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['close_ticket']) && $complaint['status'] == 'Resolved') {
    $pdo->prepare("UPDATE complaints SET status = 'Closed' WHERE id = ?")->execute([$complaint_id]);
    $pdo->prepare("INSERT INTO status_history (complaint_id, new_status, changed_by, remark) VALUES (?, 'Closed', ?, 'User confirmed resolution and closed ticket')")->execute([$complaint_id, $user_id]);
    set_flash_message('success', 'The complaint is now closed.');
    redirect("complaint_details.php?id=$complaint_id");
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">Complaint Details - <?= $complaint['complaint_number'] ?></h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <!-- Main Info -->
            <div style="flex: 2; min-width: 300px;">
                <div class="card" style="display: block; margin-bottom: 20px;">
                    <h3><?= htmlspecialchars($complaint['title']) ?></h3>
                    <hr style="margin: 10px 0; border: 0; border-top: 1px solid var(--border-color);">
                    <p><strong>Category:</strong> <?= get_display_category($complaint['category_name'], $complaint['custom_category']) ?></p>
                    <p><strong>Priority:</strong> <?= get_status_badge($complaint['priority']) ?></p>
                    <p><strong>Status:</strong> <?= get_status_badge($complaint['status']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($complaint['location']) ?></p>
                    <p><strong>Date Submitted:</strong> <?= date('d M Y, h:i A', strtotime($complaint['created_at'])) ?></p>
                    <?php if($complaint['assigned_staff_id']): ?>
                        <p><strong>Assigned To:</strong> <?= htmlspecialchars($complaint['staff_name']) ?></p>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px;">
                        <strong>Description:</strong>
                        <p style="background: var(--bg-color); padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <?= nl2br(htmlspecialchars($complaint['description'])) ?>
                        </p>
                    </div>

                    <?php if($complaint['attachment']): ?>
                        <div style="margin-top: 15px;">
                            <strong>Attachment:</strong><br>
                            <a href="../uploads/complaints/<?= $complaint['attachment'] ?>" target="_blank" class="btn btn-sm btn-secondary mt-2"><i class="fas fa-paperclip"></i> View Attachment</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Close Ticket Section if Resolved -->
                <?php if($complaint['status'] == 'Resolved'): ?>
                    <div class="card" style="display: block; margin-bottom: 20px; border-left-color: var(--success-color);">
                        <h4><i class="fas fa-check-circle text-success"></i> Close Ticket</h4>
                        <p class="text-muted mb-3">Your issue has been marked as resolved. If you are satisfied, please confirm to close this ticket.</p>
                        <form method="POST">
                            <input type="hidden" name="close_ticket" value="1">
                            <button type="submit" class="btn btn-success confirm-action">Confirm & Close Ticket</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h3>Discussion / Updates</h3>
                    <hr style="margin: 10px 0;">
                    
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px; margin-bottom: 20px;">
                        <?php if(count($comments) == 0): ?>
                            <p class="text-muted">No comments yet.</p>
                        <?php endif; ?>
                        
                        <?php foreach($comments as $c): ?>
                            <div class="comment">
                                <?php $c_letter = strtoupper(substr($c['name'], 0, 1)); ?>
                                <div class="letter-avatar" style="flex-shrink: 0; margin-right: 15px;"><?= htmlspecialchars($c_letter) ?></div>
                                <div class="comment-body <?= $c['role'] != 'user' ? 'admin-reply' : '' ?>">
                                    <div class="comment-header">
                                        <h4><?= htmlspecialchars($c['name']) ?> <?= $c['role'] != 'user' ? '<span class="badge badge-info">'.$c['role'].'</span>' : '' ?></h4>
                                        <span><?= time_elapsed_string($c['created_at']) ?></span>
                                    </div>
                                    <p><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if($complaint['status'] != 'Closed' && $complaint['status'] != 'Rejected'): ?>
                        <form method="POST">
                            <div class="form-group">
                                <textarea name="message" class="form-control" placeholder="Type your message here..." required style="min-height: 80px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div style="flex: 1; min-width: 250px;">
                <div class="card" style="display: block;">
                    <h3>Timeline</h3>
                    <hr style="margin: 10px 0;">
                    <div class="timeline">
                        <?php foreach($history as $h): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <strong><?= $h['new_status'] ?></strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 5px 0;">
                                        <?= htmlspecialchars($h['remark']) ?>
                                    </p>
                                    <small class="text-muted"><?= date('d M, h:i A', strtotime($h['changed_at'])) ?> by <?= $h['name'] ?></small>
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
