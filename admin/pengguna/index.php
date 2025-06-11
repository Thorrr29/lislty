<?php
$page_title = 'Kelola Pengguna';
require_once '../../auth/auth_check.php';
checkAuth('admin');

$pdo = getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Don't allow deleting admin users
    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user && $user['role_id'] != 1) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = 'Pengguna berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus pengguna!';
        }
    } else {
        $_SESSION['error'] = 'Tidak dapat menghapus admin!';
    }
    header('Location: index.php');
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    $stmt = $pdo->prepare("UPDATE users SET status = CASE WHEN status = 'aktif' THEN 'nonaktif' ELSE 'aktif' END WHERE id = ? AND role_id != 1");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Status pengguna berhasil diubah!';
    } else {
        $_SESSION['error'] = 'Gagal mengubah status pengguna!';
    }
    header('Location: index.php');
    exit();
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query conditions
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.nama_lengkap LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($role_filter)) {
    $where_conditions[] = "u.role_id = ?";
    $params[] = $role_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "u.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM users u $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Get users with filters and pagination
$query = "
    SELECT u.*, r.nama_role,
           (SELECT COUNT(*) FROM tugas WHERE user_id = u.id) as total_tugas
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    $where_clause
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get roles for filter
$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Kelola Pengguna</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
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

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari username, email, nama..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="role" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo $role_filter == $role['id'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($role['nama_role']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?php echo $status_filter == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="nonaktif" <?php echo $status_filter == 'nonaktif' ? 'selected' : ''; ?>>Non-aktif</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync-alt me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- User isi -->
<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada pengguna ditemukan</h5>
                <p class="text-muted">Coba ubah filter pencarian atau tambahkan pengguna baru</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Total Tugas</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $start_number = ($page - 1) * $limit + 1;
                        foreach ($users as $index => $user): 
                        ?>
                            <tr>
                                <td><?php echo $start_number + $index; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['nama_role'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                        <?php echo ucfirst($user['nama_role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['status'] == 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo number_format($user['total_tugas']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['nama_role'] != 'admin'): ?>
                                            <button onclick="confirmToggleStatus('<?php echo $user['id']; ?>', '<?php echo $user['status']; ?>')" 
                                                    class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                                <i class="fas fa-toggle-<?php echo $user['status'] == 'aktif' ? 'on' : 'off'; ?>"></i>
                                            </button>
                                            <button onclick="confirmDelete('index.php?delete=<?php echo $user['id']; ?>')" 
                                                    class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '&role=' . $role_filter . '&status=' . $status_filter . '">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                <a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '&role=' . $role_filter . '&status=' . $status_filter . '">' . $i . '</a>
                            </li>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($search) . '&role=' . $role_filter . '&status=' . $status_filter . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="text-center text-muted">
                    <small>Menampilkan <?php echo count($users); ?> dari <?php echo $total_records; ?> pengguna</small>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>

<script>
function confirmToggleStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
    const message = `Apakah Anda yakin ingin mengubah status pengguna menjadi ${newStatus}?`;
    
    Swal.fire({
        title: 'Konfirmasi Ubah Status',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?toggle_status=${userId}`;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>
