<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Get featured games
$featured_query = "SELECT g.id_game, g.nama_game, g.developer 
                  FROM Game g 
                  ORDER BY RAND() 
                  LIMIT 5";
$featured_result = mysqli_query($conn, $featured_query);
$featured_games = [];
while ($row = mysqli_fetch_assoc($featured_result)) {
    $featured_games[] = $row;
}

// Get all games
$games_query = "SELECT * FROM Game ORDER BY nama_game";
$games_result = mysqli_query($conn, $games_query);

// Get voucher games (dummy data for UI)
$voucher_games = [
    ['id' => 1, 'name' => 'Steam Wallet', 'image' => 'steam-wallet.jpg'],
    ['id' => 2, 'name' => 'Google Play', 'image' => 'google-play.jpg'],
    ['id' => 3, 'name' => 'Garena Shell', 'image' => 'garena-shell.jpg'],
    ['id' => 4, 'name' => 'PlayStation Network', 'image' => 'psn.jpg'],
    ['id' => 5, 'name' => 'Nintendo eShop', 'image' => 'nintendo.jpg']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiamondStore - Top Up Game Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Banner Carousel -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://cdn.antaranews.com/cache/1200x800/2022/06/20/Logo-Baru-Free-Fire.jpg" class="d-block w-100" alt="Promo Banner 1">
                <div class="carousel-caption">
                    <h2>Promo Spesial Bulan Ini</h2>
                    <p>Dapatkan bonus 20% untuk setiap pembelian diamond Mobile Legends</p>
                    <a href="game.php?id=1" class="btn btn-primary">Top Up Sekarang</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://picsum.photos/1200/400?random=2" class="d-block w-100" alt="Promo Banner 2">
                <div class="carousel-caption">
                    <h2>Free Fire Diamond Sale</h2>
                    <p>Harga spesial untuk pembelian diamond Free Fire</p>
                    <a href="game.php?id=2" class="btn btn-primary">Top Up Sekarang</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://picsum.photos/1200/400?random=3" class="d-block w-100" alt="Promo Banner 3">
                <div class="carousel-caption">
                    <h2>PUBG Mobile UC</h2>
                    <p>Top up UC PUBG Mobile dengan harga termurah</p>
                    <a href="game.php?id=3" class="btn btn-primary">Top Up Sekarang</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    
    <div class="container mt-5">
        <!-- Search Box -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg border-0" placeholder="Cari game favorit Anda..." id="searchGame">
                            <button class="btn btn-primary px-4" type="button">
                                <i class="bi bi-search me-2"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produk Unggulan -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-fire text-danger me-2 fs-4"></i>
                    <h2 class="section-title mb-0">Produk Unggulan</h2>
                </div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
                    <?php foreach ($featured_games as $index => $game): 
                        $game_images = [
                            'Mobile Legends' => 'https://blob.v0.dev/mobile-legends.jpg',
                            'Free Fire' => 'https://blob.v0.dev/free-fire.jpg',
                            'PUBG Mobile' => 'https://blob.v0.dev/pubg-mobile.jpg',
                            'Genshin Impact' => 'https://blob.v0.dev/genshin-impact.jpg',
                            'Clash of Clans' => 'https://blob.v0.dev/clash-of-clans.jpg'
                        ];
                        
                        $image_url = isset($game_images[$game['nama_game']]) 
                            ? $game_images[$game['nama_game']] 
                            : "https://picsum.photos/300/150?random=" . ($index + 1);
                    ?>
                    <div class="col">
                        <a href="game.php?id=<?php echo $game['id_game']; ?>" class="text-decoration-none">
                            <div class="card h-100 game-card">
                                <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo $game['nama_game']; ?>">
                                <div class="card-body text-center py-2">
                                    <h6 class="card-title mb-0"><?php echo $game['nama_game']; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Voucher -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-ticket-perforated text-primary me-2 fs-4"></i>
                    <h2 class="section-title mb-0">Voucher</h2>
                </div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
                    <?php foreach ($voucher_games as $index => $voucher): ?>
                    <div class="col">
                        <a href="#" class="text-decoration-none">
                            <div class="card h-100 voucher-card">
                                <img src="https://picsum.photos/300/150?random=<?php echo $index + 10; ?>" class="card-img-top" alt="<?php echo $voucher['name']; ?>">
                                <div class="card-body text-center py-2">
                                    <h6 class="card-title mb-0"><?php echo $voucher['name']; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Semua Games -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-controller text-success me-2 fs-4"></i>
                    <h2 class="section-title mb-0">Semua Games</h2>
                </div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3" id="gamesGrid">
                    <?php 
                    mysqli_data_seek($games_result, 0);
                    while ($game = mysqli_fetch_assoc($games_result)): 
                        $game_images = [
                            'Mobile Legends' => 'https://blob.v0.dev/mobile-legends.jpg',
                            'Free Fire' => 'https://blob.v0.dev/free-fire.jpg',
                            'PUBG Mobile' => 'https://blob.v0.dev/pubg-mobile.jpg',
                            'Genshin Impact' => 'https://blob.v0.dev/genshin-impact.jpg',
                            'Clash of Clans' => 'https://blob.v0.dev/clash-of-clans.jpg'
                        ];
                        
                        $image_url = isset($game_images[$game['nama_game']]) 
                            ? $game_images[$game['nama_game']] 
                            : "https://picsum.photos/300/150?random=" . ($game['id_game'] + 20);
                    ?>
                    <div class="col game-item">
                        <a href="game.php?id=<?php echo $game['id_game']; ?>" class="text-decoration-none">
                            <div class="card h-100 game-card">
                                <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo $game['nama_game']; ?>">
                                <div class="card-body text-center py-2">
                                    <h6 class="card-title mb-0"><?php echo $game['nama_game']; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <!-- Website Topup Cepat dan Terpercaya -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-dark text-white border-0 shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-3">Website Topup Cepat dan Terpercaya!</h2>
                        <p class="text-center mb-4">DiamondStore menawarkan cara mudah dan cepat untuk top up game favorit Anda seperti Mobile Legends, Free Fire, dan PUBG Mobile. Tanpa ribet daftar, pembayaran fleksibel, dan proses instan, semua transaksi aman dan cepat. Nikmati pengalaman gaming tanpa hambatan, hanya di DiamondStore!</p>
                        
                        <div class="row g-4 mt-2">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <h5 class="mt-3">Pasti Lebih Murah</h5>
                                    <p class="text-white-50">Top-up game favoritmu dengan harga yang lebih terjangkau dibandingkan situs top-up lainnya.</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="feature-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <h5 class="mt-3">Pengiriman Instan</h5>
                                    <p class="text-white-50">Transaksi kalian selesai dalam hitungan detik, karena semua proses kami berjalan secara otomatis.</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="feature-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                        <i class="bi bi-percent"></i>
                                    </div>
                                    <h5 class="mt-3">Banyak Promo Menarik</h5>
                                    <p class="text-white-50">Dapatkan promo harga terbaik setiap minggu, cukup ikuti kami di media sosial untuk informasi terbaru.</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="feature-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <h5 class="mt-3">Jujur dan Terpercaya</h5>
                                    <p class="text-white-50">Setiap hari, ribuan transaksi top-up game dan pembelian voucher dilakukan oleh pelanggan kami.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Metode Pembayaran -->
        <div class="row mb-5">
            <div class="col-12">
                <h4 class="text-center mb-4">METODE PEMBAYARAN</h4>
                <div class="payment-methods">
                    <div class="row justify-content-center">
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=1" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=2" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=3" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=4" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=5" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <div class="card payment-card">
                                <div class="card-body p-2 text-center">
                                    <img src="https://picsum.photos/100/40?random=6" alt="Payment Method" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pesanan -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-clock-history text-primary me-2 fs-4"></i>
                            <h3 class="mb-0">Riwayat Pemesanan</h3>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Untuk user yang sudah login -->
                        <div class="text-center mb-3">
                            <p>Lihat semua riwayat transaksi Anda di DiamondStore.</p>
                            <a href="history.php" class="btn btn-primary">
                                <i class="bi bi-list-ul me-2"></i>Lihat Riwayat Pesanan
                            </a>
                        </div>
                        <?php else: ?>
                        <!-- Untuk user yang belum login -->
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="text-center mb-3">Masukkan email yang Anda gunakan saat melakukan pemesanan untuk melihat riwayat transaksi Anda.</p>
                                <form action="history.php" method="get">
                                    <div class="input-group mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Masukkan email Anda" required>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search me-2"></i>Lihat Riwayat
                                        </button>
                                    </div>
                                </form>
                                <p class="text-center text-muted small">Atau <a href="login.php" class="text-decoration-none">login</a> untuk melihat semua riwayat transaksi Anda.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cek Transaksi -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Cek Transaksi</h3>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Masukkan nomor transaksi" aria-label="Nomor Transaksi">
                                    <button class="btn btn-primary" type="button">Cek Transaksi</button>
                                </div>
                                <p class="text-center text-muted small">Pesanan tidak muncul? Mohon tunggu 1-2 jam. Lewat dari itu, silahkan hubungi kami via <a href="#" class="text-decoration-none">WhatsApp</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchGame').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const gameItems = document.querySelectorAll('.game-item');
            
            gameItems.forEach(item => {
                const gameTitle = item.querySelector('.card-title').textContent.toLowerCase();
                
                if (gameTitle.includes(searchValue)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
