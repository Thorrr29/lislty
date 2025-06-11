<?php
$page_title = 'Edit Tugas';
require_once '../../auth/auth_check.php';
checkAuth();

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM tugas WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['error'] = 'Tugas tidak ditemukan!';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori_id = !empty($_POST['kategori_id']) ? $_POST['kategori_id'] : null;
    $prioritas_id = !empty($_POST['prioritas_id']) ? $_POST['prioritas_id'] : null;
    $status_id = $_POST['status_id'];
    $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
    $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;
    
    if (empty($judul)) {
        $error = 'Judul tugas harus diisi!';
    } elseif ($tanggal_mulai && $tanggal_jatuh_tempo && $tanggal_mulai > $tanggal_jatuh_tempo) {
        $error = 'Tanggal mulai tidak boleh lebih dari tanggal jatuh tempo!';
    } else {
        $tanggal_selesai = ($status_id == 3) ? date('Y-m-d') : null;
        
        $stmt = $pdo->prepare("UPDATE tugas SET judul = ?, deskripsi = ?, kategori_id = ?, prioritas_id = ?, status_id = ?, tanggal_mulai = ?, tanggal_jatuh_tempo = ?, tanggal_selesai = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$judul, $deskripsi, $kategori_id, $prioritas_id, $status_id, $tanggal_mulai, $tanggal_jatuh_tempo, $tanggal_selesai, $id, $user_id])) {
            $_SESSION['success'] = 'Tugas berhasil diupdate!';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal mengupdate tugas!';
        }
    }
} else {
    $judul = $task['judul'];
    $deskripsi = $task['deskripsi'];
    $kategori_id = $task['kategori_id'];
    $prioritas_id = $task['prioritas_id'];
    $status_id = $task['status_id'];
    $tanggal_mulai = $task['tanggal_mulai'];
    $tanggal_jatuh_tempo = $task['tanggal_jatuh_tempo'];
}


$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$priorities = $pdo->query("SELECT * FROM prioritas ORDER BY level_prioritas DESC")->fetchAll();
$statuses = $pdo->query("SELECT * FROM status_tugas ORDER BY id")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Tugas</h2>
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
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" 
                               value="<?php echo htmlspecialchars($judul); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($deskripsi); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $kategori_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prioritas_id" class="form-label">Prioritas</label>
                        <select class="form-select" id="prioritas_id" name="prioritas_id">
                            <option value="">Pilih Prioritas</option>
                            <?php foreach ($priorities as $priority): ?>
                                <option value="<?php echo $priority['id']; ?>" 
                                        <?php echo $prioritas_id == $priority['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($priority['nama_prioritas']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Status</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['id']; ?>" 
                                        <?php echo $status_id == $status['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['nama_status']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                               value="<?php echo htmlspecialchars($tanggal_mulai); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" 
                               value="<?php echo htmlspecialchars($tanggal_jatuh_tempo); ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Dibuat: <?php echo date('d F Y H:i', strtotime($task['created_at'])); ?>
                    <?php if ($task['updated_at'] != $task['created_at']): ?>
                        | Diupdate: <?php echo date('d F Y H:i', strtotime($task['updated_at'])); ?>
                    <?php endif; ?>
                    <?php if ($task['tanggal_selesai']): ?>
                        | Selesai: <?php echo date('d F Y', strtotime($task['tanggal_selesai'])); ?>
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
