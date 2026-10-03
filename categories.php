<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('admin');

// Add Complaint Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_c_category'])) {
    $name = sanitize($_POST['name']);
    $desc = sanitize($_POST['description']);
    $stmt = $pdo->prepare("SELECT id FROM complaint_categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        set_flash_message('danger', 'Complaint category already exists.');
    } else {
        $pdo->prepare("INSERT INTO complaint_categories (category_name, description) VALUES (?, ?)")->execute([$name, $desc]);
        set_flash_message('success', 'Complaint category added.');
    }
    redirect('categories.php');
}

// Add Request Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_r_category'])) {
    $name = sanitize($_POST['name']);
    $desc = sanitize($_POST['description']);
    $stmt = $pdo->prepare("SELECT id FROM request_categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        set_flash_message('danger', 'Request category already exists.');
    } else {
        $pdo->prepare("INSERT INTO request_categories (category_name, description) VALUES (?, ?)")->execute([$name, $desc]);
        set_flash_message('success', 'Request category added.');
    }
    redirect('categories.php');
}

// Delete Category
if (isset($_GET['delete_c']) && isset($_GET['id'])) {
    try {
        $pdo->prepare("DELETE FROM complaint_categories WHERE id = ?")->execute([$_GET['id']]);
        set_flash_message('success', "Category deleted.");
    } catch(PDOException $e) {
        set_flash_message('danger', "Cannot delete. Category is in use.");
    }
    redirect('categories.php');
}
if (isset($_GET['delete_r']) && isset($_GET['id'])) {
    try {
        $pdo->prepare("DELETE FROM request_categories WHERE id = ?")->execute([$_GET['id']]);
        set_flash_message('success', "Category deleted.");
    } catch(PDOException $e) {
        set_flash_message('danger', "Cannot delete. Category is in use.");
    }
    redirect('categories.php');
}

$c_cats = $pdo->query("SELECT * FROM complaint_categories")->fetchAll();
$r_cats = $pdo->query("SELECT * FROM request_categories")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    <div class="content">
        <h2 class="page-title">Manage Categories</h2>
        <?php $flash = get_flash_message(); if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <div class="card" style="display: block; margin-bottom: 20px;">
                    <h3>Complaint Categories</h3><hr>
                    <form method="POST" class="mb-3">
                        <div class="form-group"><input type="text" name="name" class="form-control" placeholder="Category Name" required></div>
                        <div class="form-group"><input type="text" name="description" class="form-control" placeholder="Description"></div>
                        <button type="submit" name="add_c_category" class="btn btn-sm btn-primary">Add Category</button>
                    </form>
                    <div class="table-container" style="padding: 0; box-shadow: none;">
                        <table>
                            <thead><tr><th>Name</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach($c_cats as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['category_name']) ?></td>
                                    <td><a href="?delete_c=1&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger confirm-action"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <div class="card" style="display: block; margin-bottom: 20px;">
                    <h3>Request Categories</h3><hr>
                    <form method="POST" class="mb-3">
                        <div class="form-group"><input type="text" name="name" class="form-control" placeholder="Category Name" required></div>
                        <div class="form-group"><input type="text" name="description" class="form-control" placeholder="Description"></div>
                        <button type="submit" name="add_r_category" class="btn btn-sm btn-info">Add Category</button>
                    </form>
                    <div class="table-container" style="padding: 0; box-shadow: none;">
                        <table>
                            <thead><tr><th>Name</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach($r_cats as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['category_name']) ?></td>
                                    <td><a href="?delete_r=1&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger confirm-action"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
