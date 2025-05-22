<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Check if transaction ID is provided
if (!isset($_GET['id'])) {
    redirect('index.php');
}

$transaction_id = (int)$_GET['id'];

// Get transaction details
$query = "SELECT t.*, p.nama, p.email, p.no_hp 
          FROM Transaksi t 
          JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
          WHERE t.id_transaksi = $transaction_id";
$result = mysqli_query($conn, $query);
$transaction = mysqli_fetch_assoc($result);

// If transaction doesn't exist, redirect to home
if (!$transaction) {
    redirect('index.php');
}

// Get transaction details
$detail_query = "SELECT dt.*, pd.nama_paket, pd.jumlah_diamond, g.nama_game 
                FROM DetailTransaksi dt 
                JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket 
                JOIN Game g ON pd.id_game = g.id_game 
                WHERE dt.id_transaksi = $transaction_id";
$detail_result = mysqli_query($conn, $detail_query);
$detail = mysqli_fetch_assoc($detail_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-animation {
            animation: bounce 1s ease-in-out;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-30px);}
            60% {transform: translateY(-15px);}
        }
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
        }
        .text-primary {
            color: #ff7700 !important;
        }
        .bg-primary {
            background-color: #ff7700 !important;
        }
        .card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4 success-animation">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">Pembayaran Berhasil!</h2>
                        <p class="lead mb-4">Terima kasih atas pembelian Anda. Diamond akan segera ditambahkan ke akun game Anda.</p>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Detail Pesanan</h5>
                            </div>
                            <div class="card-body text-start">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">ID Transaksi</div>
                                    <div class="col-md-8">#<?php echo str_pad($transaction_id, 8, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Game</div>
                                    <div class="col-md-8"><?php echo $detail['nama_game']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Paket</div>
                                    <div class="col-md-8"><?php echo $detail['nama_paket']; ?> (<?php echo number_format($detail['jumlah_diamond']); ?> Diamond)</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Tanggal</div>
                                    <div class="col-md-8"><?php echo date('d F Y H:i', strtotime($transaction['waktu_transaksi'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Metode Pembayaran</div>
                                    <div class="col-md-8"><?php echo $transaction['metode_pembayaran']; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 fw-bold">Total</div>
                                    <div class="col-md-8 fw-bold text-primary"><?php echo formatCurrency($transaction['total_harga']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success">
                            <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informasi Pengiriman</h5>
                            <p class="mb-0">Diamond akan ditambahkan ke akun game Anda dalam waktu 5-15 menit. Jika dalam 30 menit belum masuk, silakan hubungi customer service kami.</p>
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="index.php" class="btn btn-outline-primary">Kembali ke Home</a>
                            <a href="history.php" class="btn btn-primary">Lihat Riwayat Pesanan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically redirect to history page after 5 seconds
        setTimeout(function() {
            document.querySelector('a[href="history.php"]').classList.add('btn-pulse');
        }, 3000);
    </script>
</body>
</html>
