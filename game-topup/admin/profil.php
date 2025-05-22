<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Admin profile data (dummy)
$admin = [
    'id' => 1,
    'username' => 'admin',
    'name' => 'Administrator',
    'email' => 'admin@diamondstore.com',
    'role' => 'Super Admin',
    'last_login' => '2024-05-21 14:30:45',
    'created_at' => '2023-01-01 00:00:00',
    'avatar' => 'https://ui-avatars.com/api/?name=Administrator&background=4361ee&color=fff&size=128'
];

// Handle password change
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'Semua field harus diisi';
    } else if ($new_password !== $confirm_password) {
        $error_message = 'Password baru dan konfirmasi password tidak cocok';
    } else if (strlen($new_password) < 6) {
        $error_message = 'Password baru harus minimal 6 karakter';
    } else if ($current_password !== 'admin123') { // Dummy validation
        $error_message = 'Password saat ini tidak valid';
    } else {
        // Password change successful (dummy)
        $success_message = 'Password berhasil diubah';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profil Admin</h1>
                </div>
                
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <img src="<?php echo $admin['avatar']; ?>" alt="Admin Avatar" class="rounded-circle img-thumbnail mb-3" width="150">
                                <h4><?php echo $admin['name']; ?></h4>
                                <p class="text-muted"><?php echo $admin['role']; ?></p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#changeAvatarModal">
                                        <i class="bi bi-camera"></i> Ganti Foto
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-clock-history text-muted"></i>
                                        <small class="text-muted">Login terakhir: <?php echo date('d/m/Y H:i', strtotime($admin['last_login'])); ?></small>
                                    </div>
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Informasi Akun</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="row mb-3">
                                        <label for="username" class="col-sm-3 col-form-label">Username</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="username" value="<?php echo $admin['username']; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-3 col-form-label">Nama Lengkap</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" value="<?php echo $admin['name']; ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="email" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="email" value="<?php echo $admin['email']; ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="role" class="col-sm-3 col-form-label">Role</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="role" value="<?php echo $admin['role']; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="created" class="col-sm-3 col-form-label">Tanggal Dibuat</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="created" value="<?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Ubah Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Saat Ini</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" name="change_password" class="btn btn-primary">Ubah Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Change Avatar Modal -->
    <div class="modal fade" id="changeAvatarModal" tabindex="-1" aria-labelledby="changeAvatarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeAvatarModalLabel">Ganti Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="avatarFile" class="form-label">Pilih Foto</label>
                        <input class="form-control" type="file" id="avatarFile">
                    </div>
                    <div class="text-center mt-4">
                        <img src="<?php echo $admin['avatar']; ?>" alt="Preview" class="img-thumbnail" id="avatarPreview" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview avatar image
        document.getElementById('avatarFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
