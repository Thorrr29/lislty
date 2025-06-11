<?php
$page_title = 'Tugas Saya';
require_once '../../auth/auth_check.php';
checkAuth();

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tugas WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $user_id])) {
        $_SESSION['success'] = 'Tugas berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus tugas!';
    }
    header('Location: index.php');
    exit();
}

if (isset($_GET['update_status'])) {
    $id = $_GET['update_status'];
    $status = $_GET['status'];
    
    $stmt = $pdo->prepare("UPDATE tugas SET status_id = ?, tanggal_selesai = ? WHERE id = ? AND user_id = ?");
    $tanggal_selesai = ($status == 3) ? date('Y-m-d') : null;
    
    if ($stmt->execute([$status, $tanggal_selesai, $id, $user_id])) {
        $_SESSION['success'] = 'Status tugas berhasil diupdate!';
    } else {
        $_SESSION['error'] = 'Gagal mengupdate status tugas!';
    }
    header('Location: index.php');
    exit();
}

$where_conditions = ["t.user_id = ?"];
$params = [$user_id];


if (isset($_GET['status']) && !empty($_GET['status'])) {
    if ($_GET['status'] == 'pending') {
        $where_conditions[] = "t.status_id != 3";
    } elseif ($_GET['status'] == 'completed') {
        $where_conditions[] = "t.status_id = 3";
    } elseif ($_GET['status'] == 'overdue') {
        $where_conditions[] = "t.tanggal_jatuh_tempo < CURDATE() AND t.status_id != 3";
    } else {
        $where_conditions[] = "t.status_id = ?";
        $params[] = $_GET['status'];
    }
}

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $where_conditions[] = "t.kategori_id = ?";
    $params[] = $_GET['kategori'];
}

if (isset($_GET['prioritas']) && !empty($_GET['prioritas'])) {
    $where_conditions[] = "t.prioritas_id = ?";
    $params[] = $_GET['prioritas'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(t.judul LIKE ? OR t.deskripsi LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);

$query = "
    SELECT t.*, k.nama_kategori, k.warna as kategori_warna, 
           p.nama_prioritas, p.warna as prioritas_warna, p.level_prioritas,
           s.nama_status, s.warna as status_warna
    FROM tugas t
    LEFT JOIN kategori k ON t.kategori_id = k.id
    LEFT JOIN prioritas p ON t.prioritas_id = p.id
    LEFT JOIN status_tugas s ON t.status_id = s.id
    WHERE $where_clause
    ORDER BY 
        CASE WHEN t.tanggal_jatuh_tempo < CURDATE() AND t.status_id != 3 THEN 0 ELSE 1 END,
        p.level_prioritas DESC,
        t.tanggal_jatuh_tempo ASC,
        t.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Get filter options
$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$priorities = $pdo->query("SELECT * FROM prioritas ORDER BY level_prioritas DESC")->fetchAll();
$statuses = $pdo->query("SELECT * FROM status_tugas ORDER BY id")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i>Tugas Saya</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Tugas
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Tugas</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                       placeholder="Judul atau deskripsi...">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Belum Selesai</option>
                    <option value="completed" <?php echo ($_GET['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="overdue" <?php echo ($_GET['status'] ?? '') == 'overdue' ? 'selected' : ''; ?>>Terlambat</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>" 
                                <?php echo ($_GET['status'] ?? '') == $status['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['nama_status']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-select" id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($_GET['kategori'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="prioritas" class="form-label">Prioritas</label>
                <select class="form-select" id="prioritas" name="prioritas">
                    <option value="">Semua Prioritas</option>
                    <?php foreach ($priorities as $priority): ?>
                        <option value="<?php echo $priority['id']; ?>" 
                                <?php echo ($_GET['prioritas'] ?? '') == $priority['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($priority['nama_prioritas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($tasks)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada tugas ditemukan</h5>
                    <p class="text-muted">Klik tombol "Tambah Tugas" untuk membuat tugas baru</p>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Tugas
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <?php
            $is_overdue = $task['tanggal_jatuh_tempo'] && $task['tanggal_jatuh_tempo'] < date('Y-m-d') && $task['status_id'] != 3;
            $priority_class = '';
            if ($task['level_prioritas'] == 4) $priority_class = 'priority-high';
            elseif ($task['level_prioritas'] == 3) $priority_class = 'priority-medium';
            elseif ($task['level_prioritas'] <= 2) $priority_class = 'priority-low';
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card task-card <?php echo $priority_class; ?> <?php echo $is_overdue ? 'border-danger' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($task['judul']); ?></h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="edit.php?id=<?php echo $task['id']; ?>">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a></li>
                                    <?php if ($task['status_id'] != 3): ?>
                                        <li><a class="dropdown-item text-success" href="?update_status=<?php echo $task['id']; ?>&status=3">
                                            <i class="fas fa-check me-2"></i>Tandai Selesai
                                        </a></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item text-warning" href="?update_status=<?php echo $task['id']; ?>&status=2">
                                            <i class="fas fa-undo me-2"></i>Tandai Belum Selesai
                                        </a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('?delete=<?php echo $task['id']; ?>')">
                                        <i class="fas fa-trash me-2"></i>Hapus
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <?php if ($task['deskripsi']): ?>
                            <p class="card-text text-muted small mb-2"><?php echo nl2br(htmlspecialchars(substr($task['deskripsi'], 0, 100))); ?><?php echo strlen($task['deskripsi']) > 100 ? '...' : ''; ?></p>
                        <?php endif; ?>
                        
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <?php if ($task['nama_kategori']): ?>
                                <span class="badge" style="background-color: <?php echo $task['kategori_warna']; ?>">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($task['nama_kategori']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($task['nama_prioritas']): ?>
                                <span class="badge" style="background-color: <?php echo $task['prioritas_warna']; ?>">
                                    <i class="fas fa-exclamation me-1"></i><?php echo htmlspecialchars($task['nama_prioritas']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="badge" style="background-color: <?php echo $task['status_warna']; ?>">
                                <?php echo htmlspecialchars($task['nama_status']); ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <div>
                                <?php if ($task['tanggal_mulai']): ?>
                                    <div><i class="fas fa-play me-1"></i><?php echo date('d/m/Y', strtotime($task['tanggal_mulai'])); ?></div>
                                <?php endif; ?>
                                <?php if ($task['tanggal_jatuh_tempo']): ?>
                                    <div class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                        <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($task['tanggal_jatuh_tempo'])); ?>
                                        <?php if ($is_overdue): ?>
                                            <i class="fas fa-exclamation-triangle ms-1"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <div>Dibuat: <?php echo date('d/m/Y', strtotime($task['created_at'])); ?></div>
                                <?php if ($task['tanggal_selesai']): ?>
                                    <div class="text-success">Selesai: <?php echo date('d/m/Y', strtotime($task['tanggal_selesai'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
