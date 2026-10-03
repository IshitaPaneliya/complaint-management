<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('admin');

// Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        set_flash_message('danger', 'Email already exists.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
        if ($stmt->execute([$name, $email, $phone, $password])) {
            set_flash_message('success', 'User added successfully.');
        }
    }
    redirect('users.php');
}

// Toggle Status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $new_status = $user['status'] == 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_status, $id]);
        set_flash_message('success', "User status changed to $new_status.");
    }
    redirect('users.php');
}

// Delete User
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'")->execute([$id]);
    set_flash_message('success', "User deleted successfully.");
    redirect('users.php');
}

// Fetch users
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    <div class="content">
        
        <div class="page-header">
            <div class="page-header-info">
                <h2>Manage Users</h2>
                <p>View, add, and manage all registered users</p>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <div class="stat-card" style="margin: 0; padding: 10px 20px;">
                    <div>
                        <h3 style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 3px;">Total Users</h3>
                        <h2 style="font-size: 1.5rem; margin: 0; color: var(--primary-color); line-height: 1;"><?= count($users) ?></h2>
                    </div>
                </div>
                <button class="btn btn-primary" id="openAddUserBtn" style="height: 42px;"><i class="fas fa-plus"></i> Add User</button>
            </div>
        </div>

        <?php $flash = get_flash_message(); if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>
        
        <div class="toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, or phone...">
            </div>
            <div class="filters">
                <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">Status:</span>
                <select id="statusFilter" class="form-control">
                    <option value="all">All Users</option>
                    <option value="active">Active</option>
                    <option value="inactive">Blocked</option>
                </select>
            </div>
        </div>

        <div class="crm-table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email & Contact</th>
                        <th>Status</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr data-status="<?= $u['status'] ?>">
                        <td>
                            <div class="user-cell">
                                <div class="letter-avatar" style="width: 42px; height: 42px; font-size: 1.1rem; background-color: #<?= substr(md5($u['name']), 0, 6) ?>;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <div class="user-info">
                                    <div class="name"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="id-text">ID: #<?= $u['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500; color: var(--secondary-color);"><?= htmlspecialchars($u['email']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-phone-alt" style="font-size: 0.75rem; margin-right: 4px;"></i> <?= htmlspecialchars($u['phone']) ?></div>
                        </td>
                        <td>
                            <?php if($u['status'] == 'active'): ?>
                                <span class="badge badge-success badge-pill"><i class="fas fa-check-circle"></i> Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger badge-pill"><i class="fas fa-ban"></i> Blocked</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.95rem;">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-info view-user-btn" data-user='<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>' title="View User">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($u['status'] == 'active'): ?>
                                    <a href="?toggle_status=1&id=<?= $u['id'] ?>" class="btn btn-warning confirm-action" title="Block User">
                                        <i class="fas fa-lock"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?toggle_status=1&id=<?= $u['id'] ?>" class="btn btn-success confirm-action" title="Unblock User">
                                        <i class="fas fa-unlock"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=1&id=<?= $u['id'] ?>" class="btn btn-danger confirm-action" title="Delete User">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($users) == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">No users found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="custom-modal">
    <div class="modal-content">
        <span class="close-modal" id="closeViewModal">&times;</span>
        <div class="modal-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
            <div class="letter-avatar-large" id="modal-avatar" style="width: 70px; height: 70px; font-size: 2.2rem; margin: 0; flex-shrink: 0;">A</div>
            <div>
                <h3 id="modal-name" style="margin: 0 0 5px 0; color: var(--secondary-color); font-size: 1.4rem;">Name</h3>
                <div id="modal-status-badge"></div>
            </div>
        </div>
        <div class="modal-body">
            <div class="info-group">
                <label>Email Address</label>
                <div id="modal-email"></div>
            </div>
            <div class="info-group">
                <label>Phone Number</label>
                <div id="modal-phone"></div>
            </div>
            <div style="display: flex; gap: 15px;">
                <div class="info-group" style="flex: 1;">
                    <label>Registration Date</label>
                    <div id="modal-date"></div>
                </div>
                <div class="info-group" style="flex: 1;">
                    <label>User ID</label>
                    <div id="modal-id"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="custom-modal">
    <div class="modal-content">
        <span class="close-modal" id="closeAddModal">&times;</span>
        <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Add New User</h3>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter email address">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Create a password">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" id="cancelAddBtn" style="background: #e2e8f0; color: var(--text-main);">Cancel</button>
                <button type="submit" name="add_user" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search and Filter Logic
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('.crm-table-container tbody tr[data-status]');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();

        tableRows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const rowStatus = row.getAttribute('data-status');
            
            const matchesSearch = textContent.includes(searchTerm);
            const matchesStatus = (statusTerm === 'all' || rowStatus === statusTerm);

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);

    // View Modal Logic
    const viewModal = document.getElementById('viewUserModal');
    const closeViewBtn = document.getElementById('closeViewModal');
    const viewButtons = document.querySelectorAll('.view-user-btn');

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.getAttribute('data-user'));
            
            // Populate Modal
            document.getElementById('modal-name').textContent = user.name;
            document.getElementById('modal-email').textContent = user.email;
            document.getElementById('modal-phone').textContent = user.phone;
            document.getElementById('modal-id').textContent = '#' + user.id;
            
            // Format Date
            const dateObj = new Date(user.created_at);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            document.getElementById('modal-date').textContent = dateObj.toLocaleDateString('en-GB', options);
            
            // Avatar
            const avatar = document.getElementById('modal-avatar');
            avatar.textContent = user.name.charAt(0).toUpperCase();
            
            // Generate color based on name
            let hash = 0;
            for (let i = 0; i < user.name.length; i++) {
                hash = user.name.charCodeAt(i) + ((hash << 5) - hash);
            }
            const color = '#' + (hash & 0x00FFFFFF).toString(16).toUpperCase().padStart(6, '0');
            avatar.style.backgroundColor = color;
            
            // Status Badge
            const statusBadge = document.getElementById('modal-status-badge');
            if (user.status === 'active') {
                statusBadge.innerHTML = '<span class="badge badge-success badge-pill"><i class="fas fa-check-circle"></i> Active Account</span>';
            } else {
                statusBadge.innerHTML = '<span class="badge badge-danger badge-pill"><i class="fas fa-ban"></i> Blocked Account</span>';
            }
            
            viewModal.style.display = 'flex';
        });
    });

    if (closeViewBtn) closeViewBtn.addEventListener('click', () => viewModal.style.display = 'none');

    // Add User Modal Logic
    const addModal = document.getElementById('addUserModal');
    const openAddBtn = document.getElementById('openAddUserBtn');
    const closeAddBtn = document.getElementById('closeAddModal');
    const cancelAddBtn = document.getElementById('cancelAddBtn');

    if (openAddBtn) openAddBtn.addEventListener('click', () => addModal.style.display = 'flex');
    if (closeAddBtn) closeAddBtn.addEventListener('click', () => addModal.style.display = 'none');
    if (cancelAddBtn) cancelAddBtn.addEventListener('click', () => addModal.style.display = 'none');

    window.addEventListener('click', (e) => {
        if (e.target === viewModal) viewModal.style.display = 'none';
        if (e.target === addModal) addModal.style.display = 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
