<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: /complaint-management/admin/dashboard.php");
    } elseif ($_SESSION['user_role'] == 'staff') {
        header("Location: /complaint-management/staff/dashboard.php");
    } else {
        header("Location: /complaint-management/user/dashboard.php");
    }
} else {
    header("Location: /complaint-management/auth/login.php");
}
exit();
?>
