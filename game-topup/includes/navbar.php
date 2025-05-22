<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <!-- <img src="https://picsum.photos/150/40?random=1" alt="DiamondStore" height="40"> -->
            <i class="bi bi-gem me-2"></i>DiamondStore
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active fw-bold' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="gamesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Games
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="gamesDropdown">
                        <?php
                        // Get all games for dropdown
                        $games_dropdown_query = "SELECT id_game, nama_game FROM Game ORDER BY nama_game LIMIT 10";
                        $games_dropdown_result = mysqli_query($conn, $games_dropdown_query);
                        
                        while ($game = mysqli_fetch_assoc($games_dropdown_result)) {
                            echo '<li><a class="dropdown-item" href="game.php?id=' . $game['id_game'] . '">' . $game['nama_game'] . '</a></li>';
                        }
                        ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php#gamesGrid">Lihat Semua Games</a></li>
                    </ul>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="#">Voucher</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kontak.php' ? 'active fw-bold' : ''; ?>" href="kontak.php">Kontak</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cekTransaksiModal">Cek Transaksi</a>
                </li>
            </ul>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['user_name']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                        <li><a class="dropdown-item" href="history.php">Riwayat Pesanan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Cek Transaksi Modal -->
<div class="modal fade" id="cekTransaksiModal" tabindex="-1" aria-labelledby="cekTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cekTransaksiModalLabel">Cek Status Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="cek-transaksi.php" method="get">
                    <div class="mb-3">
                        <label for="transactionId" class="form-label">ID Transaksi</label>
                        <input type="text" class="form-control" id="transactionId" name="id" placeholder="Contoh: #00000001" required>
                        <div class="form-text">Masukkan ID transaksi yang ingin Anda cek statusnya.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Cek Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
