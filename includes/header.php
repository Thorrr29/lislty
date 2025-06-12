<?php
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /todo-list-app/auth/login.php');
    exit();
}

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: /todo-list-app/auth/login.php');
    exit();
}

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Listly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/todo-list-app/assets/css/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Enhanced Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top modern-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <!-- Brand Section -->
            <div class="navbar-brand-wrapper">
                <a class="navbar-brand modern-brand" href="/todo-list-app/">
                    <div class="brand-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <span class="brand-text">Listly</span>
                    <span class="brand-subtitle d-none d-sm-inline">Task Manager</span>
                </a>
            </div>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler modern-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="toggler-line"></span>
                <span class="toggler-line"></span>
                <span class="toggler-line"></span>
            </button>
            
            <!-- Navigation Content -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if ($user['nama_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link modern-nav-link <?php echo ($current_dir == 'admin' && $current_page == 'index.php') ? 'active' : ''; ?>" 
                               href="/todo-list-app/admin/">
                                <i class="fas fa-tachometer-alt nav-icon"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle modern-nav-link <?php echo in_array($current_dir, ['kategori', 'prioritas', 'status', 'pengguna']) ? 'active' : ''; ?>" 
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog nav-icon"></i>
                                <span class="nav-text">Master Data</span>
                            </a>
                            <ul class="dropdown-menu modern-dropdown">
                                <li><h6 class="dropdown-header"><i class="fas fa-database me-2"></i>Data Master</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item modern-dropdown-item <?php echo $current_dir == 'kategori' ? 'active' : ''; ?>" 
                                       href="/todo-list-app/admin/kategori/">
                                        <i class="fas fa-tags me-2"></i>Kategori
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item modern-dropdown-item <?php echo $current_dir == 'prioritas' ? 'active' : ''; ?>" 
                                       href="/todo-list-app/admin/prioritas/">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Prioritas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item modern-dropdown-item <?php echo $current_dir == 'status' ? 'active' : ''; ?>" 
                                       href="/todo-list-app/admin/status/">
                                        <i class="fas fa-flag me-2"></i>Status
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item modern-dropdown-item <?php echo $current_dir == 'pengguna' ? 'active' : ''; ?>" 
                                       href="/todo-list-app/admin/pengguna/">
                                        <i class="fas fa-users me-2"></i>Pengguna
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link modern-nav-link <?php echo ($current_dir == 'user' && $current_page == 'index.php') ? 'active' : ''; ?>" 
                               href="/todo-list-app/user/">
                                <i class="fas fa-home nav-icon"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link modern-nav-link <?php echo $current_dir == 'tugas' ? 'active' : ''; ?>" 
                               href="/todo-list-app/user/tugas/">
                                <i class="fas fa-list-check nav-icon"></i>
                                <span class="nav-text">Tugas Saya</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- User Profile Section -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle modern-profile-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-section">
                                <div class="profile-avatar">
                                    <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                </div>
                                <div class="profile-info d-none d-lg-block">
                                    <div class="profile-name"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                    <div class="profile-role"><?php echo ucfirst($user['nama_role']); ?></div>
                                </div>
                                <i class="fas fa-chevron-down profile-arrow"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end modern-dropdown profile-dropdown">
                            <li class="dropdown-header profile-dropdown-header">
                                <div class="profile-avatar-large">
                                    <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                </div>
                                <div class="profile-details">
                                    <div class="profile-name-large"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                    <div class="profile-email"><?php echo htmlspecialchars($user['email'] ?? '@' . $user['username']); ?></div>
                                    <span class="badge profile-role-badge"><?php echo ucfirst($user['nama_role']); ?></span>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item modern-dropdown-item" href="#" onclick="showProfileModal()">
                                    <i class="fas fa-user-edit me-2"></i>Edit Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item modern-dropdown-item" href="#" onclick="showSettingsModal()">
                                    <i class="fas fa-cog me-2"></i>Pengaturan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item modern-dropdown-item logout-item" href="/todo-list-app/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="main-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
