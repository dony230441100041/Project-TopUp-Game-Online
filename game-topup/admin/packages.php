<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include '../config/database.php';

// Check if game_id is provided
$game_id = isset($_GET['game_id']) ? $_GET['game_id'] : null;

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to get game details
function getGameDetails($conn, $game_id) {
    if (!tableExists($conn, 'Game')) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM Game WHERE id_game = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Function to get all packages for a game
function getPackages($conn, $game_id = null) {
    if (!tableExists($conn, 'PaketDiamond') || !tableExists($conn, 'Game')) {
        return [];
    }
    
    if ($game_id) {
        $stmt = $conn->prepare("SELECT p.*, g.nama_game as game_name FROM PaketDiamond p 
                               JOIN Game g ON p.id_game = g.id_game 
                               WHERE p.id_game = ? 
                               ORDER BY p.harga ASC");
        $stmt->bind_param("i", $game_id);
    } else {
        $stmt = $conn->prepare("SELECT p.*, g.nama_game as game_name FROM PaketDiamond p 
                               JOIN Game g ON p.id_game = g.id_game 
                               ORDER BY g.nama_game ASC, p.harga ASC");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        // Map field names to match our expected structure
        $packages[] = [
            'id' => $row['id_paket'],
            'game_id' => $row['id_game'],
            'name' => $row['nama_paket'],
            'description' => $row['jumlah_diamond'] . ' Diamonds',
            'price' => $row['harga'],
            'game_name' => $row['game_name']
        ];
    }
    
    return $packages;
}

// Function to get all games
function getAllGames($conn) {
    if (!tableExists($conn, 'Game')) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT * FROM Game ORDER BY nama_game ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $games = [];
    while ($row = $result->fetch_assoc()) {
        // Map field names to match our expected structure
        $games[] = [
            'id' => $row['id_game'],
            'name' => $row['nama_game']
        ];
    }
    
    return $games;
}

// Handle form submission for adding a new package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
    $package_game_id = $_POST['game_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Extract diamond amount from description
    $jumlah_diamond = intval($description);
    
    $stmt = $conn->prepare("INSERT INTO PaketDiamond (id_game, nama_paket, jumlah_diamond, harga) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isid", $package_game_id, $name, $jumlah_diamond, $price);
    
    if ($stmt->execute()) {
        $success_message = "Paket berhasil ditambahkan!";
        // Redirect to maintain the game_id in URL
        header("Location: packages.php" . ($game_id ? "?game_id=$game_id" : "") . "&success=1");
        exit();
    } else {
        $error_message = "Gagal menambahkan paket: " . $conn->error;
    }
}

// Handle package deletion
if (isset($_GET['delete']) && isset($_GET['package_id'])) {
    $package_id = $_GET['package_id'];
    
    $stmt = $conn->prepare("DELETE FROM PaketDiamond WHERE id_paket = ?");
    $stmt->bind_param("i", $package_id);
    
    if ($stmt->execute()) {
        $success_message = "Paket berhasil dihapus!";
        // Redirect to maintain the game_id in URL
        header("Location: packages.php" . ($game_id ? "?game_id=$game_id" : "") . "&deleted=1");
        exit();
    } else {
        $error_message = "Gagal menghapus paket: " . $conn->error;
    }
}

// Handle package update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_package'])) {
    $package_id = $_POST['package_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Extract diamond amount from description
    $jumlah_diamond = intval($description);
    
    $stmt = $conn->prepare("UPDATE PaketDiamond SET nama_paket = ?, jumlah_diamond = ?, harga = ? WHERE id_paket = ?");
    $stmt->bind_param("sidi", $name, $jumlah_diamond, $price, $package_id);
    
    if ($stmt->execute()) {
        $success_message = "Paket berhasil diperbarui!";
        // Redirect to maintain the game_id in URL
        header("Location: packages.php" . ($game_id ? "?game_id=$game_id" : "") . "&updated=1");
        exit();
    } else {
        $error_message = "Gagal memperbarui paket: " . $conn->error;
    }
}

// Get game details if game_id is provided
$game = null;
if ($game_id) {
    $game = getGameDetails($conn, $game_id);
    if (!$game) {
        $error_message = "Game tidak ditemukan!";
    }
}

// Get packages
$packages = getPackages($conn, $game_id);

// Get all games for the dropdown
$games = getAllGames($conn);

// Page title
$page_title = $game ? "Paket Diamond: " . $game['nama_game'] : "Semua Paket Diamond";

// Success message from redirect
if (isset($_GET['success'])) {
    $success_message = "Paket berhasil ditambahkan!";
}
if (isset($_GET['deleted'])) {
    $success_message = "Paket berhasil dihapus!";
}
if (isset($_GET['updated'])) {
    $success_message = "Paket berhasil diperbarui!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            background-color: #f5f7fb;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1e2233;
            color: #fff;
            flex-shrink: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }
        
        .app-content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #f5f7fb;
        }
        
        .package-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .package-header {
            background-color: #ff7700;
            color: white;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .package-body {
            padding: 15px;
        }
        
        .package-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff7700;
        }
        
        .package-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        
        .game-filter {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .add-package-btn {
            margin-bottom: 20px;
            background-color: #ff7700;
            border-color: #ff7700;
        }
        
        .add-package-btn:hover {
            background-color: #e56a00;
            border-color: #e56a00;
        }
        
        .modal-header {
            background-color: #ff7700;
            color: white;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
        }
        
        .alert {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .nav-link.active {
            background-color: #ff7700;
            color: white;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 119, 0, 0.1);
        }
        
        .app-content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .app-content-headerText {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .app-content {
                margin-left: 0;
            }
            
            .app-content-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-package-btn {
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <div class="app-content">
            <div class="app-content-header">
                <h1 class="app-content-headerText"><?php echo $page_title; ?></h1>
                <button class="btn btn-primary add-package-btn" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Paket Baru
                </button>
            </div>
            
            <!-- Alert Container -->
            <?php if (isset($success_message) || isset($error_message)): ?>
            <div class="alert-container">
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Game Filter -->
            <div class="game-filter">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-3 mb-md-0">Filter berdasarkan Game:</h5>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="gameFilter" onchange="filterByGame(this.value)">
                            <option value="">Semua Game</option>
                            <?php foreach ($games as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo ($game_id == $g['id']) ? 'selected' : ''; ?>>
                                <?php echo $g['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Packages Display -->
            <div class="row g-4">
                <?php if (empty($packages)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo $game ? "Belum ada paket untuk game ini." : "Belum ada paket yang tersedia."; ?>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($packages as $package): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card package-card">
                            <div class="package-header">
                                <h5 class="card-title mb-0"><?php echo $package['name']; ?></h5>
                                <small><?php echo $package['game_name']; ?></small>
                            </div>
                            <div class="package-body">
                                <p class="card-text"><?php echo $package['description']; ?></p>
                                <div class="package-price">
                                    Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                                </div>
                                <div class="package-actions">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPackageModal" 
                                            data-package-id="<?php echo $package['id']; ?>"
                                            data-package-name="<?php echo $package['name']; ?>"
                                            data-package-description="<?php echo $package['description']; ?>"
                                            data-package-price="<?php echo $package['price']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deletePackageModal"
                                            data-package-id="<?php echo $package['id']; ?>"
                                            data-package-name="<?php echo $package['name']; ?>">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1" aria-labelledby="addPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPackageModalLabel">Tambah Paket Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="game_id" class="form-label">Game</label>
                            <select class="form-select" id="game_id" name="game_id" required>
                                <?php foreach ($games as $g): ?>
                                <option value="<?php echo $g['id']; ?>" <?php echo ($game_id == $g['id']) ? 'selected' : ''; ?>>
                                    <?php echo $g['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Jumlah Diamond</label>
                            <input type="number" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="1000" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_package" class="btn btn-primary" style="background-color: #ff7700; border-color: #ff7700;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPackageModalLabel">Edit Paket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="edit_package_id" name="package_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Jumlah Diamond</label>
                            <input type="number" class="form-control" id="edit_description" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" id="edit_price" name="price" min="0" step="1000" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_package" class="btn btn-primary" style="background-color: #ff7700; border-color: #ff7700;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Package Modal -->
    <div class="modal fade" id="deletePackageModal" tabindex="-1" aria-labelledby="deletePackageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deletePackageModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus paket <strong id="delete_package_name"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Tindakan ini tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="delete_package_link" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to filter packages by game
        function filterByGame(gameId) {
            if (gameId) {
                window.location.href = 'packages.php?game_id=' + gameId;
            } else {
                window.location.href = 'packages.php';
            }
        }
        
        // Handle edit package modal
        const editPackageModal = document.getElementById('editPackageModal');
        if (editPackageModal) {
            editPackageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const packageId = button.getAttribute('data-package-id');
                const packageName = button.getAttribute('data-package-name');
                const packageDescription = button.getAttribute('data-package-description');
                const packagePrice = button.getAttribute('data-package-price');
                
                const modalPackageId = editPackageModal.querySelector('#edit_package_id');
                const modalPackageName = editPackageModal.querySelector('#edit_name');
                const modalPackageDescription = editPackageModal.querySelector('#edit_description');
                const modalPackagePrice = editPackageModal.querySelector('#edit_price');
                
                modalPackageId.value = packageId;
                modalPackageName.value = packageName;
                
                // Extract diamond amount from description (e.g., "100 Diamonds" -> 100)
                const diamondMatch = packageDescription.match(/(\d+)/);
                modalPackageDescription.value = diamondMatch ? diamondMatch[1] : '';
                
                modalPackagePrice.value = packagePrice;
            });
        }
        
        // Handle delete package modal
        const deletePackageModal = document.getElementById('deletePackageModal');
        if (deletePackageModal) {
            deletePackageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const packageId = button.getAttribute('data-package-id');
                const packageName = button.getAttribute('data-package-name');
                
                const modalPackageName = deletePackageModal.querySelector('#delete_package_name');
                const deleteLink = deletePackageModal.querySelector('#delete_package_link');
                
                modalPackageName.textContent = packageName;
                deleteLink.href = 'packages.php?delete=1&package_id=' + packageId + '<?php echo $game_id ? "&game_id=$game_id" : ""; ?>';
            });
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
