<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Process form submission for adding new game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $nama_game = sanitize($_POST['nama_game']);
    $developer = sanitize($_POST['developer']);
    
    $query = "INSERT INTO Game (nama_game, developer) VALUES ('$nama_game', '$developer')";
    if(mysqli_query($conn, $query)) {
        $success_message = "Game baru berhasil ditambahkan.";
    } else {
        $error_message = "Gagal menambahkan game: " . mysqli_error($conn);
    }
    
    // Redirect to refresh the page
    redirect('games.php');
}

// Process form submission for updating game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_game'])) {
    $id_game = (int)$_POST['id_game'];
    $nama_game = sanitize($_POST['nama_game']);
    $developer = sanitize($_POST['developer']);
    
    $query = "UPDATE Game SET nama_game = '$nama_game', developer = '$developer' WHERE id_game = $id_game";
    if(mysqli_query($conn, $query)) {
        $success_message = "Game berhasil diperbarui.";
    } else {
        $error_message = "Gagal memperbarui game: " . mysqli_error($conn);
    }
    
    // Redirect to refresh the page
    redirect('games.php');
}

// Process game deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game'])) {
    $id_game = (int)$_POST['id_game'];
    
    // Check if game has packages
    $check_query = "SELECT COUNT(*) as count FROM PaketDiamond WHERE id_game = $id_game";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        $error_message = "Game tidak dapat dihapus karena memiliki paket diamond yang terkait.";
    } else {
        $query = "DELETE FROM Game WHERE id_game = $id_game";
        if(mysqli_query($conn, $query)) {
            $success_message = "Game berhasil dihapus.";
        } else {
            $error_message = "Gagal menghapus game: " . mysqli_error($conn);
        }
        redirect('games.php');
    }
}

// Get all games
$query = "SELECT * FROM Game ORDER BY nama_game";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Game | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Game</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGameModal">
                        <i class="bi bi-plus-lg"></i> Tambah Game
                    </button>
                </div>
                
                <!-- Games Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Game</th>
                                        <th>Developer</th>
                                        <th>Jumlah Paket</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($game = mysqli_fetch_assoc($result)): ?>
                                        <?php
                                        // Get package count for this game
                                        $id_game = $game['id_game'];
                                        $package_query = "SELECT COUNT(*) as count FROM PaketDiamond WHERE id_game = $id_game";
                                        $package_result = mysqli_query($conn, $package_query);
                                        $package_row = mysqli_fetch_assoc($package_result);
                                        $package_count = $package_row['count'];
                                        ?>
                                        <tr>
                                            <td><?php echo $game['id_game']; ?></td>
                                            <td><?php echo $game['nama_game']; ?></td>
                                            <td><?php echo $game['developer']; ?></td>
                                            <td><?php echo $package_count; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="packages.php?game_id=<?php echo $game['id_game']; ?>" class="btn btn-outline-primary" title="Lihat Paket">
                                                        <i class="bi bi-gem"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#editGameModal<?php echo $game['id_game']; ?>" title="Edit Game">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteGameModal<?php echo $game['id_game']; ?>" title="Hapus Game">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Edit Game Modal -->
                                                <div class="modal fade" id="editGameModal<?php echo $game['id_game']; ?>" tabindex="-1" aria-labelledby="editGameModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editGameModalLabel">Edit Game</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id_game" value="<?php echo $game['id_game']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="nama_game" class="form-label">Nama Game</label>
                                                                        <input type="text" class="form-control" id="nama_game" name="nama_game" value="<?php echo $game['nama_game']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="developer" class="form-label">Developer</label>
                                                                        <input type="text" class="form-control" id="developer" name="developer" value="<?php echo $game['developer']; ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" name="update_game" class="btn btn-primary">Simpan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Game Modal -->
                                                <div class="modal fade" id="deleteGameModal<?php echo $game['id_game']; ?>" tabindex="-1" aria-labelledby="deleteGameModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteGameModalLabel">Konfirmasi Hapus</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah Anda yakin ingin menghapus game <strong><?php echo $game['nama_game']; ?></strong>?</p>
                                                                <?php if ($package_count > 0): ?>
                                                                <div class="alert alert-warning">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> Game ini memiliki <?php echo $package_count; ?> paket diamond yang terkait. Hapus paket diamond terlebih dahulu.
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <form method="post" action="">
                                                                    <input type="hidden" name="id_game" value="<?php echo $game['id_game']; ?>">
                                                                    <button type="submit" name="delete_game" class="btn btn-danger" <?php echo $package_count > 0 ? 'disabled' : ''; ?>>Hapus</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">Tidak ada game yang ditemukan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add Game Modal -->
    <div class="modal fade" id="addGameModal" tabindex="-1" aria-labelledby="addGameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGameModalLabel">Tambah Game Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_game" class="form-label">Nama Game</label>
                            <input type="text" class="form-control" id="nama_game" name="nama_game" required>
                        </div>
                        <div class="mb-3">
                            <label for="developer" class="form-label">Developer</label>
                            <input type="text" class="form-control" id="developer" name="developer" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_game" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebarMenu').classList.toggle('show');
        });
    </script>
</body>
</html>
