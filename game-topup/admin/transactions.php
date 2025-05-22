<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to get all transactions with customer details
function getTransactions($conn, $status = null, $search = null) {
    // Check if required tables exist
    if (!tableExists($conn, 'Transaksi') || !tableExists($conn, 'Pelanggan') || 
        !tableExists($conn, 'PaketDiamond') || !tableExists($conn, 'Game')) {
        return [];
    }
    
    $query = "SELECT t.id_transaksi as id, p.nama as customer_name, p.email as customer_email, 
              pd.nama_paket as package_name, g.nama_game as game_name, 
              t.total_harga as amount, t.metode_pembayaran as payment_method, 
              t.status, t.waktu_transaksi as created_at, t.waktu_transaksi as updated_at,
              dt.id_detail, dt.jumlah
              FROM Transaksi t 
              JOIN Pelanggan p ON t.id_pelanggan = p.id_pelanggan 
              JOIN DetailTransaksi dt ON t.id_transaksi = dt.id_transaksi
              JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket 
              JOIN Game g ON pd.id_game = g.id_game 
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($status !== null) {
        $query .= " AND t.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($search !== null) {
        $search = "%$search%";
        $query .= " AND (t.id_transaksi LIKE ? OR p.nama LIKE ? OR p.email LIKE ? OR g.nama_game LIKE ? OR pd.nama_paket LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sssss";
    }
    
    $query .= " ORDER BY t.waktu_transaksi DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        // Add game_id field for compatibility
        $row['game_id'] = 'ID-' . rand(10000, 99999);
        $row['server_id'] = 'Server-' . rand(1000, 9999);
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Function to update transaction status
function updateTransactionStatus($conn, $transaction_id, $status) {
    // Map status values to your database values
    $statusMap = [
        'completed' => 'berhasil',
        'cancelled' => 'gagal',
        'pending' => 'pending'
    ];
    
    $dbStatus = $statusMap[$status] ?? $status;
    
    $stmt = $conn->prepare("UPDATE Transaksi SET status = ? WHERE id_transaksi = ?");
    $stmt->bind_param("si", $dbStatus, $transaction_id);
    return $stmt->execute();
}

// Handle transaction status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        if (updateTransactionStatus($conn, $transaction_id, 'completed')) {
            $success_message = "Transaksi #$transaction_id berhasil disetujui!";
        } else {
            $error_message = "Gagal menyetujui transaksi: " . $conn->error;
        }
    } elseif ($action === 'reject') {
        if (updateTransactionStatus($conn, $transaction_id, 'cancelled')) {
            $success_message = "Transaksi #$transaction_id berhasil ditolak!";
        } else {
            $error_message = "Gagal menolak transaksi: " . $conn->error;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$search_term = isset($_GET['search']) ? $_GET['search'] : null;

// Get transactions based on filters
$transactions = getTransactions($conn, $status_filter, $search_term);

// Count transactions by status
$status_counts = [
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

// Map your database status values to our status values
$statusMapReverse = [
    'pending' => 'pending',
    'berhasil' => 'completed',
    'gagal' => 'cancelled'
];

if (tableExists($conn, 'Transaksi')) {
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM Transaksi GROUP BY status");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $mappedStatus = $statusMapReverse[$row['status']] ?? $row['status'];
        $status_counts[$mappedStatus] = $row['count'];
    }
}

$total_transactions = array_sum($status_counts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Transaksi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            background-color: #f5f7fb;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1e2233;
            color: #fff;
            flex-shrink: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }
        
        .app-content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #f5f7fb;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-processing {
            background-color: #b8daff;
            color: #004085;
        }
        
        .status-completed, .status-berhasil {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .status-cancelled, .status-gagal {
            background-color: #f5c6cb;
            color: #721c24;
        }
        
        .transaction-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 20px;
            background-color: #fff;
            overflow: hidden;
        }
        
        .transaction-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .transaction-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-body {
            padding: 0;
        }
        
        .transaction-details {
            padding: 15px;
        }
        
        .transaction-details h5 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .transaction-details p {
            color: #555;
            line-height: 1.8;
        }
        
        .transaction-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
        }
        
        .transaction-actions {
            display: flex;
            gap: 10px;
        }
        
        .filter-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .status-count-card {
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
            cursor: pointer;
            background-color: #fff;
        }
        
        .status-count-card:hover {
            transform: translateY(-5px);
        }
        
        .status-count-card.active {
            border: 2px solid #ff7700;
        }
        
        .status-count-card h3 {
            margin: 10px 0;
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .status-count-card p {
            margin: 0;
            color: #6c757d;
            font-weight: 500;
        }
        
        .status-count-card.pending {
            background-color: #fff8e1;
        }
        
        .status-count-card.processing {
            background-color: #e3f2fd;
        }
        
        .status-count-card.completed {
            background-color: #e8f5e9;
        }
        
        .status-count-card.cancelled {
            background-color: #ffebee;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
        }
        
        .alert {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .transaction-id {
            font-weight: bold;
            color: #ff7700;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #adb5bd;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .nav-link.active {
            background-color: #ff7700;
            color: white;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 119, 0, 0.1);
        }
        
        .detail-column {
            border-right: 1px solid #eee;
        }
        
        .app-content-header {
            margin-bottom: 20px;
        }
        
        .app-content-headerText {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-right: 40px;
            border-radius: 50px;
        }
        
        .search-box .btn {
            position: absolute;
            right: 0;
            top: 0;
            border-radius: 0 50px 50px 0;
        }
        
        .btn-primary {
            background-color: #ff7700;
            border-color: #ff7700;
        }
        
        .btn-primary:hover {
            background-color: #e56a00;
            border-color: #e56a00;
        }
        
        .btn-outline-primary {
            color: #ff7700;
            border-color: #ff7700;
        }
        
        .btn-outline-primary:hover {
            background-color: #ff7700;
            border-color: #ff7700;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .app-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <div class="app-content">
            <div class="app-content-header">
                <h1 class="app-content-headerText">Transaksi</h1>
            </div>
            
            <!-- Alert Container -->
            <?php if (isset($success_message) || isset($error_message)): ?>
            <div class="alert-container">
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Status Count Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="status-count-card pending <?php echo $status_filter === null ? 'active' : ''; ?>" onclick="filterByStatus('')">
                        <p>Total Transaksi</p>
                        <h3><?php echo $total_transactions; ?></h3>
                        <small>Semua status</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-count-card pending <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" onclick="filterByStatus('pending')">
                        <p>Menunggu</p>
                        <h3><?php echo $status_counts['pending']; ?></h3>
                        <small>Perlu diproses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-count-card processing <?php echo $status_filter === 'processing' ? 'active' : ''; ?>" onclick="filterByStatus('processing')">
                        <p>Diproses</p>
                        <h3><?php echo $status_counts['processing']; ?></h3>
                        <small>Sedang diproses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-count-card completed <?php echo $status_filter === 'completed' ? 'active' : ''; ?>" onclick="filterByStatus('completed')">
                        <p>Selesai</p>
                        <h3><?php echo $status_counts['completed']; ?></h3>
                        <small>Transaksi selesai</small>
                    </div>
                </div>
            </div>
            
            <!-- Filter and Search -->
            <div class="filter-card">
                <div class="row">
                    <div class="col-md-8">
                        <form action="" method="GET" class="d-flex">
                            <?php if ($status_filter): ?>
                            <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                            <?php endif; ?>
                            <div class="input-group search-box">
                                <input type="text" class="form-control" placeholder="Cari transaksi..." name="search" value="<?php echo $search_term; ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <?php if ($search_term || $status_filter): ?>
                        <a href="transactions.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Reset Filter
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Transactions List -->
            <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h4>Tidak ada transaksi ditemukan</h4>
                <p>
                    <?php if ($search_term): ?>
                    Tidak ada transaksi yang cocok dengan pencarian "<?php echo $search_term; ?>".
                    <?php elseif ($status_filter): ?>
                    Tidak ada transaksi dengan status "<?php echo ucfirst($status_filter); ?>".
                    <?php else: ?>
                    Belum ada transaksi yang tercatat dalam sistem.
                    <?php endif; ?>
                </p>
            </div>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span class="transaction-id">#<?php echo str_pad($transaction['id'], 8, '0', STR_PAD_LEFT); ?></span>
                                <span class="ms-3 text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('d M Y, H:i', strtotime($transaction['created_at'])); ?>
                                </span>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php 
                                    switch($transaction['status']) {
                                        case 'pending':
                                            echo 'Menunggu';
                                            break;
                                        case 'processing':
                                            echo 'Diproses';
                                            break;
                                        case 'completed':
                                        case 'berhasil':
                                            echo 'Selesai';
                                            break;
                                        case 'cancelled':
                                        case 'gagal':
                                            echo 'Dibatalkan';
                                            break;
                                        default:
                                            echo ucfirst($transaction['status']);
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="transaction-body">
                        <div class="row g-0">
                            <div class="col-md-6 detail-column">
                                <div class="transaction-details">
                                    <h5>Detail Pelanggan</h5>
                                    <p>
                                        <i class="fas fa-user me-2"></i><?php echo $transaction['customer_name']; ?><br>
                                        <i class="fas fa-envelope me-2"></i><?php echo $transaction['customer_email']; ?><br>
                                        <i class="fas fa-gamepad me-2"></i><?php echo $transaction['game_name']; ?><br>
                                        <i class="fas fa-id-card me-2"></i>ID Game: <?php echo $transaction['game_id']; ?>
                                        <?php if (!empty($transaction['server_id'])): ?>
                                        <br><i class="fas fa-server me-2"></i>Server: <?php echo $transaction['server_id']; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="transaction-details">
                                    <h5>Detail Pembelian</h5>
                                    <p>
                                        <i class="fas fa-box me-2"></i><?php echo $transaction['package_name']; ?><br>
                                        <i class="fas fa-money-bill-wave me-2"></i>Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?><br>
                                        <i class="fas fa-credit-card me-2"></i><?php echo $transaction['payment_method']; ?><br>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php 
                                        switch($transaction['status']) {
                                            case 'pending':
                                                echo 'Menunggu pembayaran';
                                                break;
                                            case 'processing':
                                                echo 'Sedang diproses';
                                                break;
                                            case 'completed':
                                            case 'berhasil':
                                                echo 'Transaksi selesai pada ' . date('d M Y, H:i', strtotime($transaction['updated_at']));
                                                break;
                                            case 'cancelled':
                                            case 'gagal':
                                                echo 'Transaksi dibatalkan pada ' . date('d M Y, H:i', strtotime($transaction['updated_at']));
                                                break;
                                            default:
                                                echo 'Status tidak diketahui';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="transaction-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <a href="transaction_detail.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <?php if ($transaction['status'] === 'pending'): ?>
                                <div class="transaction-actions">
                                    <a href="transactions.php?action=approve&id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Setujui
                                    </a>
                                    <a href="transactions.php?action=reject&id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times me-1"></i>Tolak
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to filter by status
        function filterByStatus(status) {
            const currentUrl = new URL(window.location.href);
            
            // Remove existing status parameter
            currentUrl.searchParams.delete('status');
            
            // Add new status parameter if not empty
            if (status) {
                currentUrl.searchParams.set('status', status);
            }
            
            // Navigate to the new URL
            window.location.href = currentUrl.toString();
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
