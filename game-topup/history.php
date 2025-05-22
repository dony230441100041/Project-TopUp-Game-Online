<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Cek apakah ada parameter email atau ID pelanggan
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$email = isset($_GET['email']) ? sanitize($_GET['email']) : null;

// Jika tidak ada parameter, cek apakah ada di session
if (!$customer_id && !$email && isset($_SESSION['customer_email'])) {
    $email = $_SESSION['customer_email'];
}

// Jika masih tidak ada, tampilkan form pencarian
$customer = null;
$transactions = [];

if ($customer_id || $email) {
    // Cari pelanggan berdasarkan ID atau email
    if ($customer_id) {
        $query = "SELECT * FROM Pelanggan WHERE id_pelanggan = $customer_id";
    } else {
        $query = "SELECT * FROM Pelanggan WHERE email = '$email'";
    }
    
    $result = mysqli_query($conn, $query);
    $customer = mysqli_fetch_assoc($result);
    
    // Jika pelanggan ditemukan, ambil riwayat transaksi
    if ($customer) {
        $customer_id = $customer['id_pelanggan'];
        
        // Simpan email pelanggan di session untuk kemudahan akses berikutnya
        $_SESSION['customer_email'] = $customer['email'];
        
        // Ambil semua transaksi pelanggan
        $transaction_query = "SELECT t.*, 
                             (SELECT COUNT(*) FROM DetailTransaksi WHERE id_transaksi = t.id_transaksi) as item_count
                             FROM Transaksi t 
                             WHERE t.id_pelanggan = $customer_id 
                             ORDER BY t.waktu_transaksi DESC";
        $transaction_result = mysqli_query($conn, $transaction_query);
        
        while ($row = mysqli_fetch_assoc($transaction_result)) {
            $transactions[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan | DiamondStore</title>
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
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .transaction-card {
            border-left: 5px solid #e0e0e0;
        }
        .transaction-card.status-berhasil {
            border-left-color: #28a745;
        }
        .transaction-card.status-pending {
            border-left-color: #ffc107;
        }
        .transaction-card.status-gagal {
            border-left-color: #dc3545;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        .empty-state i {
            font-size: 5rem;
            color: #e0e0e0;
            margin-bottom: 20px;
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
                        <li class="breadcrumb-item active" aria-current="page">Riwayat Pemesanan</li>
                    </ol>
                </nav>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="mb-4"><i class="bi bi-clock-history me-2"></i>Riwayat Pemesanan</h2>
                        
                        <?php if (!$customer): ?>
                        <div class="alert alert-info mb-4">
                            <p class="mb-0">Masukkan email yang Anda gunakan saat melakukan pemesanan untuk melihat riwayat transaksi Anda.</p>
                        </div>
                        
                        <form method="get" action="" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <input type="email" class="form-control" name="email" placeholder="Masukkan email Anda" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Cari Riwayat</button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="empty-state">
                            <i class="bi bi-search"></i>
                            <h4>Cari Riwayat Pemesanan Anda</h4>
                            <p class="text-muted">Masukkan email yang Anda gunakan saat melakukan pemesanan untuk melihat riwayat transaksi.</p>
                        </div>
                        
                        <?php elseif (empty($transactions)): ?>
                        <div class="alert alert-warning mb-4">
                            <h5 class="alert-heading">Tidak Ada Transaksi</h5>
                            <p class="mb-0">Kami tidak menemukan riwayat transaksi untuk email <strong><?php echo htmlspecialchars($customer['email']); ?></strong>.</p>
                        </div>
                        
                        <div class="empty-state">
                            <i class="bi bi-cart-x"></i>
                            <h4>Belum Ada Pemesanan</h4>
                            <p class="text-muted">Anda belum melakukan pemesanan apapun. Silakan lakukan pemesanan terlebih dahulu.</p>
                            <a href="games.php" class="btn btn-primary mt-3">Lihat Game</a>
                        </div>
                        
                        <?php else: ?>
                        <div class="alert alert-success mb-4">
                            <h5 class="alert-heading">Riwayat Ditemukan</h5>
                            <p class="mb-0">Menampilkan riwayat transaksi untuk <strong><?php echo htmlspecialchars($customer['nama']); ?></strong> (<?php echo htmlspecialchars($customer['email']); ?>).</p>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Transaksi</h5>
                                        <p class="card-text display-6"><?php echo count($transactions); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Transaksi Berhasil</h5>
                                        <p class="card-text display-6">
                                            <?php 
                                            $success_count = 0;
                                            foreach ($transactions as $t) {
                                                if ($t['status'] == 'berhasil') $success_count++;
                                            }
                                            echo $success_count;
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Pengeluaran</h5>
                                        <p class="card-text display-6">
                                            <?php 
                                            $total_spent = 0;
                                            foreach ($transactions as $t) {
                                                if ($t['status'] == 'berhasil') $total_spent += $t['total_harga'];
                                            }
                                            echo formatCurrency($total_spent);
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($transaction['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($transaction['waktu_transaksi'])); ?></td>
                                        <td><?php echo formatCurrency($transaction['total_harga']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['metode_pembayaran']); ?></td>
                                        <td><?php echo getStatusLabel($transaction['status']); ?></td>
                                        <td>
                                            <a href="transaction_detail.php?id=<?php echo $transaction['id_transaksi']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
