<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE nama LIKE '%$search%' OR email LIKE '%$search%' OR no_hp LIKE '%$search%'";
}

// Get customers
$query = "SELECT * FROM Pelanggan $search_condition ORDER BY id_pelanggan DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Get total customers for pagination
$total_query = "SELECT COUNT(*) as total FROM Pelanggan $search_condition";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total = $total_row['total'];
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan | DiamondStore</title>
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
                    <h1 class="h2">Manajemen Pelanggan</h1>
                </div>
                
                <!-- Search -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="get" action="" class="row g-3">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari pelanggan..." value="<?php echo $search; ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <a href="customers.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Customers Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>No. HP</th>
                                        <th>Total Transaksi</th>
                                        <th>Total Belanja</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($customer = mysqli_fetch_assoc($result)): ?>
                                        <?php
                                        // Get transaction count and total spending for this customer
                                        $id_pelanggan = $customer['id_pelanggan'];
                                        $stats_query = "SELECT COUNT(*) as transaction_count, SUM(total_harga) as total_spending 
                                                      FROM Transaksi 
                                                      WHERE id_pelanggan = $id_pelanggan";
                                        $stats_result = mysqli_query($conn, $stats_query);
                                        $stats = mysqli_fetch_assoc($stats_result);
                                        $transaction_count = $stats['transaction_count'] ?: 0;
                                        $total_spending = $stats['total_spending'] ?: 0;
                                        ?>
                                        <tr>
                                            <td><?php echo $customer['id_pelanggan']; ?></td>
                                            <td><?php echo $customer['nama']; ?></td>
                                            <td><?php echo $customer['email']; ?></td>
                                            <td><?php echo $customer['no_hp']; ?></td>
                                            <td><?php echo $transaction_count; ?></td>
                                            <td><?php echo formatCurrency($total_spending); ?></td>
                                            <td>
                                                <a href="customer_detail.php?id=<?php echo $customer['id_pelanggan']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-3">Tidak ada pelanggan yang ditemukan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
