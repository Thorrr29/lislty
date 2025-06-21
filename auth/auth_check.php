<?php
require_once __DIR__ . '/../config/db.php';

function checkAuth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /todo-list-app/auth/login.php');
        exit();
    }
    
    if ($required_role) {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || $user['nama_role'] !== $required_role) {
            header('Location: /todo-list-app/');
            exit();
        }
    }
}

function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user && $user['nama_role'] === 'admin';
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>
