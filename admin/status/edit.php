<?php
$page_title = 'Edit Status';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();
$error = '';
$success = '';

// status ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// fetch status data
$stmt = $pdo->prepare("SELECT * FROM status_tugas WHERE id = ?");
$stmt->execute([$id]);
$status = $stmt->fetch();

if (!$status) {
    $_SESSION['error'] = 'Status tidak ditemukan!';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_status = trim($_POST['nama_status']);
    $deskripsi = trim($_POST['deskripsi']);
    $warna = $_POST['warna'];
    
    if (empty($nama_status)) {
        $error = 'Nama status harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM status_tugas WHERE nama_status = ? AND id != ?");
        $stmt->execute([$nama_status, $id]);
        
        if ($stmt->fetch()) {
            $error = 'Nama status sudah ada!';
        } else {
            $stmt = $pdo->prepare("UPDATE status_tugas SET nama_status = ?, deskripsi = ?, warna = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([$nama_status, $deskripsi, $warna, $id])) {
                $_SESSION['success'] = 'Status berhasil diupdate!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Gagal mengupdate status!';
            }
        }
    }
} else {
    $nama_status = $status['nama_status'];
    $deskripsi = $status['deskripsi'];
    $warna = $status['warna'];
}

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Status</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="nama_status" class="form-label">Nama Status <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_status" name="nama_status" 
                       value="<?php echo htmlspecialchars($nama_status); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?php echo htmlspecialchars($deskripsi); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="warna" class="form-label">Warna</label>
                <input type="color" class="form-control form-control-color" id="warna" name="warna" 
                       value="<?php echo htmlspecialchars($warna); ?>">
            </div>
            
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Dibuat: <?php echo date('d F Y H:i', strtotime($status['created_at'])); ?>
                    <?php if ($status['updated_at'] != $status['created_at']): ?>
                        | Diupdate: <?php echo date('d F Y H:i', strtotime($status['updated_at'])); ?>
                    <?php endif; ?>
                </small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
