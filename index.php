<?php
require_once 'config/db.php';
require_once 'auth/auth_check.php';

$user = getCurrentUser();

if (!$user) {
    header('Location: /todo-list-app/auth/login.php');
    exit();
}

// Redirect based on role
if ($user['nama_role'] == 'admin') {
    header('Location: /todo-list-app/admin/');
} else {
    header('Location: /todo-list-app/user/');
}
exit();
?>
