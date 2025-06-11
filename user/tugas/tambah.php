<?php
$page_title = 'Tambah Tugas';
require_once '../../auth/auth_check.php';
checkAuth();

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

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
        $stmt = $pdo->prepare("INSERT INTO tugas (user_id, judul, deskripsi, kategori_id, prioritas_id, status_id, tanggal_mulai, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$user_id, $judul, $deskripsi, $kategori_id, $prioritas_id, $status_id, $tanggal_mulai, $tanggal_jatuh_tempo])) {
            $_SESSION['success'] = 'Tugas berhasil ditambahkan!';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal menambahkan tugas!';
        }
    }
}

$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$priorities = $pdo->query("SELECT * FROM prioritas ORDER BY level_prioritas DESC")->fetchAll();
$statuses = $pdo->query("SELECT * FROM status_tugas ORDER BY id")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus me-2"></i>Tambah Tugas</h2>
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
                               value="<?php echo htmlspecialchars($judul ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($kategori_id ?? '') == $category['id'] ? 'selected' : ''; ?>>
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
                                        <?php echo ($prioritas_id ?? '') == $priority['id'] ? 'selected' : ''; ?>>
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
                                        <?php echo ($status_id ?? 1) == $status['id'] ? 'selected' : ''; ?>>
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
                               value="<?php echo htmlspecialchars($tanggal_mulai ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" 
                               value="<?php echo htmlspecialchars($tanggal_jatuh_tempo ?? ''); ?>">
                    </div>
                </div>
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
