<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if customer ID is provided
if (!isset($_GET['id'])) {
    redirect('customers.php');
}

$customer_id = (int)$_GET['id'];

// Get customer details
$query = "SELECT * FROM Pelanggan WHERE id_pelanggan = $customer_id";
$result = mysqli_query($conn, $query);
$customer = mysqli_fetch_assoc($result);

// If customer doesn't exist, redirect to customers page
if (!$customer) {
    redirect('customers.php');
}

// Get customer transactions
$transactions_query = "SELECT t.*, g.nama_game, pd.nama_paket, pd.jumlah_diamond 
                      FROM Transaksi t 
                      JOIN DetailTransaksi dt ON t.id_transaksi = dt.id_transaksi 
                      JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket 
                      JOIN Game g ON pd.id_game = g.id_game 
                      WHERE t.id_pelanggan = $customer_id 
                      ORDER BY t.id_transaksi DESC";
$transactions_result = mysqli_query($conn, $transactions_query);

// Get customer statistics
$stats_query = "SELECT COUNT(*) as transaction_count, SUM(total_harga) as total_spending 
               FROM Transaksi 
               WHERE id_pelanggan = $customer_id";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
$transaction_count = $stats['transaction_count'] ?: 0;
$total_spending = $stats['total_spending'] ?: 0;

// Get favorite game
$favorite_game_query = "SELECT g.nama_game, COUNT(dt.id_detail) as purchase_count 
                       FROM Game g 
                       JOIN PaketDiamond pd ON g.id_game = pd.id_game 
                       JOIN DetailTransaksi dt ON pd.id_paket = dt.id_paket 
                       JOIN Transaksi t ON dt.id_transaksi = t.id_transaksi 
                       WHERE t.id_pelanggan = $customer_id 
                       GROUP BY g.id_game 
                       ORDER BY purchase_count DESC 
                       LIMIT 1";
$favorite_game_result = mysqli_query($conn, $favorite_game_query);
$favorite_game = mysqli_fetch_assoc($favorite_game_result);
$favorite_game_name = $favorite_game ? $favorite_game['nama_game'] : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggan | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Pelanggan</h1>
                    <a href="customers.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <!-- Customer Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Informasi Pelanggan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">ID Pelanggan</div>
                                    <div class="col-md-8"><?php echo $customer['id_pelanggan']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Nama</div>
                                    <div class="col-md-8"><?php echo $customer['nama']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Email</div>
                                    <div class="col-md-8"><?php echo $customer['email']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">No. HP</div>
                                    <div class="col-md-8"><?php echo $customer['no_hp']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Statistik Pelanggan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="card bg-primary bg-opacity-10 border-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-2">Total Transaksi</h6>
                                                <h3 class="mb-0"><?php echo $transaction_count; ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-success bg-opacity-10 border-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-2">Total Belanja</h6>
                                                <h3 class="mb-0"><?php echo formatCurrency($total_spending); ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card bg-info bg-opacity-10 border-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-2">Game Favorit</h6>
                                                <h3 class="mb-0"><?php echo $favorite_game_name; ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Transactions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Riwayat Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Game</th>
                                        <th>Paket</th>
                                        <th>Total</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($transactions_result) > 0): ?>
                                        <?php while ($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($transaction['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($transaction['tanggal_transaksi'])); ?></td>
                                            <td><?php echo $transaction['nama_game']; ?></td>
                                            <td><?php echo $transaction['nama_paket']; ?> (<?php echo number_format($transaction['jumlah_diamond']); ?> Diamond)</td>
                                            <td><?php echo formatCurrency($transaction['total_harga']); ?></td>
                                            <td><?php echo $transaction['metode_pembayaran']; ?></td>
                                            <td><?php echo getStatusLabel($transaction['status']); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-3">Tidak ada transaksi yang ditemukan</td>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
