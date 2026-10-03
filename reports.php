<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('admin');

// Complaints by Category
$cat_stmt = $pdo->query("SELECT c.category_name, COUNT(comp.id) as total FROM complaint_categories c LEFT JOIN complaints comp ON c.id = comp.category_id GROUP BY c.id");
$cat_data = $cat_stmt->fetchAll();

// Complaints by Priority
$prio_stmt = $pdo->query("SELECT priority, COUNT(id) as total FROM complaints GROUP BY priority");
$prio_data = $prio_stmt->fetchAll();

// Staff Performance
$staff_stmt = $pdo->query("SELECT u.name, 
                            COUNT(a.id) as assigned, 
                            SUM(CASE WHEN c.status = 'Resolved' THEN 1 ELSE 0 END) + SUM(CASE WHEN r.status = 'Resolved' THEN 1 ELSE 0 END) as resolved
                           FROM users u 
                           LEFT JOIN assignments a ON u.id = a.staff_id 
                           LEFT JOIN complaints c ON a.complaint_id = c.id
                           LEFT JOIN requests r ON a.request_id = r.id
                           WHERE u.role = 'staff' 
                           GROUP BY u.id");
$staff_data = $staff_stmt->fetchAll();


include '../includes/header.php';
include '../includes/sidebar.php';
?>
<style>
    @media print {
        .sidebar, .navbar, .btn { display: none !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; break-inside: avoid; }
    }
</style>
<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    <div class="content">
        <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-3">
            <h2 class="page-title" style="margin: 0;">System Reports</h2>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Report</button>
        </div>
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <div class="card" style="flex: 1; min-width: 300px; display: block;">
                <h3>Complaints by Category</h3><hr>
                <table>
                    <thead><tr><th>Category</th><th>Total Complaints</th></tr></thead>
                    <tbody>
                        <?php foreach($cat_data as $c): ?>
                            <tr><td><?= htmlspecialchars($c['category_name']) ?></td><td><?= $c['total'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card" style="flex: 1; min-width: 300px; display: block;">
                <h3>Complaints by Priority</h3><hr>
                <table>
                    <thead><tr><th>Priority</th><th>Total Complaints</th></tr></thead>
                    <tbody>
                        <?php foreach($prio_data as $p): ?>
                            <tr><td><?= get_status_badge($p['priority']) ?></td><td><?= $p['total'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="card" style="flex: 2; min-width: 300px; display: block;">
                <h3>Staff Performance</h3><hr>
                <table>
                    <thead><tr><th>Staff Name</th><th>Total Assigned</th><th>Total Resolved</th><th>Resolution Rate</th></tr></thead>
                    <tbody>
                        <?php foreach($staff_data as $s): 
                            $rate = $s['assigned'] > 0 ? round(($s['resolved'] / $s['assigned']) * 100, 2) : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td><?= $s['assigned'] ?></td>
                                <td><?= $s['resolved'] ?></td>
                                <td>
                                    <div style="width: 100%; background: #eee; border-radius: 10px; height: 10px; margin-top: 5px;">
                                        <div style="width: <?= $rate ?>%; background: var(--success-color); height: 100%; border-radius: 10px;"></div>
                                    </div>
                                    <small><?= $rate ?>%</small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
