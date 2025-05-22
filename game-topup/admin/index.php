<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get dashboard statistics
$total_transactions_query = "SELECT COUNT(*) as count FROM Transaksi";
$total_transactions_result = mysqli_query($conn, $total_transactions_query);
$total_transactions_row = mysqli_fetch_assoc($total_transactions_result);
$total_transactions = $total_transactions_row['count'];

$total_customers_query = "SELECT COUNT(*) as count FROM Pelanggan";
$total_customers_result = mysqli_query($conn, $total_customers_query);
$total_customers_row = mysqli_fetch_assoc($total_customers_result);
$total_customers = $total_customers_row['count'];

$total_revenue_query = "SELECT SUM(total_harga) as total FROM Transaksi WHERE status = 'berhasil'";
$total_revenue_result = mysqli_query($conn, $total_revenue_query);
$total_revenue_row = mysqli_fetch_assoc($total_revenue_result);
$total_revenue = $total_revenue_row['total'] ? $total_revenue_row['total'] : 0;

$top_game_query = "SELECT g.nama_game, COUNT(dt.id_detail) as total_sales 
                  FROM Game g
                  JOIN PaketDiamond pd ON g.id_game = pd.id_game
                  JOIN DetailTransaksi dt ON pd.id_paket = dt.id_paket
                  JOIN Transaksi t ON dt.id_transaksi = t.id_transaksi
                  WHERE t.status = 'berhasil'
                  GROUP BY g.id_game
                  ORDER BY total_sales DESC
                  LIMIT 1";
$top_game_result = mysqli_query($conn, $top_game_query);
$top_game_row = mysqli_fetch_assoc($top_game_result);
$top_game = $top_game_row ? $top_game_row['nama_game'] : 'N/A';

// Get weekly transaction data for chart
$weekly_data_query = "SELECT DATE(tanggal_transaksi) as date, SUM(total_harga) as total 
                     FROM Transaksi 
                     WHERE tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                     GROUP BY DATE(tanggal_transaksi) 
                     ORDER BY date";
$weekly_data_result = mysqli_query($conn, $weekly_data_query);
$chart_dates = [];
$chart_values = [];

while ($row = mysqli_fetch_assoc($weekly_data_result)) {
    $chart_dates[] = date('d/m', strtotime($row['date']));
    $chart_values[] = $row['total'];
}

// Get recent transactions
$recent_query = "SELECT t.id_transaksi, p.nama, t.total_harga, t.tanggal_transaksi, t.status 
                FROM Transaksi t 
                JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                ORDER BY t.id_transaksi DESC 
                LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

// Filter transactions by week if requested
$filter_week = isset($_GET['filter']) && $_GET['filter'] == 'week';
$filter_condition = $filter_week ? "WHERE t.tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)" : "";

// Get filtered transactions
$filtered_query = "SELECT t.id_transaksi, p.nama, t.total_harga, t.tanggal_transaksi, t.status 
                  FROM Transaksi t 
                  JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                  $filter_condition
                  ORDER BY t.id_transaksi DESC 
                  LIMIT 10";
$filtered_result = mysqli_query($conn, $filtered_query);

// Get top 5 customers
$top_customers_query = "SELECT * FROM vw_top5_pelanggan";
$top_customers_result = mysqli_query($conn, $top_customers_query);

// Get large transactions
$large_transactions_query = "SELECT * FROM vw_transaksi_besar LIMIT 5";
$large_transactions_result = mysqli_query($conn, $large_transactions_query);

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $new_status = sanitize($_POST['status']);
    
    $update_query = "UPDATE Transaksi SET status = '$new_status' WHERE id_transaksi = $transaction_id";
    if(mysqli_query($conn, $update_query)) {
        $success_message = "Status transaksi berhasil diperbarui.";
    } else {
        $error_message = "Gagal memperbarui status transaksi: " . mysqli_error($conn);
    }
    
    // Redirect to refresh the page
    redirect("index.php" . ($filter_week ? "?filter=week" : ""));
}

