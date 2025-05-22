<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Check if game ID is provided
if (!isset($_GET['id']) || !isset($_GET['package_id'])) {
    redirect('index.php');
}
// Get and sanitize parameters
$game_id = (int)$_GET['id'];
$package_id = (int)$_GET['package_id'];
$player_id = isset($_GET['game_id']) ? sanitize($_GET['game_id']) : '';
$server_zone = isset($_GET['server_zone']) ? sanitize($_GET['server_zone']) : '';
$payment_method = isset($_GET['payment_method']) ? sanitize($_GET['payment_method']) : '';

// Get game details - fetch before using it
$game = getGameById($game_id);

// If game doesn't exist, create a default game object to prevent null errors
if (!$game) {
    // Create a default game object with placeholder values
    $game = [
        'id_game' => $game_id,
        'nama_game' => 'Game Not Found',
        'developer' => 'Unknown Developer',
        'deskripsi' => 'Game information not available'
    ];
    
    // Log the error
    error_log("Game with ID $game_id not found, using default values");
}

// Get packages and ensure it's an array
$packages = getPackagesByGameId($game_id);
if (!is_array($packages)) {
    $packages = [];
}

/// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $errors = [];
    
    if (!isset($_POST['package_id']) || empty($_POST['package_id'])) {
        $errors[] = "Silakan pilih paket diamond";
    }
    
    if (!isset($_POST['player_id']) || empty($_POST['player_id'])) {
        $errors[] = "ID Player tidak boleh kosong";
    }
    
    if (!isset($_POST['player_name']) || empty($_POST['player_name'])) {
        $errors[] = "Nama Player tidak boleh kosong";
    }
    
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!isset($_POST['phone']) || empty($_POST['phone'])) {
        $errors[] = "Nomor HP tidak boleh kosong";
    }
    
    if (!isset($_POST['payment_method']) || empty($_POST['payment_method'])) {
        $errors[] = "Silakan pilih metode pembayaran";
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        $package_id = (int)$_POST['package_id'];
        $player_id = sanitize($_POST['player_id']);
        $player_name = sanitize($_POST['player_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $payment_method = sanitize($_POST['payment_method']);
        
        // Get package details
        $package_id = $_GET['package_id'];
        $query = "SELECT * FROM PaketDiamond WHERE id_paket = $package_id";
        $result = mysqli_query($conn, $query);
        $package = mysqli_fetch_assoc($result);

        if (!$package) {
            die("Paket tidak ditemukan. Silakan kembali dan pilih paket yang tersedia.");
            header("Location: error.php?msg=paket_tidak_ditemukan");
            exit;
        }

        // Baru gunakan setelah validasi
        $harga = $package['harga'];
  
        // Check if customer exists
        $customer_query = "SELECT * FROM Pelanggan WHERE email = '$email'";
        $customer_result = mysqli_query($conn, $customer_query);
        $customer = mysqli_fetch_assoc($customer_result);
        
        if ($customer) {
            $customer_id = $customer['id_pelanggan'];
            
            // Update customer info if needed
            if ($customer['nama'] != $player_name || $customer['no_hp'] != $phone) {
                $update_query = "UPDATE Pelanggan SET nama = '$player_name', no_hp = '$phone' WHERE id_pelanggan = $customer_id";
                mysqli_query($conn, $update_query);
            }
        } else {
            // Create new customer
            $insert_query = "INSERT INTO Pelanggan (nama, email, no_hp) VALUES ('$player_name', '$email', '$phone')";
            mysqli_query($conn, $insert_query);
            $customer_id = mysqli_insert_id($conn);
        }
        
        // Create transaction
        $total_price = $package['harga'];
        $current_date = date('Y-m-d');
        $current_time = date('Y-m-d H:i:s');
        
        $transaction_query = "INSERT INTO Transaksi (id_pelanggan, tanggal_transaksi, waktu_transaksi, total_harga, metode_pembayaran, status) 
                             VALUES ($customer_id, '$current_date', '$current_time', $total_price, '$payment_method', 'pending')";
        mysqli_query($conn, $transaction_query);
        $transaction_id = mysqli_insert_id($conn);
        
        // Create transaction detail
        $detail_query = "INSERT INTO DetailTransaksi (id_transaksi, id_paket, jumlah, subtotal) 
                        VALUES ($transaction_id, $package_id, 1, $total_price)";
        mysqli_query($conn, $detail_query);
        
        // Store player ID in session for future use
        $_SESSION['last_player_id'] = $player_id;
        
        // Redirect to payment confirmation page
        redirect("payment.php?id=$transaction_id");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Top Up - <?php echo htmlspecialchars($game['nama_game']); ?> | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="games.php">Games</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($game['nama_game']); ?></li>
                    </ol>
                </nav>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center mb-4">
                            <div class="col-md-2 text-center">
                                <?php 
                                // $game_image = "assets/images/games/default-game.jpg";
                                $game_image = "assets/images/games/pubg.webp";
                                // $game_name = strtolower(str_replace(' ', '-', $game['nama_game']));
                                // $custom_image = "assets/images/games/{$game_name}.jpg";
                                
                                // if (file_exists($custom_image)) {
                                //     $game_image = $custom_image;
                                // }
                                ?>
                                <img src="<?php echo htmlspecialchars($game_image); ?>" class="img-fluid rounded" style="max-height: 120px;" alt="<?php echo htmlspecialchars($game['nama_game']); ?>">
                            </div>
                            <div class="col-md-10">
                                <h2><?php echo htmlspecialchars($game['nama_game']); ?></h2>
                                <p class="text-muted">Developer: <?php echo htmlspecialchars($game['developer']); ?></p>
                                <p>Silakan pilih paket diamond dan masukkan data diri Anda untuk melakukan top up.</p>
                            </div>
                        </div>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-3">1. Pilih Paket Diamond</h4>
                                    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                                        <?php if (!empty($packages)): ?>
                                            <?php foreach ($packages as $package): ?>
                                            <div class="col">
                                                <div class="card package-card h-100">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="package_id" id="package_<?php echo $package['id_paket']; ?>" value="<?php echo $package['id_paket']; ?>" required>
                                                                <label class="form-check-label w-100" for="package_<?php echo $package['id_paket']; ?>">
                                                                    <h5 class="card-title"><?php echo htmlspecialchars($package['nama_paket']); ?></h5>
                                                                    <p class="card-text mb-1"><?php echo number_format($package['jumlah_diamond']); ?> Diamond</p>
                                                                    <p class="card-text text-primary fw-bold"><?php echo formatCurrency($package['harga']); ?></p>
                                                                </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    Tidak ada paket diamond yang tersedia untuk game ini.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h4 class="mb-3">2. Masukkan Data Diri</h4>
                                    <div class="mb-3">
                                        <label for="player_id" class="form-label">ID Player</label>
                                        <input type="text" class="form-control" id="player_id" name="player_id" placeholder="Masukkan ID Player" value="<?php echo isset($_SESSION['last_player_id']) ? htmlspecialchars($_SESSION['last_player_id']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="player_name" class="form-label">Nama Player</label>
                                        <input type="text" class="form-control" id="player_name" name="player_name" placeholder="Masukkan Nama Player" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Nomor HP</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan Nomor HP" required>
                                    </div>
                                </div>
                                
                                
                                <div class="col-md-6">
                                    <h4 class="mb-3">3. Pilih Metode Pembayaran</h4>
                                    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                                        <div class="col">
                                            <div class="card payment-method h-100 <?php echo $payment_method === 'OVO' ? 'selected' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_ovo" value="OVO" required>
                                                        <label class="form-check-label w-100" for="payment_ovo">
                                                            <div class="d-flex align-items-center">
                                                                <img src="assets/images/payments/ovo.png" alt="OVO" height="30">
                                                                <span class="ms-2">OVO</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card payment-method h-100 <?php echo $payment_method === 'Gopay' ? 'selected' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_gopay" value="Gopay" required>
                                                        <label class="form-check-label w-100" for="payment_gopay">
                                                            <div class="d-flex align-items-center">
                                                                <img src="assets/images/payments/gopay.png" alt="Gopay" height="30">
                                                                <span class="ms-2">Gopay</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card payment-method h-100 <?php echo $payment_method === 'DANA' ? 'selected' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_dana" value="DANA" required>
                                                        <label class="form-check-label w-100" for="payment_dana">
                                                            <div class="d-flex align-items-center">
                                                                <img src="assets/images/payments/dana.png" alt="DANA" height="30">
                                                                <span class="ms-2">DANA</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card payment-method h-100 <?php echo $payment_method === 'ShopeePay' ? 'selected' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_shopeepay" value="ShopeePay" required>
                                                        <label class="form-check-label w-100" for="payment_shopeepay">
                                                            <div class="d-flex align-items-center">
                                                                <img src="assets/images/payments/shopeepay.png" alt="ShopeePay" height="30">
                                                                <span class="ms-2">ShopeePay</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card payment-method h-100 <?php echo $payment_method === 'Transfer Bank' ? 'selected' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="Transfer Bank" required>
                                                        <label class="form-check-label w-100" for="payment_bank">
                                                            <div class="d-flex align-items-center">
                                                                <img src="assets/images/payments/bank.png" alt="Transfer Bank" height="30">
                                                                <span class="ms-2">Transfer Bank</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h4 class="mb-3">4. Konfirmasi Pesanan</h4>
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <p class="mb-1">Dengan melakukan pemesanan, Anda menyetujui:</p>
                                            <ul>
                                                <li>Syarat dan ketentuan layanan DiamondStore</li>
                                                <li>Pesanan tidak dapat dibatalkan setelah pembayaran</li>
                                                <li>Diamond akan masuk ke akun dalam waktu 5-15 menit setelah pembayaran berhasil</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">Lanjutkan ke Pembayaran</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script to handle package card selection
        document.querySelectorAll('.package-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Remove selected class from all cards
                document.querySelectorAll('.package-card').forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Add selected class to clicked card
                this.classList.add('selected');
            });
        });
        
        // Script to handle payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                
                // Add selected class to clicked method
                this.classList.add('selected');
            });
        });
    </script>
</body>
</html>
