<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

$transaction = null;
$error = null;

if (isset($_GET['id'])) {
    $transaction_id = sanitize($_GET['id']);
    // Remove # and leading zeros
    $transaction_id = ltrim($transaction_id, '#0');
    
    if (!empty($transaction_id) && is_numeric($transaction_id)) {
        $query = "SELECT t.*, p.nama, g.nama_game, pd.nama_paket, pd.jumlah_diamond 
                FROM Transaksi t 
                JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan
                JOIN DetailTransaksi dt ON t.id_transaksi = dt.id_transaksi
                JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket
                JOIN Game g ON pd.id_game = g.id_game
                WHERE t.id_transaksi = $transaction_id";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $transaction = mysqli_fetch_assoc($result);
        } else {
            $error = "Transaksi dengan ID #" . str_pad($transaction_id, 8, '0', STR_PAD_LEFT) . " tidak ditemukan.";
        }
    } else {
        $error = "ID Transaksi tidak valid. Contoh format: #00000001";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Transaksi | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .btn-primary {
            background-color: #ff7700;
            border-color: #ff7700;
        }
        .btn-primary:hover {
            background-color: #e66c00;
            border-color: #e66c00;
        }
        .btn-outline-primary {
            color: #ff7700;
            border-color: #ff7700;
        }
        .btn-outline-primary:hover {
            background-color: #ff7700;
            border-color: #ff7700;
            color: white;
        }
        .text-primary {
            color: #ff7700 !important;
        }
        .search-card {
            background: linear-gradient(135deg, #ff7700, #ff9d4c);
            color: white;
            border-radius: 15px;
        }
        .result-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Search Form -->
                <div class="card search-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="card-title text-white mb-3"><i class="bi bi-search me-2"></i>Cek Status Transaksi</h3>
                        <form method="get" action="">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                                        <input type="text" class="form-control form-control-lg" name="id" placeholder="Masukkan ID Transaksi (contoh: #00000006)" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-light btn-lg w-100">
                                        <i class="bi bi-search me-2"></i>Cari Transaksi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo $error; ?></div>
                </div>
                <?php endif; ?>
                
                <!-- Transaction Details -->
                <?php if ($transaction): ?>
                <div class="card result-card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Detail Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Informasi Transaksi</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>ID Transaksi</strong></td>
                                        <td>#<?php echo str_pad($transaction['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal</strong></td>
                                        <td><?php echo date('d F Y H:i', strtotime($transaction['tanggal_transaksi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td><?php echo getStatusLabel($transaction['status']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td class="fw-bold text-primary"><?php echo formatCurrency($transaction['total_harga']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Detail Produk</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Pelanggan</strong></td>
                                        <td><?php echo $transaction['nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Game</strong></td>
                                        <td><?php echo $transaction['nama_game']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Paket</strong></td>
                                        <td><?php echo $transaction['nama_paket']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Diamond</strong></td>
                                        <td><?php echo number_format($transaction['jumlah_diamond']); ?> Diamond</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php if ($transaction['status'] === 'berhasil'): ?>
                        <div class="alert alert-success mt-3">
                            <h6 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>Transaksi Berhasil</h6>
                            <p class="mb-0">Diamond telah ditambahkan ke akun game Anda. Terima kasih telah berbelanja di DiamondStore!</p>
                        </div>
                        <?php elseif ($transaction['status'] === 'pending'): ?>
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading"><i class="bi bi-clock-fill me-2"></i>Transaksi Dalam Proses</h6>
                            <p class="mb-0">Pembayaran Anda sedang diproses. Diamond akan ditambahkan ke akun game Anda segera setelah pembayaran dikonfirmasi.</p>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger mt-3">
                            <h6 class="alert-heading"><i class="bi bi-x-circle-fill me-2"></i>Transaksi Gagal</h6>
                            <p class="mb-0">Mohon maaf, transaksi Anda gagal diproses. Silakan hubungi customer service kami untuk informasi lebih lanjut.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-house me-2"></i>Kembali ke Home
                            </a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="history.php" class="btn btn-primary">
                                <i class="bi bi-clock-history me-2"></i>Lihat Riwayat Pesanan
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
