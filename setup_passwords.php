<?php
require_once 'config/database.php';

// Hash passwords correctly instead of using dummy strings in SQL
$hashed_admin = password_hash('Admin@123', PASSWORD_DEFAULT);
$hashed_staff = password_hash('Staff@123', PASSWORD_DEFAULT);
$hashed_user = password_hash('User@123', PASSWORD_DEFAULT);

$pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@gmail.com'")->execute([$hashed_admin]);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'staff@gmail.com'")->execute([$hashed_staff]);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'user@gmail.com'")->execute([$hashed_user]);

echo "Passwords updated successfully.";
?>
