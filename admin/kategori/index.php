<?php
$page_title = 'Kelola Kategori';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();

//  delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Kategori berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus kategori!';
    }
    header('Location: index.php');
    exit();
}

// semua kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$categories = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tags me-2"></i>Kelola Kategori</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Kategori
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
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada kategori</h5>
                <p class="text-muted">Klik tombol "Tambah Kategori" untuk menambah kategori baru</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Warna</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $index => $category): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['nama_kategori']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($category['deskripsi'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $category['warna']; ?>; color: white;">
                                        <?php echo $category['warna']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('index.php?delete=<?php echo $category['id']; ?>')" 
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
