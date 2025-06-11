<?php
$page_title = 'Dashboard User';
require_once '../auth/auth_check.php';
checkAuth();

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

$stats = [];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats['total_tasks'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE user_id = ? AND status_id = 3");
$stmt->execute([$user_id]);
$stats['completed_tasks'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE user_id = ? AND status_id != 3");
$stmt->execute([$user_id]);
$stats['pending_tasks'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE user_id = ? AND tanggal_jatuh_tempo < CURDATE() AND status_id != 3");
$stmt->execute([$user_id]);
$stats['overdue_tasks'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT t.*, k.nama_kategori, k.warna as kategori_warna, 
           p.nama_prioritas, p.warna as prioritas_warna,
           s.nama_status, s.warna as status_warna
    FROM tugas t
    LEFT JOIN kategori k ON t.kategori_id = k.id
    LEFT JOIN prioritas p ON t.prioritas_id = p.id
    LEFT JOIN status_tugas s ON t.status_id = s.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_tasks = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT s.nama_status, s.warna, COUNT(t.id) as jumlah 
    FROM status_tugas s 
    LEFT JOIN tugas t ON s.id = t.status_id AND t.user_id = ?
    GROUP BY s.id, s.nama_status, s.warna
    ORDER BY s.id
");
$stmt->execute([$user_id]);
$task_stats = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-home me-2"></i>Dashboard</h2>
                <p class="text-muted mb-0">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</p>
            </div>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i><?php echo date('d F Y'); ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-primary">
                <i class="fas fa-tasks"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_tasks']); ?></h3>
            <p class="text-muted mb-0">Total Tugas</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['completed_tasks']); ?></h3>
            <p class="text-muted mb-0">Selesai</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-warning">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['pending_tasks']); ?></h3>
            <p class="text-muted mb-0">Belum Selesai</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="stats-icon text-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['overdue_tasks']); ?></h3>
            <p class="text-muted mb-0">Terlambat</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Tugas Saya</h5>
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
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Tugas Terbaru</h5>
                <a href="/todo-list-app/user/tugas/" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_tasks)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Belum ada tugas</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_tasks as $task): ?>
                        <div class="task-card card mb-2 p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($task['judul']); ?></h6>
                                    <div class="d-flex gap-2 mb-1">
                                        <?php if ($task['nama_kategori']): ?>
                                            <span class="badge" style="background-color: <?php echo $task['kategori_warna']; ?>">
                                                <?php echo htmlspecialchars($task['nama_kategori']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($task['nama_prioritas']): ?>
                                            <span class="badge" style="background-color: <?php echo $task['prioritas_warna']; ?>">
                                                <?php echo htmlspecialchars($task['nama_prioritas']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($task['tanggal_jatuh_tempo']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($task['tanggal_jatuh_tempo'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge" style="background-color: <?php echo $task['status_warna']; ?>">
                                    <?php echo htmlspecialchars($task['nama_status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <a href="/todo-list-app/user/tugas/tambah.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Tambah Tugas Baru
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="/todo-list-app/user/tugas/?status=pending" class="btn btn-outline-warning w-100">
                            <i class="fas fa-clock me-2"></i>Lihat Tugas Pending
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="/todo-list-app/user/tugas/?status=overdue" class="btn btn-outline-danger w-100">
                            <i class="fas fa-exclamation-triangle me-2"></i>Lihat Tugas Terlambat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
