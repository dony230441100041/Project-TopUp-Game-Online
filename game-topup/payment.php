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

// Process payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // In a real application, you would integrate with payment gateway
    // For this demo, we'll just update the transaction status to 'berhasil'
    $update_query = "UPDATE Transaksi SET status = 'berhasil' WHERE id_transaksi = $transaction_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to success page
    redirect("payment_success.php?id=$transaction_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Konfirmasi Pembayaran</h2>
                        
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Instruksi Pembayaran</h5>
                            <p class="mb-0">Silakan lakukan pembayaran sesuai dengan metode yang Anda pilih. Pesanan akan diproses setelah pembayaran berhasil.</p>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Detail Pesanan</h5>
                            </div>
                            <div class="card-body">
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
                                    <div class="col-md-4 fw-bold">Nama</div>
                                    <div class="col-md-8"><?php echo $transaction['nama']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Email</div>
                                    <div class="col-md-8"><?php echo $transaction['email']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Nomor HP</div>
                                    <div class="col-md-8"><?php echo $transaction['no_hp']; ?></div>
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
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Cara Pembayaran <?php echo $transaction['metode_pembayaran']; ?></h5>
                            </div>
                            <div class="card-body">
                                <?php if ($transaction['metode_pembayaran'] === 'OVO'): ?>
                                <ol>
                                    <li>Buka aplikasi OVO di smartphone Anda</li>
                                    <li>Pilih menu "Scan" dan scan QR code berikut:</li>
                                    <div class="text-center my-3">
                                        <img src="assets/images/payments/qr-ovo.png" alt="QR Code OVO" class="img-fluid" style="max-width: 200px;">
                                    </div>
                                    <li>Masukkan nominal <?php echo formatCurrency($transaction['total_harga']); ?></li>
                                    <li>Konfirmasi pembayaran dengan PIN OVO Anda</li>
                                    <li>Setelah pembayaran berhasil, klik tombol "Konfirmasi Pembayaran" di bawah</li>
                                </ol>
                                <?php elseif ($transaction['metode_pembayaran'] === 'Gopay'): ?>
                                <ol>
                                    <li>Buka aplikasi Gojek di smartphone Anda</li>
                                    <li>Pilih menu "Bayar" dan scan QR code berikut:</li>
                                    <div class="text-center my-3">
                                        <img src="assets/images/payments/qr-gopay.png" alt="QR Code Gopay" class="img-fluid" style="max-width: 200px;">
                                    </div>
                                    <li>Masukkan nominal <?php echo formatCurrency($transaction['total_harga']); ?></li>
                                    <li>Konfirmasi pembayaran dengan PIN Gopay Anda</li>
                                    <li>Setelah pembayaran berhasil, klik tombol "Konfirmasi Pembayaran" di bawah</li>
                                </ol>
                                <?php elseif ($transaction['metode_pembayaran'] === 'DANA'): ?>
                                <ol>
                                    <li>Buka aplikasi DANA di smartphone Anda</li>
                                    <li>Pilih menu "Scan" dan scan QR code berikut:</li>
                                    <div class="text-center my-3">
                                        <img src="assets/images/payments/qr-dana.png" alt="QR Code DANA" class="img-fluid" style="max-width: 200px;">
                                    </div>
                                    <li>Masukkan nominal <?php echo formatCurrency($transaction['total_harga']); ?></li>
                                    <li>Konfirmasi pembayaran dengan PIN DANA Anda</li>
                                    <li>Setelah pembayaran berhasil, klik tombol "Konfirmasi Pembayaran" di bawah</li>
                                </ol>
                                <?php elseif ($transaction['metode_pembayaran'] === 'ShopeePay'): ?>
                                <ol>
                                    <li>Buka aplikasi Shopee di smartphone Anda</li>
                                    <li>Pilih menu "ShopeePay" dan scan QR code berikut:</li>
                                    <div class="text-center my-3">
                                        <img src="assets/images/payments/qr-shopeepay.png" alt="QR Code ShopeePay" class="img-fluid" style="max-width: 200px;">
                                    </div>
                                    <li>Masukkan nominal <?php echo formatCurrency($transaction['total_harga']); ?></li>
                                    <li>Konfirmasi pembayaran dengan PIN ShopeePay Anda</li>
                                    <li>Setelah pembayaran berhasil, klik tombol "Konfirmasi Pembayaran" di bawah</li>
                                </ol>
                                <?php elseif ($transaction['metode_pembayaran'] === 'Transfer Bank'): ?>
                                <ol>
                                    <li>Lakukan transfer ke rekening berikut:</li>
                                    <div class="card my-3">
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Bank:</strong> Bank Central Asia (BCA)</p>
                                            <p class="mb-1"><strong>Nomor Rekening:</strong> 1234567890</p>
                                            <p class="mb-1"><strong>Atas Nama:</strong> PT Diamond Store Indonesia</p>
                                            <p class="mb-0"><strong>Jumlah:</strong> <?php echo formatCurrency($transaction['total_harga']); ?></p>
                                        </div>
                                    </div>
                                    <li>Pastikan untuk transfer tepat sampai 3 digit terakhir</li>
                                    <li>Simpan bukti transfer</li>
                                    <li>Setelah transfer berhasil, klik tombol "Konfirmasi Pembayaran" di bawah</li>
                                </ol>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <form method="post" action="">
                            <div class="d-grid">
                                <button type="submit" name="confirm_payment" class="btn btn-primary btn-lg">Konfirmasi Pembayaran</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="text-muted">Butuh bantuan? <a href="contact.php">Hubungi kami</a></p>
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
