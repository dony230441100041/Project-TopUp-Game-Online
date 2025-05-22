<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';
// require 'functions.php';

// Check if game ID is provided
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    redirect('index.php');
}
$game_id = (int)$_GET['id'];

// Get game details using the function from functions.php
$game = getGameById($game_id);

if (!$game) {
    echo '<div class="alert alert-danger">Game tidak ditemukan.</div>';
    die("Game dengan ID $game_id tidak ditemukan.");
}

// Get game details
$stmt = $conn->prepare("SELECT * FROM Game WHERE id_game = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$game_result = $stmt->get_result();
$game = $game_result->fetch_assoc();
$stmt->close();

if (!$game) {
    echo '<div class="alert alert-danger">Game tidak ditemukan.</div>';
    die("Game dengan ID $game_id tidak ditemukan.");
}

// Get diamond packages for this game
$packages = getPackagesByGameId($game_id);

// Get diamond packages for this game
$stmt = $conn->prepare("SELECT * FROM PaketDiamond WHERE id_game = ? ORDER BY jumlah_diamond");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$packages_result = $stmt->get_result();
$stmt->close();

// Get related games
$stmt = $conn->prepare("SELECT * FROM Game WHERE id_game != ? ORDER BY RAND() LIMIT 4");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$related_result = $stmt->get_result();
$stmt->close();
// Game image mapping
// $game_images = [
//     'Mobile Legends' => 'https://blob.v0.dev/mobile-legends.jpg',
//     'Free Fire' => 'https://blob.v0.dev/free-fire.jpg',
//     'PUBG Mobile' => 'https://blob.v0.dev/pubg-mobile.jpg',
//     'Genshin Impact' => 'https://blob.v0.dev/genshin-impact.jpg',
//     'Clash of Clans' => 'https://blob.v0.dev/clash-of-clans.jpg'
// ];

// Game image mapping
$game_images = [
    'Mobile Legends' => 'https://images.unsplash.com/photo-1607995548153-ca92f9c657e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'Free Fire' => 'https://images.unsplash.com/photo-1630637833870-4ffc79a45799?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'Genshin Impact' => 'https://images.unsplash.com/photo-1635332497344-46bca744f311?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'PUBG Mobile' => 'https://images.unsplash.com/photo-1605492824689-4c26922e7410?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'Valorant' => 'https://images.unsplash.com/photo-1623775324247-9ca91c9c7293?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'
];


// $topup_options = [
//     [
//         'nama' => '50 Diamonds',
//         'harga' => 15000
//     ],
//     [
//         'nama' => '100 Diamonds',
//         'harga' => 30000
//     ],
//     [
//         'nama' => '300 Diamonds',
//         'harga' => 75000
//     ],
//     [
//         'nama' => '500 Diamonds',
//         'harga' => 120000
//     ],
//     [
//         'nama' => '1000 Diamonds',
//         'harga' => 230000
//     ],
// ];

// Make sure we have a game name, even if it's a fallback
$game_name = isset($game['nama_game']) && !empty($game['nama_game']) ? $game['nama_game'] : 'Game';

// Default image if game name not found in mapping
$game_image = isset($game_images[$game_name]) 
    ? $game_images[$game_name] 
    : "https://picsum.photos/800/400?random=" . $game_id;

// Default developer value if not set
$developer = isset($game['developer']) && !empty($game['developer']) ? $game['developer'] : 'Unknown';

// $game_image = isset($game) && isset($game['nama_game']) && isset($game_images[$game['nama_game']]) 
//     ? $game_images[$game['nama_game']] 
//     : "https://picsum.photos/800/400?random=" . $game_id;

// // Default developer value if not set
// $developer = isset($game['developer']) ? $game['developer'] : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up <?php echo isset($game['nama_game']) ? htmlspecialchars($game['nama_game']) : 'Game'; ?> | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <!-- <li class="breadcrumb-item"><a href="games.php">Games</a></li> -->
                <li class="breadcrumb-item active" aria-current="page">Games</li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($game_name); ?></li>
            </ol>
        </nav>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="<?php echo htmlspecialchars($game_image); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($game_name); ?>">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body p-4">
                            <h1 class="card-title"><?php echo htmlspecialchars($game_name); ?></h1>
                            <p class="card-text text-muted">Developer: <?php echo htmlspecialchars($developer); ?></p>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="ratings me-3">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-half text-warning"></i>
                                    <span class="ms-1">4.5</span>
                                </div>
                                <span class="badge bg-success">Populer</span>
                            </div>
                            
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <div>
                                    Pastikan ID game Anda sudah benar sebelum melakukan top up.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="gameId" class="form-label">ID Game</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="gameId" name="game_id" placeholder="Masukkan ID Game Anda" required>
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#idHelpModal">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </div>
                                <div class="form-text">Masukkan ID game Anda dengan benar</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="serverZone" class="form-label">Server/Zone ID</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="serverZone" name="server_zone" placeholder="Masukkan Server/Zone ID (opsional)">
                                </div>
                                <div class="form-text">Opsional, hanya untuk game tertentu</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h3 class="mb-0">Pilih Nominal</h3>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    <?php if (mysqli_num_rows($packages_result) > 0): ?>
                        <?php while ($package = mysqli_fetch_assoc($packages_result)): ?>
                        <div class="col">
                            <div class="card package-card h-100">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="package_id" id="package<?php echo $package['id_paket']; ?>" value="<?php echo $package['id_paket']; ?>">
                                        <label class="form-check-label w-100" for="package<?php echo $package['id_paket']; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold"><?php echo htmlspecialchars($package['nama_paket']); ?></span>
                                                <?php if ($package['id_paket'] % 3 == 0): ?>
                                                <span class="badge bg-danger">HOT</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-2 mb-3">
                                                <i class="bi bi-gem text-primary me-1"></i>
                                                <span class="fs-5"><?php echo number_format($package['jumlah_diamond']); ?></span>
                                                <span class="text-muted">Diamonds</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-primary fw-bold"><?php echo formatCurrency($package['harga']); ?></span>
                                                <?php if ($package['id_paket'] % 4 == 0): ?>
                                                <span class="text-danger small"><s>Rp <?php echo number_format($package['harga'] * 1.2); ?></s></span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Tidak ada paket diamond yang tersedia untuk game ini saat ini.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h3 class="mb-0">Pilih Pembayaran</h3>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    <div class="col">
                        <div class="card payment-method-card h-100">
                            <div class="card-body text-center">
                                <img src="https://picsum.photos/100/40?random=1" alt="DANA" class="img-fluid mb-2">
                                <h6 class="card-title">DANA</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card payment-method-card h-100">
                            <div class="card-body text-center">
                                <img src="https://picsum.photos/100/40?random=2" alt="OVO" class="img-fluid mb-2">
                                <h6 class="card-title">OVO</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card payment-method-card h-100">
                            <div class="card-body text-center">
                                <img src="https://picsum.photos/100/40?random=3" alt="GoPay" class="img-fluid mb-2">
                                <h6 class="card-title">GoPay</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card payment-method-card h-100">
                            <div class="card-body text-center">
                                <img src="https://picsum.photos/100/40?random=4" alt="ShopeePay" class="img-fluid mb-2">
                                <h6 class="card-title">ShopeePay</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2 mb-5">
            <button type="button" class="btn btn-primary btn-lg" onclick="processOrder()">Beli Sekarang</button>
        </div>
        
        <!-- Cara Top Up -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0">Cara Top Up <?php echo isset($game['nama_game']) ? $game['nama_game'] : 'Game'; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex mb-3">
                            <div class="step-number">1</div>
                            <div>
                                <h5>Masukkan ID Game</h5>
                                <p class="text-muted">Masukkan ID game <?php echo isset($game['nama_game']) ? $game['nama_game'] : 'Game'; ?> Anda dengan benar untuk memastikan diamond masuk ke akun yang tepat.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="step-number">2</div>
                            <div>
                                <h5>Pilih Nominal</h5>
                                <p class="text-muted">Pilih nominal diamond yang Anda inginkan sesuai dengan kebutuhan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex mb-3">
                            <div class="step-number">3</div>
                            <div>
                                <h5>Pilih Metode Pembayaran</h5>
                                <p class="text-muted">Pilih metode pembayaran yang tersedia (OVO, DANA, GoPay, dll).</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="step-number">4</div>
                            <div>
                                <h5>Selesaikan Pembayaran</h5>
                                <p class="text-muted">Selesaikan pembayaran dan diamond akan langsung masuk ke akun Anda.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Games -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">Game Lainnya</h3>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    <?php while ($related_game = mysqli_fetch_assoc($related_result)): 
                        $related_image = isset($related_game['nama_game']) && isset($game_images[$related_game['nama_game']]) 
                            ? $game_images[$related_game['nama_game']] 
                            : "https://picsum.photos/300/150?random=" . ($related_game['id_game'] + 20);
                    ?>
                    <div class="col">
                        <a href="game.php?id=<?php echo $related_game['id_game']; ?>" class="text-decoration-none">
                            <div class="card h-100 game-card">
                                <img src="<?php echo $related_image; ?>" class="card-img-top" alt="<?php echo isset($related_game['nama_game']) ? $related_game['nama_game'] : 'Game'; ?>">
                                <div class="card-body text-center py-2">
                                    <h6 class="card-title mb-0"><?php echo isset($related_game['nama_game']) ? $related_game['nama_game'] : 'Game'; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ID Help Modal -->
    <div class="modal fade" id="idHelpModal" tabindex="-1" aria-labelledby="idHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idHelpModalLabel">Cara Menemukan ID Game <?php echo isset($game['nama_game']) ? $game['nama_game'] : 'Game'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="https://picsum.photos/400/300?random=100" class="img-fluid rounded" alt="ID Game Tutorial">
                    </div>
                    <ol>
                        <li>Buka aplikasi <?php echo isset($game['nama_game']) ? $game['nama_game'] : 'Game'; ?> di perangkat Anda</li>
                        <li>Klik pada ikon profil di pojok kanan atas layar</li>
                        <li>ID game Anda akan ditampilkan di bawah nama profil Anda</li>
                        <li>Salin ID game tersebut dan masukkan ke dalam form top up</li>
                    </ol>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Pastikan ID game yang Anda masukkan sudah benar. Kami tidak bertanggung jawab atas kesalahan input ID game.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Package card selection
        const packageCards = document.querySelectorAll('.package-card');
        packageCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                packageCards.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
        
        // Payment method selection
        const paymentMethods = document.querySelectorAll('.payment-method-card');
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                paymentMethods.forEach(m => m.classList.remove('selected'));
                
                // Add selected class to clicked method
                this.classList.add('selected');
            });
        });
        
        // Process order function
        function processOrder() {
            const gameId = document.getElementById('gameId').value;
            const serverZone = document.getElementById('serverZone').value;
            const selectedPackage = document.querySelector('input[name="package_id"]:checked');
            
            if (!gameId) {
                alert('Silakan masukkan ID Game Anda');
                return;
            }
            
            if (!selectedPackage) {
                alert('Silakan pilih nominal diamond');
                return;
            }
            
            // In a real application, you would submit the form or make an AJAX request
            // For this demo, we'll just show an alert
            alert('Pesanan Anda sedang diproses. Silakan lanjutkan ke pembayaran.');
            
            // Redirect to order.php with the selected package
            window.location.href = 'order.php?id=' + <?php echo $game_id; ?> + '&package_id=' + selectedPackage.value + '&game_id=' + encodeURIComponent(gameId) + '&server_zone=' + encodeURIComponent(serverZone);
        }
    </script>
</body>
</html>
