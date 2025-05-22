<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Check if transaction ID is provided
if (!isset($_GET['id'])) {
    redirect('history.php');
}

$transaction_id = (int)$_GET['id'];

// Get transaction details
$query = "SELECT t.*, p.nama, p.email, p.no_hp 
          FROM Transaksi t 
          JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
          WHERE t.id_transaksi = $transaction_id";
$result = mysqli_query($conn, $query);
$transaction = mysqli_fetch_assoc($result);

// If transaction doesn't exist, redirect to history
if (!$transaction) {
    redirect('history.php');
}

// Get transaction details
$detail_query = "SELECT dt.*, pd.nama_paket, pd.jumlah_diamond, g.nama_game, g.id_game 
                FROM DetailTransaksi dt 
                JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket 
                JOIN Game g ON pd.id_game = g.id_game 
                WHERE dt.id_transaksi = $transaction_id";
$detail_result = mysqli_query($conn, $detail_query);
$details = [];

while ($row = mysqli_fetch_assoc($detail_result)) {
    $details[] = $row;
}

// Get player ID if available
$player_query = "SELECT player_id, server_id FROM PlayerInfo WHERE id_transaksi = $transaction_id";
$player_result = mysqli_query($conn, $player_query);
$player_info = mysqli_fetch_assoc($player_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?php echo str_pad($transaction_id, 8, '0', STR_PAD_LEFT); ?> | DiamondStore</title>
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e0e0e0;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #ff7700;
        }
        .timeline-item.completed::before {
            background-color: #ff7700;
        }
        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .game-img {
            max-height: 80px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="history.php">Riwayat Pemesanan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Transaksi</li>
                    </ol>
                </nav>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Detail Transaksi #<?php echo str_pad($transaction_id, 8, '0', STR_PAD_LEFT); ?></h4>
                            <span class="badge bg-light text-dark"><?php echo date('d F Y', strtotime($transaction['tanggal_transaksi'])); ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">Informasi Transaksi</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="width: 30%"><strong>ID Transaksi</strong></td>
                                            <td>#<?php echo str_pad($transaction_id, 8, '0', STR_PAD_LEFT); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal & Waktu</strong></td>
                                            <td><?php echo date('d F Y H:i:s', strtotime($transaction['waktu_transaksi'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td><?php echo getStatusLabel($transaction['status']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Metode Pembayaran</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['metode_pembayaran']); ?></td>
                                        </tr>
                                        <?php if ($player_info): ?>
                                        <tr>
                                            <td><strong>ID Player</strong></td>
                                            <td><?php echo htmlspecialchars($player_info['player_id']); ?></td>
                                        </tr>
                                        <?php if (!empty($player_info['server_id'])): ?>
                                        <tr>
                                            <td><strong>Server ID</strong></td>
                                            <td><?php echo htmlspecialchars($player_info['server_id']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                
                                <h5 class="mb-3 mt-4">Informasi Pelanggan</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="width: 30%"><strong>Nama</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['nama']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. HP</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['no_hp']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <?php if ($transaction['status'] == 'berhasil'): ?>
                                <div class="alert alert-success mt-4">
                                    <h5 class="alert-heading"><i class="bi bi-check-circle me-2"></i>Transaksi Berhasil</h5>
                                    <p class="mb-0">Diamond telah ditambahkan ke akun game Anda. Terima kasih telah berbelanja di DiamondStore!</p>
                                </div>
                                <?php elseif ($transaction['status'] == 'pending'): ?>
                                <div class="alert alert-warning mt-4">
                                    <h5 class="alert-heading"><i class="bi bi-hourglass-split me-2"></i>Transaksi Dalam Proses</h5>
                                    <p class="mb-0">Pembayaran Anda sedang diproses. Diamond akan ditambahkan ke akun game Anda segera setelah pembayaran dikonfirmasi.</p>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-danger mt-4">
                                    <h5 class="alert-heading"><i class="bi bi-x-circle me-2"></i>Transaksi Gagal</h5>
                                    <p class="mb-0">Maaf, transaksi Anda gagal diproses. Silakan hubungi customer service kami untuk informasi lebih lanjut.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Ringkasan Pesanan</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($details as $detail): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <?php 
                                            $game_image = "assets/images/games/default-game.jpg";
                                            $game_name = strtolower(str_replace(' ', '-', $detail['nama_game']));
                                            $custom_image = "assets/images/games/{$game_name}.jpg";
                                            
                                            if (file_exists($custom_image)) {
                                                $game_image = $custom_image;
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($game_image); ?>" alt="<?php echo htmlspecialchars($detail['nama_game']); ?>" class="game-img me-3">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($detail['nama_game']); ?></h6>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($detail['nama_paket']); ?></p>
                                                <p class="mb-0"><strong><?php echo number_format($detail['jumlah_diamond']); ?> Diamond</strong></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <hr>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal</span>
                                            <span><?php echo formatCurrency($transaction['total_harga']); ?></span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Biaya Layanan</span>
                                            <span>Rp 0</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total</span>
                                            <span class="text-primary"><?php echo formatCurrency($transaction['total_harga']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Status Pesanan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            <div class="timeline-item completed">
                                                <h6 class="mb-0">Pesanan Dibuat</h6>
                                                <p class="timeline-date"><?php echo date('d M Y H:i', strtotime($transaction['waktu_transaksi'])); ?></p>
                                            </div>
                                            
                                            <div class="timeline-item <?php echo ($transaction['status'] != 'gagal') ? 'completed' : ''; ?>">
                                                <h6 class="mb-0">Pembayaran</h6>
                                                <p class="timeline-date">
                                                    <?php 
                                                    if ($transaction['status'] == 'gagal') {
                                                        echo 'Pembayaran gagal';
                                                    } elseif ($transaction['status'] == 'pending') {
                                                        echo 'Menunggu konfirmasi';
                                                    } else {
                                                        echo date('d M Y H:i', strtotime($transaction['waktu_transaksi'] . ' +5 minutes'));
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            
                                            <div class="timeline-item <?php echo ($transaction['status'] == 'berhasil') ? 'completed' : ''; ?>">
                                                <h6 class="mb-0">Pengiriman Diamond</h6>
                                                <p class="timeline-date">
                                                    <?php 
                                                    if ($transaction['status'] == 'berhasil') {
                                                        echo date('d M Y H:i', strtotime($transaction['waktu_transaksi'] . ' +10 minutes'));
                                                    } elseif ($transaction['status'] == 'pending') {
                                                        echo 'Menunggu pembayaran';
                                                    } else {
                                                        echo 'Dibatalkan';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            
                                            <div class="timeline-item <?php echo ($transaction['status'] == 'berhasil') ? 'completed' : ''; ?>">
                                                <h6 class="mb-0">Selesai</h6>
                                                <p class="timeline-date">
                                                    <?php 
                                                    if ($transaction['status'] == 'berhasil') {
                                                        echo date('d M Y H:i', strtotime($transaction['waktu_transaksi'] . ' +15 minutes'));
                                                    } else {
                                                        echo 'Menunggu';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4 justify-content-between">
                            <a href="history.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                            </a>
                            
                            <div>
                                <?php if ($transaction['status'] == 'berhasil' && !empty($details)): ?>
                                <a href="order.php?id=<?php echo $details[0]['id_game']; ?>&package_id=<?php echo $details[0]['id_paket']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-cart-plus me-1"></i> Beli Lagi
                                </a>
                                <?php endif; ?>
                                
                                <a href="#" class="btn btn-primary" onclick="window.print()">
                                    <i class="bi bi-printer me-1"></i> Cetak Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
