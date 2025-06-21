<?php
$page_title = 'Tambah Status';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_status = trim($_POST['nama_status']);
    $deskripsi = trim($_POST['deskripsi']);
    $warna = $_POST['warna'];
    
    if (empty($nama_status)) {
        $error = 'Nama status harus diisi!';
    } else {
        $pdo = getConnection();
        
        // Check if status name already exists
        $stmt = $pdo->prepare("SELECT id FROM status_tugas WHERE nama_status = ?");
        $stmt->execute([$nama_status]);
        
        if ($stmt->fetch()) {
            $error = 'Nama status sudah ada!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO status_tugas (nama_status, deskripsi, warna) VALUES (?, ?, ?)");
            if ($stmt->execute([$nama_status, $deskripsi, $warna])) {
                $_SESSION['success'] = 'Status berhasil ditambahkan!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Gagal menambahkan status!';
            }
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus me-2"></i>Tambah Status</h2>
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
                       value="<?php echo htmlspecialchars($nama_status ?? ''); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="warna" class="form-label">Warna</label>
                <input type="color" class="form-control form-control-color" id="warna" name="warna" 
                       value="<?php echo htmlspecialchars($warna ?? '#6c757d'); ?>">
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
