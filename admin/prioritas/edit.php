<?php
$page_title = 'Edit Prioritas';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();
$error = '';
$success = '';

// priority ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// fetch priority data
$stmt = $pdo->prepare("SELECT * FROM prioritas WHERE id = ?");
$stmt->execute([$id]);
$priority = $stmt->fetch();

if (!$priority) {
    $_SESSION['error'] = 'Prioritas tidak ditemukan!';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_prioritas = trim($_POST['nama_prioritas']);
    $level_prioritas = intval($_POST['level_prioritas']);
    $warna = $_POST['warna'];
    
    if (empty($nama_prioritas)) {
        $error = 'Nama prioritas harus diisi!';
    } elseif ($level_prioritas <= 0) {
        $error = 'Level prioritas harus berupa angka positif!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM prioritas WHERE nama_prioritas = ? AND id != ?");
        $stmt->execute([$nama_prioritas, $id]);
        
        if ($stmt->fetch()) {
            $error = 'Nama prioritas sudah ada!';
        } else {
            $stmt = $pdo->prepare("UPDATE prioritas SET nama_prioritas = ?, level_prioritas = ?, warna = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([$nama_prioritas, $level_prioritas, $warna, $id])) {
                $_SESSION['success'] = 'Prioritas berhasil diupdate!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Gagal mengupdate prioritas!';
            }
        }
    }
} else {
    $nama_prioritas = $priority['nama_prioritas'];
    $level_prioritas = $priority['level_prioritas'];
    $warna = $priority['warna'];
}

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Prioritas</h2>
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
                <label for="nama_prioritas" class="form-label">Nama Prioritas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_prioritas" name="nama_prioritas" 
                       value="<?php echo htmlspecialchars($nama_prioritas); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="level_prioritas" class="form-label">Level Prioritas <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="level_prioritas" name="level_prioritas" min="1" 
                       value="<?php echo htmlspecialchars($level_prioritas); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="warna" class="form-label">Warna</label>
                <input type="color" class="form-control form-control-color" id="warna" name="warna" 
                       value="<?php echo htmlspecialchars($warna); ?>">
            </div>
            
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Dibuat: <?php echo date('d F Y H:i', strtotime($priority['created_at'])); ?>
                    <?php if ($priority['updated_at'] != $priority['created_at']): ?>
                        | Diupdate: <?php echo date('d F Y H:i', strtotime($priority['updated_at'])); ?>
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