// Process transaction search
$search_result = null;
$search_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_transaction'])) {
    $search_id = sanitize($_POST['transaction_id']);
    // Remove # and leading zeros
    $search_id = ltrim($search_id, '#0');
    
    if (!empty($search_id) && is_numeric($search_id)) {
        $search_query = "SELECT t.*, p.nama, g.nama_game, pd.nama_paket, pd.jumlah_diamond 
                        FROM Transaksi t 
                        JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan
                        JOIN DetailTransaksi dt ON t.id_transaksi = dt.id_transaksi
                        JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket
                        JOIN Game g ON pd.id_game = g.id_game
                        WHERE t.id_transaksi = $search_id";
        $search_result = mysqli_query($conn, $search_query);
        
        if (mysqli_num_rows($search_result) == 0) {
            $search_error = "Transaksi dengan ID #" . str_pad($search_id, 8, '0', STR_PAD_LEFT) . " tidak ditemukan.";
        }
    } else {
        $search_error = "ID Transaksi tidak valid. Contoh format: #00000001";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            margin: 0;
            padding: 0;
        }
        
        .app-container {
            display: flex;
        }
        
        .app-content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #f5f7fb;
        }
        
        .dashboard-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .stat-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .btn-primary, .bg-primary {
            background-color: #ff7700 !important;
            border-color: #ff7700 !important;
        }
        
        .btn-outline-primary {
            color: #ff7700 !important;
            border-color: #ff7700 !important;
        }
        
        .btn-outline-primary:hover {
            background-color: #ff7700 !important;
            color: white !important;
        }
        
        .text-primary {
            color: #ff7700 !important;
        }
        
        .nav-pills .nav-link.active {
            background-color: #ff7700;
        }
        
        @media (max-width: 768px) {
            .app-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="app-content">
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">Dashboard</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="bi bi-share"></i> Share
                    </button>
                    <button class="btn btn-outline-primary" id="exportBtn">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar"></i> 
                            <?php echo $filter_week ? 'This Week' : 'All Time'; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item <?php echo !$filter_week ? 'active' : ''; ?>" href="index.php">All Time</a></li>
                            <li><a class="dropdown-item <?php echo $filter_week ? 'active' : ''; ?>" href="index.php?filter=week">This Week</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="stat-icon" style="background-color: rgba(255, 119, 0, 0.1); color: #ff7700;">
                                <i class="bi bi-cart"></i>
                            </div>
                            <div class="stat-label">Total Transaksi</div>
                            <div class="stat-value"><?php echo $total_transactions; ?></div>
                            <div class="stat-trend text-success">
                                <i class="bi bi-graph-up me-1"></i> +12% dari bulan lalu
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="stat-icon" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-label">Total Pelanggan</div>
                            <div class="stat-value"><?php echo $total_customers; ?></div>
                            <div class="stat-trend text-success">
                                <i class="bi bi-graph-up me-1"></i> +5% dari bulan lalu
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="stat-icon" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="stat-label">Total Pendapatan</div>
                            <div class="stat-value"><?php echo formatCurrency($total_revenue); ?></div>
                            <div class="stat-trend text-success">
                                <i class="bi bi-graph-up me-1"></i> +8% dari bulan lalu
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <div class="stat-icon" style="background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                                <i class="bi bi-controller"></i>
                            </div>
                            <div class="stat-label">Game Terpopuler</div>
                            <div class="stat-value" style="font-size: 20px;"><?php echo $top_game; ?></div>
                            <div class="stat-trend text-warning">
                                <i class="bi bi-star-fill me-1"></i> Rating 4.8/5
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Weekly Transaction Chart -->
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Transaksi Mingguan</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" id="chart7Days">7 Hari</button>
                        <button type="button" class="btn btn-outline-secondary" id="chart30Days">30 Hari</button>
                        <button type="button" class="btn btn-outline-secondary" id="chart90Days">90 Hari</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="transactionChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Transaction Search -->
            <div class="card dashboard-card mb-4" style="background: linear-gradient(135deg, #ff7700, #ff9d4c);">
                <div class="card-body p-4">
                    <h5 class="card-title text-white mb-3"><i class="bi bi-search me-2"></i>Cek Status Transaksi</h5>
                    <form method="post" action="" id="searchForm">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control form-control-lg" name="transaction_id" id="transaction_id" placeholder="Masukkan ID Transaksi (contoh: #00000006)" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="search_transaction" class="btn btn-light btn-lg w-100">
                                    <i class="bi bi-search me-2"></i>Cari Transaksi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Search Results -->
            <?php if ($search_result && mysqli_num_rows($search_result) > 0): ?>
                <?php $transaction = mysqli_fetch_assoc($search_result); ?>
                <div class="card dashboard-card mb-4" id="searchResult">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Hasil Pencarian Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Detail Transaksi</h6>
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
                        <div class="text-end mt-3">
                            <a href="transaction_detail.php?id=<?php echo $transaction['id_transaksi']; ?>" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>Lihat Detail Lengkap
                            </a>
                        </div>
                    </div>
                </div>
            <?php elseif ($search_error): ?>
                <div class="alert alert-danger" id="searchError">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $search_error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Recent Transactions -->
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Transaksi Terbaru</h5>
                    <span class="badge bg-primary rounded-pill"><?php echo mysqli_num_rows($filtered_result); ?> transaksi</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($filtered_result) > 0): ?>
                                    <?php while ($transaction = mysqli_fetch_assoc($filtered_result)): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($transaction['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo $transaction['nama']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($transaction['tanggal_transaksi'])); ?></td>
                                        <td><?php echo formatCurrency($transaction['total_harga']); ?></td>
                                        <td><?php echo getStatusLabel($transaction['status']); ?></td>
                                        <td>
                                            <a href="transaction_detail.php?id=<?php echo $transaction['id_transaksi']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">Tidak ada transaksi yang ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="transactions.php" class="btn btn-primary btn-sm">Lihat Semua Transaksi</a>
                    </div>
                </div>
            </div>
            
            <!-- Top Customers and Large Transactions -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Top 5 Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Pelanggan</th>
                                            <th>Total Belanja</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $i = 0;
                                        mysqli_data_seek($top_customers_result, 0);
                                        while ($customer = mysqli_fetch_assoc($top_customers_result)): 
                                            $i++;
                                            $status = '';
                                            if ($i == 1) $status = '<span class="badge bg-warning text-dark"><i class="bi bi-trophy"></i> Gold</span>';
                                            else if ($i == 2) $status = '<span class="badge bg-secondary"><i class="bi bi-trophy"></i> Silver</span>';
                                            else if ($i == 3) $status = '<span class="badge bg-danger"><i class="bi bi-trophy"></i> Bronze</span>';
                                            else $status = '<span class="badge bg-info"><i class="bi bi-person"></i> Regular</span>';
                                        ?>
                                        <tr>
                                            <td><?php echo $customer['nama_pelanggan']; ?></td>
                                            <td><?php echo formatCurrency($customer['total_belanja']); ?></td>
                                            <td><?php echo $status; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Transaksi Besar (>100k)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        mysqli_data_seek($large_transactions_result, 0);
                                        while ($large = mysqli_fetch_assoc($large_transactions_result)):
                                        ?>
                                        <tr>
                                            <td>#<?php echo str_pad($large['id_transaksi'], 8, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $large['nama_pelanggan']; ?></td>
                                            <td><?php echo formatCurrency($large['total_harga']); ?></td>
                                            <td>
                                                <a href="transaction_detail.php?id=<?php echo $large['id_transaksi']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <a href="reports.php?report=large_transactions" class="btn btn-primary btn-sm">Lihat Semua</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareModalLabel">Bagikan Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bagikan tautan dashboard ini dengan tim Anda:</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="https://diamondstore.com/admin/dashboard?token=abc123" id="shareLink" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">Salin</button>
                    </div>
                    <p class="mb-2">Atau bagikan melalui:</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary"><i class="bi bi-envelope"></i> Email</button>
                        <button class="btn btn-outline-success"><i class="bi bi-whatsapp"></i> WhatsApp</button>
                        <button class="btn btn-outline-info"><i class="bi bi-telegram"></i> Telegram</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart.js implementation
        const ctx = document.getElementById('transactionChart').getContext('2d');
        let transactionChart;
        
        // Initial chart data (7 days)
        const chartData7Days = {
            labels: <?php echo json_encode($chart_dates); ?>,
            datasets: [{
                label: 'Total Transaksi (Rp)',
                data: <?php echo json_encode($chart_values); ?>,
                backgroundColor: 'rgba(255, 119, 0, 0.2)',
                borderColor: 'rgba(255, 119, 0, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(255, 119, 0, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        };
        
        // Dummy data for 30 days
        const chartData30Days = {
            labels: Array.from({length: 30}, (_, i) => {
                const date = new Date();
                date.setDate(date.getDate() - (29 - i));
                return `${date.getDate()}/${date.getMonth() + 1}`;
            }),
            datasets: [{
                label: 'Total Transaksi (Rp)',
                data: Array.from({length: 30}, () => Math.floor(Math.random() * 500000) + 100000),
                backgroundColor: 'rgba(255, 119, 0, 0.2)',
                borderColor: 'rgba(255, 119, 0, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(255, 119, 0, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        };
        
        // Dummy data for 90 days
        const chartData90Days = {
            labels: Array.from({length: 12}, (_, i) => {
                const date = new Date();
                date.setDate(date.getDate() - (90 - i * 8));
                return `${date.getDate()}/${date.getMonth() + 1}`;
            }),
            datasets: [{
                label: 'Total Transaksi (Rp)',
                data: Array.from({length: 12}, () => Math.floor(Math.random() * 1500000) + 500000),
                backgroundColor: 'rgba(255, 119, 0, 0.2)',
                borderColor: 'rgba(255, 119, 0, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(255, 119, 0, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        };
        
        // Chart options
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                            return label;
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        };
        
        // Initialize chart with 7 days data
        transactionChart = new Chart(ctx, {
            type: 'line',
            data: chartData7Days,
            options: chartOptions
        });
        
        // Chart period buttons
        document.getElementById('chart7Days').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('chart30Days').classList.remove('active');
            document.getElementById('chart90Days').classList.remove('active');
            
            transactionChart.data = chartData7Days;
            transactionChart.update();
        });
        
        document.getElementById('chart30Days').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('chart7Days').classList.remove('active');
            document.getElementById('chart90Days').classList.remove('active');
            
            transactionChart.data = chartData30Days;
            transactionChart.update();
        });
        
        document.getElementById('chart90Days').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('chart7Days').classList.remove('active');
            document.getElementById('chart30Days').classList.remove('active');
            
            transactionChart.data = chartData90Days;
            transactionChart.update();
        });
        
        // Copy share link
        document.getElementById('copyLinkBtn').addEventListener('click', function() {
            const shareLink = document.getElementById('shareLink');
            shareLink.select();
            document.execCommand('copy');
            this.innerHTML = '<i class="bi bi-check"></i> Disalin';
            setTimeout(() => {
                this.innerHTML = 'Salin';
            }, 2000);
        });
        
        // Export to CSV
        document.getElementById('exportBtn').addEventListener('click', function() {
            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "ID,Pelanggan,Tanggal,Total,Status\n";
            
            // Add data rows
            <?php 
            mysqli_data_seek($filtered_result, 0);
            while ($row = mysqli_fetch_assoc($filtered_result)) {
                echo "csvContent += \"#" . str_pad($row['id_transaksi'], 8, '0', STR_PAD_LEFT) . ",";
                echo $row['nama'] . ",";
                echo date('d/m/Y', strtotime($row['tanggal_transaksi'])) . ",";
                echo "Rp " . number_format($row['total_harga'], 0, ',', '.') . ",";
                echo $row['status'] . "\\n\";\n";
            }
            ?>
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "transaksi_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            
            // Trigger download
            link.click();
        });
        
        // Transaction search form
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const transactionId = document.getElementById('transaction_id').value.trim();
            if (!transactionId) {
                e.preventDefault();
                alert('Silakan masukkan ID Transaksi');
            }
        });
    </script>
</body>
</html>
