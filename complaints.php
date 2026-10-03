<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];

// Fetch complaints
$stmt = $pdo->prepare("SELECT c.*, cat.category_name FROM complaints c JOIN complaint_categories cat ON c.category_id = cat.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">My Complaints</h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div class="table-container">
            <?php if(count($complaints) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Complaint No</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($complaints as $c): ?>
                    <tr>
                        <td><?= $c['complaint_number'] ?></td>
                        <td><?= get_display_category($c['category_name'], $c['custom_category']) ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= get_status_badge($c['priority']) ?></td>
                        <td><?= get_status_badge($c['status']) ?></td>
                        <td><?= date('d M, Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <a href="complaint_details.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-center">
                    <p>You have not submitted any complaints yet.</p>
                    <a href="submit_complaint.php" class="btn btn-primary mt-3">Submit a Complaint</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
