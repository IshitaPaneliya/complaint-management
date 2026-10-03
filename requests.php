<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];

// Fetch requests
$stmt = $pdo->prepare("SELECT r.*, cat.category_name FROM requests r JOIN request_categories cat ON r.category_id = cat.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">My Service Requests</h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div class="table-container">
            <?php if(count($requests) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $r): ?>
                    <tr>
                        <td><?= $r['request_number'] ?></td>
                        <td><?= get_display_category($r['category_name'], $r['custom_category']) ?></td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><?= get_status_badge($r['priority']) ?></td>
                        <td><?= get_status_badge($r['status']) ?></td>
                        <td><?= date('d M, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <a href="request_details.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-center">
                    <p>You have not submitted any service requests yet.</p>
                    <a href="submit_request.php" class="btn btn-primary mt-3">Submit a Request</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
