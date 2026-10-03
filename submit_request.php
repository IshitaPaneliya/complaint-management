<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('user');
$user_id = $_SESSION['user_id'];

// Get Active Categories
$stmt = $pdo->query("SELECT * FROM request_categories WHERE status = 'active'");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'];
    $custom_category = isset($_POST['custom_category']) ? sanitize($_POST['custom_category']) : null;
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $location = sanitize($_POST['location']);
    $priority = $_POST['priority'];
    
    // Generate ID
    $stmt = $pdo->query("SELECT MAX(id) FROM requests");
    $last_id = $stmt->fetchColumn() ?: 0;
    $request_number = generate_id('REQ', $last_id);
    
    // Insert - Attachment logic completely removed
    $stmt = $pdo->prepare("INSERT INTO requests (request_number, user_id, category_id, custom_category, title, description, location, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$request_number, $user_id, $category_id, $custom_category, $title, $description, $location, $priority])) {
        $request_id = $pdo->lastInsertId();
        
        // Add status history
        $stmt = $pdo->prepare("INSERT INTO status_history (request_id, new_status, changed_by, remark) VALUES (?, 'Pending', ?, 'Request submitted successfully')");
        $stmt->execute([$request_id, $user_id]);
        
        // Notify Admins
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $stmt->fetchAll();
        foreach ($admins as $admin) {
            add_notification($pdo, $admin['id'], 'New Service Request', "New request ($request_number) submitted.", 'request', $request_id);
        }
        
        set_flash_message('success', "Service request submitted successfully. Your reference number is $request_number.");
        redirect('requests.php');
    } else {
        set_flash_message('danger', 'Error submitting request. Please try again.');
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<style>
/* Scoped Custom Styles for Submit Request Form */
.request-form-container {
    max-width: 1000px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    padding: 40px 50px;
    border: 1px solid var(--border-color);
}
.form-header {
    text-align: center;
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--border-color);
}
.form-header i {
    font-size: 3.5rem;
    color: var(--primary-color);
    margin-bottom: 15px;
    opacity: 0.9;
}
.form-header h2 {
    color: var(--secondary-color);
    font-size: 2.2rem;
    margin: 0 0 10px 0;
    font-weight: 700;
}
.form-header p {
    color: var(--text-muted);
    font-size: 1.05rem;
    margin: 0;
}
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 25px;
}
.form-col {
    flex: 1;
    min-width: 250px;
}
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    display: block;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 8px;
    font-size: 0.95rem;
}
.form-group label .required {
    color: var(--danger-color);
    margin-left: 3px;
}
.form-control {
    width: 100%;
    padding: 14px 18px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
    font-size: 1rem;
    color: var(--text-main);
    transition: all 0.3s ease;
}
.form-control:focus {
    background-color: #ffffff;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    outline: none;
}
textarea.form-control {
    min-height: 150px;
    resize: vertical;
}
.submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: var(--primary-color);
    color: white;
    font-weight: 600;
    font-size: 1.15rem;
    padding: 16px 30px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}
.submit-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(67, 97, 238, 0.25);
}
.submit-btn:active {
    transform: translateY(0);
}
.submit-btn.loading {
    opacity: 0.8;
    pointer-events: none;
    cursor: not-allowed;
}
.submit-btn.loading .fa-paper-plane {
    display: none;
}
.submit-btn.loading .fa-spinner {
    display: inline-block !important;
}

@media (max-width: 768px) {
    .request-form-container {
        padding: 30px 20px;
    }
    .form-row {
        gap: 15px;
        margin-bottom: 15px;
    }
    .form-group {
        margin-bottom: 20px;
    }
}
</style>

<div class="main-content">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="content">
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" style="max-width: 1000px; margin: 0 auto 20px auto;"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div class="request-form-container">
            <div class="form-header">
                <i class="fas fa-concierge-bell"></i>
                <h2>Submit Service Request</h2>
                <p>Tell us what you need and our team will review your request.</p>
            </div>
            
            <form action="" method="POST" id="requestForm">
                <!-- Row 1: Title -->
                <div class="form-group">
                    <label>Request Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="What service do you need?">
                </div>
                
                <!-- Row 2: Category & Priority -->
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Request Category <span class="required">*</span></label>
                            <select name="category_id" id="category_select" class="form-control" required>
                                <option value="" disabled selected>-- Select a category --</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Priority <span class="required">*</span></label>
                            <select name="priority" class="form-control" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Custom Category (Dynamic) -->
                <div class="form-group" id="custom-category-div" style="display: none;">
                    <label>Enter Other Request Category <span class="required">*</span></label>
                    <input type="text" name="custom_category" id="custom-category-input" class="form-control" placeholder="Please specify your request category">
                </div>

                <!-- Row 4: Location -->
                <div class="form-group">
                    <label>Location / Address <span class="required">*</span></label>
                    <input type="text" name="location" class="form-control" required placeholder="Where is this service required?">
                </div>

                <!-- Row 5: Description -->
                <div class="form-group">
                    <label>Detailed Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" required placeholder="Describe your service request in detail..."></textarea>
                </div>

                <!-- Row 6: Submit Button -->
                <div style="margin-top: 35px;">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        <i class="fas fa-spinner fa-spin" style="display:none;"></i>
                        <span id="btnText">Submit Request</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_select');
    const customDiv = document.getElementById('custom-category-div');
    const customInput = document.getElementById('custom-category-input');
    const requestForm = document.getElementById('requestForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');

    // Dynamic "Other" Category Logic
    categorySelect.addEventListener('change', function() {
        const selectedText = this.options[this.selectedIndex].text;
        
        if (selectedText.toLowerCase() === 'other') {
            customDiv.style.display = 'block';
            customInput.required = true;
            // Add a small animation effect
            customDiv.style.opacity = '0';
            setTimeout(() => {
                customDiv.style.transition = 'opacity 0.3s ease';
                customDiv.style.opacity = '1';
            }, 10);
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });

    // Form Submission / Double Click Prevention
    requestForm.addEventListener('submit', function(e) {
        // Form is valid, show loading state
        submitBtn.classList.add('loading');
        btnText.textContent = 'Submitting Request...';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
