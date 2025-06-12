<?php
require_once 'config/db.php';
require_once 'auth/auth_check.php';

$user = getCurrentUser();

if (!$user) {
    header('Location: /lislty/auth/login.php');
    exit();
}

// Redirect based on role
if ($user['nama_role'] == 'admin') {
    header('Location: /lislty/admin/');
} else {
    header('Location: /lislty/user/');
}
exit();
?>
