<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash_message('warning', 'Please login to access this page.');
        redirect('/complaint-management/auth/login.php');
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user_role'] !== $role) {
        set_flash_message('error', 'Unauthorized access.');
        if ($_SESSION['user_role'] == 'admin') redirect('/complaint-management/admin/dashboard.php');
        else if ($_SESSION['user_role'] == 'staff') redirect('/complaint-management/staff/dashboard.php');
        else redirect('/complaint-management/user/dashboard.php');
    }
}
?>
