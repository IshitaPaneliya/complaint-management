<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_role'] ?? 'user';
?>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <i class="fas fa-hands-helping fa-2x"></i>
            <h2>CRMS</h2>
        </div>
        <button class="close-sidebar" id="close-sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-scroll">
        <ul class="sidebar-menu">
            <?php if($role == 'user'): ?>
                <li><a href="/complaint-management/user/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="/complaint-management/user/submit_complaint.php" class="<?= $current_page == 'submit_complaint.php' ? 'active' : '' ?>"><i class="fas fa-exclamation-circle"></i> Submit Complaint</a></li>
                <li><a href="/complaint-management/user/submit_request.php" class="<?= $current_page == 'submit_request.php' ? 'active' : '' ?>"><i class="fas fa-concierge-bell"></i> Submit Request</a></li>
                <li><a href="/complaint-management/user/complaints.php" class="<?= $current_page == 'complaints.php' ? 'active' : '' ?>"><i class="fas fa-list-alt"></i> My Complaints</a></li>
                <li><a href="/complaint-management/user/requests.php" class="<?= $current_page == 'requests.php' ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> My Requests</a></li>
                <li><a href="/complaint-management/user/notifications.php" class="<?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a></li>
            
            <?php elseif($role == 'staff'): ?>
                <li><a href="/complaint-management/staff/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="/complaint-management/staff/complaints.php" class="<?= $current_page == 'complaints.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Assigned Complaints</a></li>
                <li><a href="/complaint-management/staff/requests.php" class="<?= $current_page == 'requests.php' ? 'active' : '' ?>"><i class="fas fa-clipboard-check"></i> Assigned Requests</a></li>

            <?php elseif($role == 'admin'): ?>
                <li><a href="/complaint-management/admin/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="/complaint-management/admin/users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="/complaint-management/admin/staff.php" class="<?= $current_page == 'staff.php' ? 'active' : '' ?>"><i class="fas fa-user-tie"></i> Staff</a></li>
                <li><a href="/complaint-management/admin/categories.php" class="<?= $current_page == 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="/complaint-management/admin/complaints.php" class="<?= $current_page == 'complaints.php' ? 'active' : '' ?>"><i class="fas fa-list"></i> Complaints</a></li>
                <li><a href="/complaint-management/admin/requests.php" class="<?= $current_page == 'requests.php' ? 'active' : '' ?>"><i class="fas fa-clipboard"></i> Requests</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <a href="/complaint-management/auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
