<?php
session_start();
session_unset();
session_destroy();
header("Location: /complaint-management/auth/login.php");
exit();
?>
