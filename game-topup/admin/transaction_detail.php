<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if transaction ID is provided
if (!isset($_GET['id'])) {
    redirect('transactions.php');
}

$transaction_id = (int)$_GET['id'];

// Get transaction details
$query = "SELECT t.*, p.nama, p.email, p.no_hp 
          FROM Transaksi t 
          JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
          WHERE t.id_transaksi = $transaction_id";
$result = mysqli_query($conn, $query);
$transaction = mysqli_fetch_assoc($result);

// If transaction doesn't exist, redirect to transactions page
if (!$transaction) {
    redirect('transactions.php');
}

// Get transaction items
$items_query = "SELECT dt.*, pd.nama_paket, pd.jumlah_diamond, g.nama_game 
               FROM DetailTransaksi dt 
               JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket 
               JOIN Game g ON pd.id_game = g.id_game 
               WHERE dt.id_transaksi = $transaction_id";
$items_result = mysqli_query($conn, $items_query);

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    
    $update_query = "UPDATE Transaksi SET status = '$new_status' WHERE id_transaksi = $transaction_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to refresh the page
    redirect("transaction_detail.php?id=$transaction_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi | DiamondStore</title>
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
                    <h1 class="h2">Detail Transaksi</h1>
                    <div>
                        <a href="transactions.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
                
                <!-- Transaction Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Informasi Transaksi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">ID Transaksi</div>
                                    <div class="col-md-8">#<?php echo str_pad($transaction_id, 8, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Tanggal</div>
                                    <div class="col-md-8"><?php echo date('d F Y', strtotime($transaction['tanggal_transaksi'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Waktu</div>
                                    <div class="col-md-8"><?php echo date('H:i:s', strtotime($transaction['waktu_transaksi'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Total</div>
                                    <div class="col-md-8"><?php echo formatCurrency($transaction['total_harga']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Metode Pembayaran</div>
                                    <div class="col-md-8"><?php echo $transaction['metode_pembayaran']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Status</div>
                                    <div class="col-md-8">
                                        <?php echo getStatusLabel($transaction['status']); ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                            <i class="bi bi-pencil"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Informasi Pelanggan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Nama</div>
                                    <div class="col-md-8"><?php echo $transaction['nama']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Email</div>
                                    <div class="col-md-8"><?php echo $transaction['email']; ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">No. HP</div>
                                    <div class="col-md-8"><?php echo $transaction['no_hp']; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <a href="customer_detail.php?id=<?php echo $transaction['id_pelanggan']; ?>" class="btn btn-outline-primary">
                                            <i class="bi bi-person"></i> Lihat Detail Pelanggan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction Items -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Item Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Game</th>
                                        <th>Paket</th>
                                        <th>Jumlah Diamond</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($items_result) > 0): ?>
                                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                        <tr>
                                            <td><?php echo $item['nama_game']; ?></td>
                                            <td><?php echo $item['nama_paket']; ?></td>
                                            <td><?php echo number_format($item['jumlah_diamond']); ?></td>
                                            <td><?php echo $item['jumlah']; ?></td>
                                            <td><?php echo formatCurrency($item['subtotal']); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">Tidak ada item yang ditemukan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total</td>
                                        <td class="fw-bold"><?php echo formatCurrency($transaction['total_harga']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Status Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending" <?php echo $transaction['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="berhasil" <?php echo $transaction['status'] === 'berhasil' ? 'selected' : ''; ?>>Berhasil</option>
                                <option value="gagal" <?php echo $transaction['status'] === 'gagal' ? 'selected' : ''; ?>>Gagal</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
