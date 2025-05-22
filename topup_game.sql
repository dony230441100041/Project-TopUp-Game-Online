-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 06:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `topup_game`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_laporan_harian` (IN `p_start_date` DATE, IN `p_end_date` DATE)   BEGIN
    SELECT 
        DATE(t.tanggal_transaksi) as tanggal,
        COUNT(*) as jumlah_transaksi,
        SUM(t.total_harga) as total_penjualan
    FROM Transaksi t
    WHERE t.tanggal_transaksi BETWEEN p_start_date AND p_end_date
    GROUP BY DATE(t.tanggal_transaksi)
    ORDER BY DATE(t.tanggal_transaksi);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_laporan_penjualan_game` (IN `p_id_game` INT)   BEGIN
    SELECT 
        g.nama_game,
        pd.nama_paket,
        pd.jumlah_diamond,
        COUNT(dt.id_detail) as jumlah_terjual,
        SUM(dt.subtotal) as total_penjualan
    FROM Game g
    JOIN PaketDiamond pd ON g.id_game = pd.id_game
    LEFT JOIN DetailTransaksi dt ON pd.id_paket = dt.id_paket
    LEFT JOIN Transaksi t ON dt.id_transaksi = t.id_transaksi
    WHERE g.id_game = p_id_game OR p_id_game = 0
    GROUP BY g.nama_game, pd.nama_paket, pd.jumlah_diamond
    ORDER BY g.nama_game, pd.jumlah_diamond;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_user` (IN `p_username` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(255), IN `p_full_name` VARCHAR(100), IN `p_phone` VARCHAR(20), IN `p_verification_token` VARCHAR(100), OUT `p_user_id` INT)   BEGIN
    INSERT INTO Users (username, email, password, full_name, phone, verification_token)
    VALUES (p_username, p_email, p_password, p_full_name, p_phone, p_verification_token);
    
    SET p_user_id = LAST_INSERT_ID();
    
    -- Create a corresponding entry in Pelanggan table
    INSERT INTO Pelanggan (nama, email, no_hp, user_id)
    VALUES (p_full_name, p_email, p_phone, p_user_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_transaksi` (IN `p_id_pelanggan` INT, IN `p_metode_pembayaran` VARCHAR(50), IN `p_id_paket` INT, IN `p_jumlah` INT, OUT `p_id_transaksi` INT)   BEGIN
    DECLARE v_total_harga DECIMAL(10,2);
    DECLARE v_harga DECIMAL(10,2);
    
    -- Get package price
    SELECT harga INTO v_harga FROM PaketDiamond WHERE id_paket = p_id_paket;
    
    -- Calculate total price
    SET v_total_harga = v_harga * p_jumlah;
    
    -- Insert transaction
    INSERT INTO Transaksi (id_pelanggan, tanggal_transaksi, waktu_transaksi, total_harga, metode_pembayaran, status)
    VALUES (p_id_pelanggan, CURDATE(), NOW(), v_total_harga, p_metode_pembayaran, 'pending');
    
    -- Get the transaction ID
    SET p_id_transaksi = LAST_INSERT_ID();
    
    -- Insert transaction detail
    INSERT INTO DetailTransaksi (id_transaksi, id_paket, jumlah, subtotal)
    VALUES (p_id_transaksi, p_id_paket, p_jumlah, v_total_harga);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_transaksi_pelanggan` (IN `p_id_pelanggan` INT)   BEGIN
    SELECT 
        t.*,
        g.nama_game,
        pd.nama_paket,
        pd.jumlah_diamond
    FROM Transaksi t
    JOIN DetailTransaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN PaketDiamond pd ON dt.id_paket = pd.id_paket
    JOIN Game g ON pd.id_game = g.id_game
    WHERE t.id_pelanggan = p_id_pelanggan
    ORDER BY t.id_transaksi DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_status_transaksi` (IN `p_id_transaksi` INT, IN `p_status` VARCHAR(20))   BEGIN
    UPDATE Transaksi
    SET status = p_status
    WHERE id_transaksi = p_id_transaksi;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detailtransaksi`
--

CREATE TABLE `detailtransaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_paket` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailtransaksi`
--

INSERT INTO `detailtransaksi` (`id_detail`, `id_transaksi`, `id_paket`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 1, 15000.00),
(2, 2, 4, 1, 25000.00),
(3, 3, 7, 1, 40000.00),
(4, 4, 2, 1, 30000.00),
(5, 4, 6, 1, 25000.00),
(6, 5, 5, 1, 12000.00),
(7, 6, 2, 1, 30000.00),
(8, 7, 5, 1, 12000.00);

