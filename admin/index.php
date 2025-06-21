<?php
$page_title = 'Dashboard Admin';
require_once '../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();


$stats = [];

// Total user
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role_id != 1");
$result = $stmt->fetch();
$stats['total_users'] = $result['total'] ?? 0;

if ($stats['total_users'] == 0) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    $stats['total_users'] = $result['total'] ?? 0;
}

// Total kategori
$stmt = $pdo->query("SELECT COUNT(*) as total FROM kategori");
$result = $stmt->fetch();
$stats['total_categories'] = $result['total'] ?? 0;

// Total prioritas  
$stmt = $pdo->query("SELECT COUNT(*) as total FROM prioritas");
$result = $stmt->fetch();
$stats['total_priorities'] = $result['total'] ?? 0;

// Total tugas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tugas");
$result = $stmt->fetch();
$stats['total_tasks'] = $result['total'] ?? 0;


$current_user_id = $_SESSION['user_id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$current_user_id]);
$recent_users = $stmt->fetchAll();


if (empty($recent_users)) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
}


$stmt = $pdo->prepare("
    SELECT s.nama_status, s.warna, COUNT(t.id) as jumlah 
    FROM status_tugas s 
    LEFT JOIN tugas t ON s.id = t.status_id 
    GROUP BY s.id, s.nama_status, s.warna
    ORDER BY s.id
");
$stmt->execute();
$task_stats = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i><?php echo date('d F Y'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-primary">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_users']); ?></h3>
            <p class="text-muted mb-0">Total Pengguna</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-success">
                <i class="fas fa-tags"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_categories']); ?></h3>
            <p class="text-muted mb-0">Kategori</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_priorities']); ?></h3>
            <p class="text-muted mb-0">Prioritas</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-info">
                <i class="fas fa-tasks"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_tasks']); ?></h3>
            <p class="text-muted mb-0">Total Tugas</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tugas Statistik -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Tugas</h5>
            </div>
            <div class="card-body">
                <?php foreach ($task_stats as $stat): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="badge me-2" style="background-color: <?php echo $stat['warna']; ?>; width: 20px; height: 20px;"></div>
                            <span><?php echo htmlspecialchars($stat['nama_status']); ?></span>
                        </div>
                        <span class="badge bg-secondary"><?php echo number_format($stat['jumlah']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- pengguna terbaru -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Pengguna Terbaru</h5>
                <a href="/todo-list-app/admin/pengguna/" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_users)): ?>
                    <p class="text-muted text-center">Belum ada pengguna terdaftar</p>
                <?php else: ?>
                    <?php foreach ($recent_users as $user): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h6>
                                <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                <br>
                                <span class="badge <?php echo $user['status'] == 'aktif' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- aksi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/todo-list-app/admin/kategori/tambah.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus me-2"></i>Tambah Kategori
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/todo-list-app/admin/prioritas/tambah.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus me-2"></i>Tambah Prioritas
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/todo-list-app/admin/status/tambah.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-plus me-2"></i>Tambah Status
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/todo-list-app/admin/pengguna/tambah.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-plus me-2"></i>Tambah Pengguna
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
