<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];

// Get Complaint Stats
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_complaints,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_complaints,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_complaints,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_complaints
    FROM complaints WHERE user_id = ?");
$stmt->execute([$user_id]);
$complaint_stats = $stmt->fetch();

// Get Request Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_requests FROM requests WHERE user_id = ?");
$stmt->execute([$user_id]);
$request_stats = $stmt->fetch();

// Get Recent Complaints
$stmt = $pdo->prepare("SELECT c.*, cat.category_name FROM complaints c JOIN complaint_categories cat ON c.category_id = cat.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_complaints = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <h2 class="page-title">User Dashboard</h2>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-info">
                    <h3>Total Complaints</h3>
                    <h2><?= $complaint_stats['total_complaints'] ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Pending</h3>
                    <h2><?= $complaint_stats['pending_complaints'] ?: 0 ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>In Progress</h3>
                    <h2><?= $complaint_stats['in_progress_complaints'] ?: 0 ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-spinner"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Resolved</h3>
                    <h2><?= $complaint_stats['resolved_complaints'] ?: 0 ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card" style="border-left-color: var(--info-color);">
                <div class="card-info">
                    <h3>Total Requests</h3>
                    <h2><?= $request_stats['total_requests'] ?></h2>
                </div>
                <div class="card-icon" style="color: var(--info-color);"><i class="fas fa-concierge-bell"></i></div>
            </div>
        </div>

        <h3 class="mt-3 mb-3" style="color: var(--secondary-color);">Recent Complaints</h3>
        <div class="table-container">
            <?php if(count($recent_complaints) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Complaint No</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_complaints as $c): ?>
                    <tr>
                        <td><?= $c['complaint_number'] ?></td>
                        <td><?= get_display_category($c['category_name'], $c['custom_category']) ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= get_status_badge($c['priority']) ?></td>
                        <td><?= get_status_badge($c['status']) ?></td>
                        <td><?= date('d M, Y', strtotime($c['created_at'])) ?></td>
                        <td><a href="complaint_details.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No recent complaints found.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
