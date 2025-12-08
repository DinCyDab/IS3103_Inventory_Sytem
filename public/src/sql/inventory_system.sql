-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 08, 2025 at 10:17 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_ID` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `role` enum('staff','admin','super_admin') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_ID`, `first_name`, `last_name`, `password`, `email`, `contact_number`, `role`, `status`) VALUES
(1, 'Juan', 'Dela Cruz', '$2y$10$B6DbJWTioOvMKLz/SaxFKutc4E844LP7VL1MQCOFBvrShwlN3/Czy', '1234@gmail.com', '', 'admin', 'active'),
(2, 'James', 'Galon', '$2y$10$QXNaKJyJqUz76ZIPuK.k2OpgxQfZrLT63fnkbloXC8wRUg/omKcTi', '24100467@usc.edu.php', '0921 443 9215', 'admin', 'active'),
(3, 'Dino', 'Dabon', '$2y$10$Sovvezgs/KnYpbsBDAhBr.UuKOiA8BOnkkWhHhbZ5bojNMK..GDQy', '12345@gmail.com', '0921 443 9215', 'super_admin', 'active'),
(4, 'Miguel', 'Andaya', '$2y$10$JEH3gDnEZXcr.GhvUmQl/.r.xGPdAThpraelZ3jzzKx2dlsRQnTPm', '123@gmail.com', '0921 443 9215', 'staff', 'active'),
(5, 'Remegio', 'Magallanes', '$2y$10$YdJZJwZtY.iMnfZOkr1AzugaJSixMjnEaWzAmteuQdrvcvR.9vrZG', '12@gmail.com', '0921 443 9215', 'staff', 'active'),
(6, 'Kris', 'Mesola', '$2y$10$2QuNnCVoHCO82RrJwPZCBuV/UkVB2yr.JzGEtc9JD1/TarHPQNRo2', '1@gmail.com', '0921 443 9215', 'staff', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `productID` varchar(50) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `productID`, `productName`, `quantity`, `unit`, `price`, `expiryDate`, `category`, `created_at`, `image`, `status`) VALUES
(43, '1', 'Red Bull', 2, 'packets', '6767.00', '2025-12-25', 'Beverage', '2025-12-02 10:25:01', '69302c01806d8_360_F_431621440_FFG7fwFMxdVlADPCaOPKOkD94nQkL1nQ.jpg', 'active'),
(44, '2', 'Maggi', 3, 'packets', '1245.00', '2028-09-12', 'Spices', '2025-12-03 15:18:26', '693054c2e1e2b_692a6c5d158c9_6926f0fd63b9d_692446b956eb3_MAGGIMAGICSARAP55G_grande.jpg', 'active'),
(45, '3', 'Oreo', 3, 'packets', '6032.00', '2025-09-12', 'Snacks', '2025-12-06 06:39:20', '6933cf9870e80_692a6c25cdf1d_6926efab58f1f_69244da329381_100000093360-Oreo-Original-Sandwich-Cookies-Trial-Pack-6x27.6g-230907_dd55cbd0-ac97-4796-bd06-370be62d9a01.jpg', 'active'),
(46, '4', 'Ariel', 1, 'packets', '4095.00', '2028-09-12', 'Cleaning Supplies', '2025-12-06 06:40:37', '6933cfe54d5db_692c626e89b72_6927b73d1aad7_692456da98c47_105034909_1024x.png', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `salesreport`
--

CREATE TABLE `salesreport` (
  `transaction_ID` varchar(20) NOT NULL,
  `date_time` datetime NOT NULL,
  `products` varchar(255) NOT NULL,
  `order_value` decimal(10,2) NOT NULL,
  `quantity_sold` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `payment_method` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `salesreport`
--

INSERT INTO `salesreport` (`transaction_ID`, `date_time`, `products`, `order_value`, `quantity_sold`, `customer_name`, `payment_method`) VALUES
('TXN-00001', '2025-12-05 07:58:18', 'Maggi', '6767.00', '12', 'James', 'GCash'),
('TXN-00002', '2025-12-05 10:00:23', 'Red Bull', '9999.00', '5', 'Dino', 'Cash'),
('TXN-00003', '2025-12-05 10:05:40', 'Red Bull', '1200.00', '3', 'Kris', 'GCash'),
('TXN-00004', '2025-12-05 10:20:46', 'Maggi', '867.00', '12', 'Migs', 'Card'),
('TXN-00005', '2025-12-05 10:22:37', 'Maggi', '1023.00', '14', 'Remegio', 'GCash'),
('TXN-00006', '2025-12-06 06:41:20', 'Ariel', '4000.00', '40', 'James', 'Cash'),
('TXN-00007', '2025-12-06 06:42:40', 'Oreo', '6000.00', '40', 'James', 'Card');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_ID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productID` (`productID`);

--
-- Indexes for table `salesreport`
--
ALTER TABLE `salesreport`
  ADD PRIMARY KEY (`transaction_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
