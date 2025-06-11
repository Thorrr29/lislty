<?php
$page_title = 'Edit Kategori';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();
$error = '';
$success = '';

//  kategori ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// kategori data
$stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['error'] = 'Kategori tidak ditemukan!';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    $warna = $_POST['warna'];
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi!';
    } else {

        $stmt = $pdo->prepare("SELECT id FROM kategori WHERE nama_kategori = ? AND id != ?");
        $stmt->execute([$nama_kategori, $id]);
        
        if ($stmt->fetch()) {
            $error = 'Nama kategori sudah ada!';
        } else {
            $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ?, warna = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([$nama_kategori, $deskripsi, $warna, $id])) {
                $_SESSION['success'] = 'Kategori berhasil diupdate!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Gagal mengupdate kategori!';
            }
        }
    }
} else {
    $nama_kategori = $category['nama_kategori'];
    $deskripsi = $category['deskripsi'];
    $warna = $category['warna'];
}

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Kategori</h2>
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
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                               value="<?php echo htmlspecialchars($nama_kategori); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="warna" class="form-label">Warna</label>
                        <input type="color" class="form-control form-control-color" id="warna" name="warna" 
                               value="<?php echo htmlspecialchars($warna); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?php echo htmlspecialchars($deskripsi); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Dibuat: <?php echo date('d F Y H:i', strtotime($category['created_at'])); ?>
                    <?php if ($category['updated_at'] != $category['created_at']): ?>
                        | Diupdate: <?php echo date('d F Y H:i', strtotime($category['updated_at'])); ?>
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
