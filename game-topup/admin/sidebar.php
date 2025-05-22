<?php
// Check if session is already active before starting
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="d-flex align-items-center text-decoration-none">
            <i class="bi bi-gem me-2 fs-4"></i>
            <h3 class="mb-0">DiamondStore</h3>
        </a>
    </div>
    
    <div class="admin-profile">
        <div class="d-flex align-items-center p-3">
            <div class="admin-avatar">
                <span>AD</span>
            </div>
            <div class="ms-3">
                <div class="admin-name">Administrator</div>
                <div class="admin-role">Super Admin</div>
            </div>
        </div>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="transactions.php" class="nav-link <?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart"></i>
                <span>Transaksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="games.php" class="nav-link <?php echo $current_page == 'games.php' ? 'active' : ''; ?>">
                <i class="bi bi-controller"></i>
                <span>Game</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="packages.php" class="nav-link <?php echo $current_page == 'packages.php' ? 'active' : ''; ?>">
                <i class="bi bi-gem"></i>
                <span>Paket Diamond</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="nav-link <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Pelanggan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart"></i>
                <span>Laporan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="profil.php" class="nav-link <?php echo $current_page == 'profil.php' ? 'active' : ''; ?>">
                <i class="bi bi-person"></i>
                <span>Profil</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    background-color: #1e2233;
    color: #fff;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 10;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
}

.admin-profile {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.admin-avatar {
    width: 45px;
    height: 45px;
    background-color: #4361ee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    border: 2px solid #ff7700;
}

.admin-name {
    font-size: 0.9rem;
    font-weight: 500;
}

.admin-role {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
}

.nav-item {
    margin: 5px 0;
}

.nav-link {
    color: rgba(255, 255, 255, 0.7);
    padding: 10px 15px;
    border-radius: 5px;
    margin: 0 10px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.nav-link i {
    font-size: 1.1rem;
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.nav-link:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.active {
    color: #fff;
    background-color: #ff7700;
    box-shadow: 0 4px 8px rgba(255, 119, 0, 0.3);
}

.sidebar-footer {
    margin-top: auto;
    padding: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout {
    background-color: #dc3545;
    color: #fff;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    border-radius: 5px;
    transition: all 0.3s;
}

.btn-logout:hover {
    background-color: #c82333;
    color: #fff;
}

.btn-logout i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    
    .sidebar-header h3,
    .admin-name,
    .admin-role,
    .nav-link span,
    .btn-logout span {
        display: none;
    }
    
    .nav-link i {
        margin-right: 0;
    }
    
    .nav-link {
        justify-content: center;
        padding: 10px;
    }
    
    .admin-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .admin-profile {
        display: flex;
        justify-content: center;
        padding: 10px 0;
    }
    
    .admin-profile .d-flex {
        justify-content: center;
    }
}
</style>
