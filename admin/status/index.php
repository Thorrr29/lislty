<?php
$page_title = 'Kelola Status';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();

// delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM status_tugas WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Status berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus status!';
    }
    header('Location: index.php');
    exit();
}

// fetch all status
$stmt = $pdo->query("SELECT * FROM status_tugas ORDER BY nama_status ASC");
$statuses = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tasks me-2"></i>Kelola Status</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Status
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

<div class="card">
    <div class="card-body">
        <?php if (empty($statuses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada status</h5>
                <p class="text-muted">Klik tombol "Tambah Status" untuk menambah status baru</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Status</th>
                            <th>Deskripsi</th>
                            <th>Warna</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statuses as $index => $status): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($status['nama_status']); ?></strong></td>
                                <td><?php echo htmlspecialchars($status['deskripsi'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $status['warna']; ?>; color: white;">
                                        <?php echo $status['warna']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($status['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $status['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('index.php?delete=<?php echo $status['id']; ?>')" 
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
