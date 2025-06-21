<?php
$page_title = 'Kelola Prioritas';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();

// delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM prioritas WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Prioritas berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus prioritas!';
    }
    header('Location: index.php');
    exit();
}

// fetch all prioritas
$stmt = $pdo->query("SELECT * FROM prioritas ORDER BY level_prioritas ASC");
$priorities = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-flag me-2"></i>Kelola Prioritas</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Prioritas
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
        <?php if (empty($priorities)): ?>
            <div class="text-center py-5">
                <i class="fas fa-flag fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada prioritas</h5>
                <p class="text-muted">Klik tombol "Tambah Prioritas" untuk menambah prioritas baru</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Prioritas</th>
                            <th>Level Prioritas</th>
                            <th>Warna</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($priorities as $index => $priority): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($priority['nama_prioritas']); ?></strong></td>
                                <td><?php echo htmlspecialchars($priority['level_prioritas']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $priority['warna']; ?>; color: white;">
                                        <?php echo $priority['warna']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($priority['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $priority['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('index.php?delete=<?php echo $priority['id']; ?>')" 
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
