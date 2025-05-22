<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get report type
$report_type = isset($_GET['report']) ? sanitize($_GET['report']) : 'top_customers';

// Date filter
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t'); // Last day of current month

// Get report data based on type
$report_title = '';
$report_data = [];

switch ($report_type) {
    case 'top_customers':
        $report_title = 'Top 5 Pelanggan';
        $query = "SELECT * FROM vw_top5_pelanggan";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        break;
        
    case 'large_transactions':
        $report_title = 'Transaksi Besar (>100k)';
        $query = "SELECT * FROM vw_transaksi_besar";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        break;
        
    case 'customer_purchases':
        $report_title = 'Total Pembelian Pelanggan';
        $query = "SELECT * FROM vw_total_pembelian_pelanggan ORDER BY total_pembelian DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        break;
        
    case 'package_details':
        $report_title = 'Detail Paket Terjual per Game';
        $game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
        $game_filter = $game_id > 0 ? "WHERE g.id_game = $game_id" : "";
        
        $query = "SELECT g.nama_game, pd.nama_paket, pd.jumlah_diamond, COUNT(dt.id_detail) as jumlah_terjual, 
                 SUM(dt.subtotal) as total_penjualan
                 FROM Game g
                 JOIN PaketDiamond pd ON g.id_game = pd.id_game
                 LEFT JOIN DetailTransaksi dt ON pd.id_paket = dt.id_paket
                 LEFT JOIN Transaksi t ON dt.id_transaksi = t.id_transaksi
                 $game_filter
                 GROUP BY g.nama_game, pd.nama_paket, pd.jumlah_diamond
                 ORDER BY g.nama_game, pd.jumlah_diamond";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        break;
        
    case 'daily_transactions':
        $report_title = 'Transaksi Harian';
        $query = "SELECT DATE(t.tanggal_transaksi) as tanggal, COUNT(*) as jumlah_transaksi, 
                 SUM(t.total_harga) as total_penjualan
                 FROM Transaksi t
                 WHERE t.tanggal_transaksi BETWEEN '$start_date' AND '$end_date'
                 GROUP BY DATE(t.tanggal_transaksi)
                 ORDER BY DATE(t.tanggal_transaksi)";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        break;
}

// Get all games for dropdown
$games_query = "SELECT * FROM Game ORDER BY nama_game";
$games_result = mysqli_query($conn, $games_query);
$games = [];
while ($game = mysqli_fetch_assoc($games_result)) {
    $games[] = $game;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | DiamondStore</title>
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
                    <h1 class="h2">Laporan: <?php echo $report_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
                
                <!-- Report Type Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="get" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="report" class="form-label">Jenis Laporan</label>
                                <select name="report" id="report" class="form-select" onchange="this.form.submit()">
                                    <option value="top_customers" <?php echo $report_type === 'top_customers' ? 'selected' : ''; ?>>Top 5 Pelanggan</option>
                                    <option value="large_transactions" <?php echo $report_type === 'large_transactions' ? 'selected' : ''; ?>>Transaksi Besar (>100k)</option>
                                    <option value="customer_purchases" <?php echo $report_type === 'customer_purchases' ? 'selected' : ''; ?>>Total Pembelian Pelanggan</option>
                                    <option value="package_details" <?php echo $report_type === 'package_details' ? 'selected' : ''; ?>>Detail Paket Terjual per Game</option>
                                    <option value="daily_transactions" <?php echo $report_type === 'daily_transactions' ? 'selected' : ''; ?>>Transaksi Harian</option>
                                </select>
                            </div>
                            
                            <?php if ($report_type === 'package_details'): ?>
                            <div class="col-md-4">
                                <label for="game_id" class="form-label">Game</label>
                                <select name="game_id" id="game_id" class="form-select" onchange="this.form.submit()">
                                    <option value="0">Semua Game</option>
                                    <?php foreach ($games as $game): ?>
                                    <option value="<?php echo $game['id_game']; ?>" <?php echo isset($_GET['game_id']) && $_GET['game_id'] == $game['id_game'] ? 'selected' : ''; ?>>
                                        <?php echo $game['nama_game']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($report_type === 'daily_transactions'): ?>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Report Content -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <?php if ($report_type === 'top_customers'): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Total Belanja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data)): ?>
                                        <?php $no = 1; foreach ($report_data as $data): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $data['nama_pelanggan']; ?></td>
                                            <td><?php echo formatCurrency($data['total_belanja']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php elseif ($report_type === 'large_transactions'): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data)): ?>
                                        <?php foreach ($report_data as $data): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($data['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $data['nama_pelanggan']; ?></td>
                                            <td><?php echo formatCurrency($data['total_harga']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php elseif ($report_type === 'customer_purchases'): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Pelanggan</th>
                                        <th>Total Pembelian</th>
                                        <th>Jumlah Transaksi</th>
                                        <th>Rata-rata per Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data)): ?>
                                        <?php foreach ($report_data as $data): ?>
                                        <tr>
                                            <td><?php echo $data['nama_pelanggan']; ?></td>
                                            <td><?php echo formatCurrency($data['total_pembelian']); ?></td>
                                            <td><?php echo $data['jumlah_transaksi']; ?></td>
                                            <td><?php echo formatCurrency($data['total_pembelian'] / $data['jumlah_transaksi']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php elseif ($report_type === 'package_details'): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Game</th>
                                        <th>Nama Paket</th>
                                        <th>Jumlah Diamond</th>
                                        <th>Jumlah Terjual</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data)): ?>
                                        <?php foreach ($report_data as $data): ?>
                                        <tr>
                                            <td><?php echo $data['nama_game']; ?></td>
                                            <td><?php echo $data['nama_paket']; ?></td>
                                            <td><?php echo number_format($data['jumlah_diamond']); ?></td>
                                            <td><?php echo $data['jumlah_terjual'] ?: 0; ?></td>
                                            <td><?php echo formatCurrency($data['total_penjualan'] ?: 0); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php elseif ($report_type === 'daily_transactions'): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah Transaksi</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data)): ?>
                                        <?php foreach ($report_data as $data): ?>
                                        <tr>
                                            <td><?php echo date('d F Y', strtotime($data['tanggal'])); ?></td>
                                            <td><?php echo $data['jumlah_transaksi']; ?></td>
                                            <td><?php echo formatCurrency($data['total_penjualan']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