-- --------------------------------------------------------

--
-- Table structure for table `game`
--

CREATE TABLE `game` (
  `id_game` int(11) NOT NULL,
  `nama_game` varchar(100) DEFAULT NULL,
  `developer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game`
--

INSERT INTO `game` (`id_game`, `nama_game`, `developer`) VALUES
(1, 'Mobile Legends', 'Moonton'),
(2, 'Free Fire', 'Garena'),
(3, 'PUBG Mobile', 'Tencent'),
(4, 'Genshin Impact', 'miHoYo'),
(5, 'Clash of Clans', 'Supercell');

-- --------------------------------------------------------

--
-- Table structure for table `paketdiamond`
--

CREATE TABLE `paketdiamond` (
  `id_paket` int(11) NOT NULL,
  `id_game` int(11) DEFAULT NULL,
  `nama_paket` varchar(100) DEFAULT NULL,
  `jumlah_diamond` int(11) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paketdiamond`
--

INSERT INTO `paketdiamond` (`id_paket`, `id_game`, `nama_paket`, `jumlah_diamond`, `harga`) VALUES
(1, 1, 'Weekly Diamond Pass', 250, 15000.00),
(2, 1, 'Twilight Pass', 500, 30000.00),
(3, 2, '50 Diamonds', 50, 10000.00),
(4, 2, '140 Diamonds', 140, 25000.00),
(5, 3, 'UC 60', 60, 12000.00),
(6, 4, 'Genesis Crystals 300', 300, 50000.00),
(7, 5, '500 Gems', 500, 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `email`, `no_hp`, `user_id`) VALUES
(1, 'Andi Pratama', 'andi@gmail.com', '081234567890', NULL),
(2, 'Budi Santoso', 'budi@yahoo.com', '082198765432', NULL),
(3, 'Citra Lestari', 'citra@outlook.com', '081345678901', NULL),
(4, 'Dewi Anjani', 'dewi@gmail.com', '085612345678', NULL),
(5, 'Eka Saputra', 'eka@gmail.com', '089876543210', NULL),
(6, 'dony', 'oktadony25@gmail.com', '0812515450611', NULL),
(7, 'DONY123', 'oktadony2543@gmail.com', '0812515450612', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tanggal_transaksi` date DEFAULT NULL,
  `waktu_transaksi` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` enum('pending','berhasil','gagal') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pelanggan`, `tanggal_transaksi`, `waktu_transaksi`, `total_harga`, `metode_pembayaran`, `status`) VALUES
(1, 1, '2024-05-01', '2024-05-01 10:15:30', 15000.00, 'OVO', 'pending'),
(2, 2, '2024-05-02', '2024-05-02 14:22:45', 25000.00, 'Gopay', 'berhasil'),
(3, 3, '2024-05-03', '2024-05-03 09:30:12', 40000.00, 'ShopeePay', 'berhasil'),
(4, 1, '2024-05-04', '2024-05-04 16:45:33', 105000.00, 'Transfer Bank', 'berhasil'),
(5, 4, '2024-05-05', '2024-05-05 11:10:25', 12000.00, 'DANA', 'berhasil'),
(6, 6, '2025-05-22', '2025-05-22 13:15:15', 30000.00, 'OVO', 'berhasil'),
(7, 7, '2025-05-22', '2025-05-22 17:47:56', 12000.00, 'DANA', 'berhasil');

--
-- Triggers `transaksi`
--
DELIMITER $$
CREATE TRIGGER `after_transaksi_insert` AFTER INSERT ON `transaksi` FOR EACH ROW BEGIN
    -- You could log the transaction or perform other actions
    -- For example, insert into a transaction_log table
    -- INSERT INTO transaction_log (id_transaksi, action, timestamp)
    -- VALUES (NEW.id_transaksi, 'INSERT', NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `verification_token` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_detail_paket_game`
-- (See below for the actual view)
--
CREATE TABLE `vw_detail_paket_game` (
`id_detail` int(11)
,`id_game` int(11)
,`nama_game` varchar(100)
,`nama_paket` varchar(100)
,`jumlah_diamond` int(11)
,`jumlah` int(11)
,`subtotal` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_top5_pelanggan`
-- (See below for the actual view)
--
CREATE TABLE `vw_top5_pelanggan` (
`nama_pelanggan` varchar(100)
,`total_belanja` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_total_pembelian_pelanggan`
-- (See below for the actual view)
--
CREATE TABLE `vw_total_pembelian_pelanggan` (
`nama_pelanggan` varchar(100)
,`total_pembelian` decimal(32,2)
,`jumlah_transaksi` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_transaksi_besar`
-- (See below for the actual view)
--
CREATE TABLE `vw_transaksi_besar` (
`id_transaksi` int(11)
,`nama_pelanggan` varchar(100)
,`total_harga` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_transaksi_pelanggan`
-- (See below for the actual view)
--
CREATE TABLE `vw_transaksi_pelanggan` (
`id_transaksi` int(11)
,`nama_pelanggan` varchar(100)
,`tanggal_transaksi` date
,`total_harga` decimal(10,2)
,`metode_pembayaran` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_detail_paket_game`
--
DROP TABLE IF EXISTS `vw_detail_paket_game`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_detail_paket_game`  AS SELECT `dt`.`id_detail` AS `id_detail`, `g`.`id_game` AS `id_game`, `g`.`nama_game` AS `nama_game`, `pd`.`nama_paket` AS `nama_paket`, `pd`.`jumlah_diamond` AS `jumlah_diamond`, `dt`.`jumlah` AS `jumlah`, `dt`.`subtotal` AS `subtotal` FROM ((`detailtransaksi` `dt` join `paketdiamond` `pd` on(`dt`.`id_paket` = `pd`.`id_paket`)) join `game` `g` on(`pd`.`id_game` = `g`.`id_game`)) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_top5_pelanggan`
--
DROP TABLE IF EXISTS `vw_top5_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_top5_pelanggan`  AS SELECT `p`.`nama` AS `nama_pelanggan`, sum(`dt`.`subtotal`) AS `total_belanja` FROM ((`pelanggan` `p` join `transaksi` `t` on(`p`.`id_pelanggan` = `t`.`id_pelanggan`)) join `detailtransaksi` `dt` on(`t`.`id_transaksi` = `dt`.`id_transaksi`)) GROUP BY `p`.`nama` ORDER BY sum(`dt`.`subtotal`) DESC LIMIT 0, 5 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_total_pembelian_pelanggan`
--
DROP TABLE IF EXISTS `vw_total_pembelian_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_total_pembelian_pelanggan`  AS SELECT `p`.`nama` AS `nama_pelanggan`, sum(`dt`.`subtotal`) AS `total_pembelian`, count(`t`.`id_transaksi`) AS `jumlah_transaksi` FROM ((`pelanggan` `p` join `transaksi` `t` on(`p`.`id_pelanggan` = `t`.`id_pelanggan`)) join `detailtransaksi` `dt` on(`t`.`id_transaksi` = `dt`.`id_transaksi`)) GROUP BY `p`.`nama` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_transaksi_besar`
--
DROP TABLE IF EXISTS `vw_transaksi_besar`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_transaksi_besar`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `p`.`nama` AS `nama_pelanggan`, `t`.`total_harga` AS `total_harga` FROM (`transaksi` `t` join `pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) WHERE `t`.`total_harga` > 100000 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_transaksi_pelanggan`
--
DROP TABLE IF EXISTS `vw_transaksi_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_transaksi_pelanggan`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `p`.`nama` AS `nama_pelanggan`, `t`.`tanggal_transaksi` AS `tanggal_transaksi`, `t`.`total_harga` AS `total_harga`, `t`.`metode_pembayaran` AS `metode_pembayaran` FROM (`transaksi` `t` join `pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detailtransaksi`
--
ALTER TABLE `detailtransaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_paket` (`id_paket`);

--
-- Indexes for table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`id_game`);

--
-- Indexes for table `paketdiamond`
--
ALTER TABLE `paketdiamond`
  ADD PRIMARY KEY (`id_paket`),
  ADD KEY `id_game` (`id_game`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detailtransaksi`
--
ALTER TABLE `detailtransaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `game`
--
ALTER TABLE `game`
  MODIFY `id_game` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `paketdiamond`
--
ALTER TABLE `paketdiamond`
  MODIFY `id_paket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detailtransaksi`
--
ALTER TABLE `detailtransaksi`
  ADD CONSTRAINT `detailtransaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detailtransaksi_ibfk_2` FOREIGN KEY (`id_paket`) REFERENCES `paketdiamond` (`id_paket`);

--
-- Constraints for table `paketdiamond`
--
ALTER TABLE `paketdiamond`
  ADD CONSTRAINT `paketdiamond_ibfk_1` FOREIGN KEY (`id_game`) REFERENCES `game` (`id_game`);

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
