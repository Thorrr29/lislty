<?php
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        $pdo = getConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username atau email sudah digunakan!';
        } else {
            //  user abru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, nama_lengkap, role_id) VALUES (?, ?, ?, ?, 2)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $nama_lengkap])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Listly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="register-title">Daftar Akun</h1>
                <p class="register-subtitle">Buat akun baru untuk menggunakan Listly</p>
            </div>
            
            <div class="register-form">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" autocomplete="off">
                    <div class="form-floating">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                               placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($nama_lengkap ?? ''); ?>" required>
                        <label for="nama_lengkap">Nama Lengkap</label>
                    </div>
                    
                    <div class="form-floating">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                        <label for="username">Username</label>
                    </div>
                    
                    <div class="form-floating">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        <label for="email">Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <label for="password">Password</label>
                    </div>
                    <div class="form-text">Minimal 6 karakter</div>
                    
                    <div class="form-floating">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Konfirmasi Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <label for="confirm_password">Konfirmasi Password</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary register-btn w-100">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </button>
                </form>
            </div>
            
            <div class="register-footer">
                <p class="mb-0">Sudah punya akun? 
                    <a href="login.php" class="register-link">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                    
                    if (alert.classList.contains('alert-success')) {
                        window.location.href = 'login.php';
                    }
                }, 500);
            });
        }, 3000);
    </script>
</body>
</html>
