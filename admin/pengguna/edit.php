<?php
$page_title = 'Edit Pengguna';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();
$error = '';
$success = '';

// Get user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Get user data
$stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Pengguna tidak ditemukan!';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role_id = $_POST['role_id'];
    $status = $_POST['status'];
    
    // Validation
    if (empty($username) || empty($email) || empty($nama_lengkap)) {
        $error = 'Username, email, dan nama lengkap harus diisi!';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Check if username or email already exists (except current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $id]);
        
        if ($stmt->fetch()) {
            $error = 'Username atau email sudah digunakan!';
        } else {
            // Update user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, nama_lengkap = ?, role_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $params = [$username, $email, $hashed_password, $nama_lengkap, $role_id, $status, $id];
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, nama_lengkap = ?, role_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $params = [$username, $email, $nama_lengkap, $role_id, $status, $id];
            }
            
            if ($stmt->execute($params)) {
                $_SESSION['success'] = 'Pengguna berhasil diupdate!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Terjadi kesalahan saat mengupdate pengguna!';
            }
        }
    }
} else {
    // Pre-fill form with existing data
    $username = $user['username'];
    $email = $user['email'];
    $nama_lengkap = $user['nama_lengkap'];
    $role_id = $user['role_id'];
    $status = $user['status'];
}

// Get roles
$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit me-2"></i>Edit Pengguna</h2>
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
                        <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                               value="<?php echo htmlspecialchars($nama_lengkap); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['id']; ?>" 
                                                <?php echo $role_id == $role['id'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($role['nama_role']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif" <?php echo $status == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo $status == 'nonaktif' ? 'selected' : ''; ?>>Non-aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Terdaftar: <?php echo date('d F Y H:i', strtotime($user['created_at'])); ?>
                    <?php if ($user['updated_at'] != $user['created_at']): ?>
                        | Diupdate: <?php echo date('d F Y H:i', strtotime($user['updated_at'])); ?>
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
