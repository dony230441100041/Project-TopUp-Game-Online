<?php
// Function to sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Function to get game details by ID
function getGameById($game_id) {
    global $conn;
    
    // Validate input
    $game_id = (int)$game_id;
    
    // Debug output
    // echo "Fetching game with ID: $game_id<br>";
    
    // Check if connection exists
    if (!$conn) {
        // Log error
        error_log("Database connection not available in getGameById()");
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM Game WHERE id_game = ?");
        if (!$stmt) {
            // Log error
            error_log("Failed to prepare statement: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $game = $result->fetch_assoc();
            $stmt->close();
            return $game;
        } else {
            // Log error
            error_log("No game found with ID: $game_id");
            $stmt->close();
            return null;
        }
    } catch (Exception $e) {
        // Log error
        error_log("Exception in getGameById(): " . $e->getMessage());
        return null;
    }
}

// Function to get diamond packages by game ID
function getPackagesByGameId($game_id) {
    global $conn;
    
    // Validate input
    $game_id = (int)$game_id;
    
    // Check if connection exists
    if (!$conn) {
        error_log("Database connection not available in getPackagesByGameId()");
        return [];
    }
    
    try {
        $packages = [];
        $stmt = $conn->prepare("SELECT * FROM PaketDiamond WHERE id_game = ? ORDER BY jumlah_diamond");
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
        
        $stmt->close();
        
        return $packages;
    } catch (Exception $e) {
        error_log("Exception in getPackagesByGameId(): " . $e->getMessage());
        return [];
    }
}

// Function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to get transaction status label with color
function getStatusLabel($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Pending</span>';
        case 'berhasil':
            return '<span class="badge bg-success">Berhasil</span>';
        case 'gagal':
            return '<span class="badge bg-danger">Gagal</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Function to get transaction count
function getTransactionCount() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM Transaksi";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Function to get customer count
function getCustomerCount() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM Pelanggan";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Function to get total revenue
function getTotalRevenue() {
    global $conn;
    $query = "SELECT SUM(total_harga) as total FROM Transaksi WHERE status = 'berhasil'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// Function to get top selling game
function getTopSellingGame() {
    global $conn;
    $query = "SELECT g.nama_game, COUNT(dt.id_detail) as total_sales 
              FROM Game g
              JOIN PaketDiamond pd ON g.id_game = pd.id_game
              JOIN DetailTransaksi dt ON pd.id_paket = dt.id_paket
              JOIN Transaksi t ON dt.id_transaksi = t.id_transaksi
              WHERE t.status = 'berhasil'
              GROUP BY g.id_game
              ORDER BY total_sales DESC
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['nama_game'] : 'N/A';
}
