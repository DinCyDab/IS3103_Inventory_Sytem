-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 19, 2025 at 10:39 AM
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
(1, 'Juan', 'Dela Cruz', '$2y$10$B6DbJWTioOvMKLz/SaxFKutc4E844LP7VL1MQCOFBvrShwlN3/Czy', '1234@gmail.com', '0912 345 6789', 'admin', 'active'),
(2, 'James', 'Galon', '$2y$10$QXNaKJyJqUz76ZIPuK.k2OpgxQfZrLT63fnkbloXC8wRUg/omKcTi', '24100467@usc.edu.php', '0912 345 6789', 'admin', 'active'),
(3, 'Dino', 'Dabon', '$2y$10$Sovvezgs/KnYpbsBDAhBr.UuKOiA8BOnkkWhHhbZ5bojNMK..GDQy', '12345@gmail.com', '0921 443 9215', 'super_admin', 'active'),
(4, 'Miguel', 'Andaya', '$2y$10$JEH3gDnEZXcr.GhvUmQl/.r.xGPdAThpraelZ3jzzKx2dlsRQnTPm', '123@gmail.com', '0912 345 6789', 'staff', 'inactive'),
(5, 'Remegio', 'Magallanes', '$2y$10$YdJZJwZtY.iMnfZOkr1AzugaJSixMjnEaWzAmteuQdrvcvR.9vrZG', '12@gmail.com', '0912 345 6789', 'staff', 'active'),
(6, 'Kris', 'Mesola', '$2y$10$2QuNnCVoHCO82RrJwPZCBuV/UkVB2yr.JzGEtc9JD1/TarHPQNRo2', '1@gmail.com', '0912 345 6789', 'staff', 'active'),
(7, 'Gab', 'Arispe', '$2y$10$LcgWk1IiNLRH6oALdJ/IHOIyAHkTgn/8nm/35sbeP8JX3EuxRJSEm', '123456@gmail.com', '0912 345 6789', 'staff', 'active'),
(8, 'Maya', 'Maya', '$2y$10$l8C1JdhKH6SBDvXKqkOZv.S2j3tteRlAvWvSCYUoDfiJYCv3.V1gS', '12345678@gmail.com', '0912 345 6789', 'admin', 'active'),
(9, 'Shiori', 'Morisaka', '$2y$10$e0Zf/MF9oXn8NMPcry94wuJkTtKNR27K5ZRX8eBHfKN6mNmg0Pfim', '123456789@gmail.com', '0912 345 6789', 'admin', 'active'),
(10, 'Q', 'P', '$2y$10$Py1TQfUmBToGXSW2Ls/5V.dRpkxeemCRtXeImJUgmU2UWJoFNNQUu', '2@gmail.com', '0912 345 6789', 'admin', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `contact_number`, `email`, `address`, `created_at`) VALUES
(6, 'Shiori', NULL, NULL, NULL, '2025-12-19 06:12:46'),
(7, 'James', NULL, NULL, NULL, '2025-12-19 09:19:36'),
(8, 'Kris', NULL, NULL, NULL, '2025-12-19 09:25:46'),
(9, 'Maya', NULL, NULL, NULL, '2025-12-19 09:26:01');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `category_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `productID`, `productName`, `quantity`, `unit`, `price`, `expiryDate`, `created_at`, `image`, `status`, `category_id`) VALUES
(43, '1', 'Red Bull', 20, 'packets', '6767.00', '2025-12-25', '2025-12-02 10:25:01', '69302c01806d8_360_F_431621440_FFG7fwFMxdVlADPCaOPKOkD94nQkL1nQ.jpg', 'active', 33),
(44, '2', 'Maggi', 3, 'packets', '1245.00', '2028-09-12', '2025-12-03 15:18:26', '693054c2e1e2b_692a6c5d158c9_6926f0fd63b9d_692446b956eb3_MAGGIMAGICSARAP55G_grande.jpg', 'active', 32),
(45, '3', 'Oreo', 3, 'packets', '6032.00', '2025-09-12', '2025-12-06 06:39:20', '6933cf9870e80_692a6c25cdf1d_6926efab58f1f_69244da329381_100000093360-Oreo-Original-Sandwich-Cookies-Trial-Pack-6x27.6g-230907_dd55cbd0-ac97-4796-bd06-370be62d9a01.jpg', 'active', 31),
(46, '4', 'Ariel', 1, 'packets', '4095.00', '2028-09-12', '2025-12-06 06:40:37', '6933cfe54d5db_692c626e89b72_6927b73d1aad7_692456da98c47_105034909_1024x.png', 'active', 34),
(47, '5', 'Coca Cola', 0, 'packets', '6032.00', '2028-09-12', '2025-12-08 10:16:38', '6936a58632a71_692c62915878e_6926f12487559_692447236b86a_istockphoto-487787108-612x612.jpg', 'active', 33),
(48, '6', 'Oreo', 1, 'packets', '6032.00', '2025-09-12', '2025-12-08 15:01:13', '6936e839ca348_692a6c25cdf1d_6926efab58f1f_69244da329381_100000093360-Oreo-Original-Sandwich-Cookies-Trial-Pack-6x27.6g-230907_dd55cbd0-ac97-4796-bd06-370be62d9a01.jpg', 'active', 31),
(49, 'PRD-0001', 'Red Bull', 43, 'packets', '100000.00', '2025-09-12', '2025-12-10 02:23:31', '6938d9a3d916e_360_F_431621440_FFG7fwFMxdVlADPCaOPKOkD94nQkL1nQ.jpg', 'active', 33),
(50, 'PRD-0002', 'Maggi', 31, 'packets', '4095.00', '2028-12-25', '2025-12-18 10:28:09', '6943d739d435a_692a6c5d158c9_6926f0fd63b9d_692446b956eb3_MAGGIMAGICSARAP55G_grande.jpg', 'active', 32),
(51, 'PRD-0003', 'Oreo', 23, 'packets', '4095.00', '2028-12-25', '2025-12-19 03:33:45', '6944c7992553e_692c63b39b625_692c61eba832b_692a6c25cdf1d_6926efab58f1f_69244da329381_100000093360-Oreo-Original-Sandwich-Cookies-Trial-Pack-6x27.6g-230907_dd55cbd0-ac97-4796-bd06-370be62d9a01.jpg', 'active', 31);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_group` varchar(100) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `category_name`, `category_group`, `display_order`, `description`) VALUES
(31, 'Snacks', 'Miscellaneous', 99, NULL),
(32, 'Spices', 'Miscellaneous', 99, NULL),
(33, 'Beverage', 'Miscellaneous', 99, NULL),
(34, 'Cleaning Supplies', 'Miscellaneous', 99, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `sale_item_id` int(11) NOT NULL,
  `transaction_id` varchar(20) NOT NULL,
  `product_id` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`sale_item_id`, `transaction_id`, `product_id`, `product_name`, `quantity_sold`, `unit_price`, `subtotal`) VALUES
(1, 'TXN-00002', '1', 'Red Bull', 5, '6767.00', '33835.00'),
(2, 'TXN-00003', '1', 'Red Bull', 3, '6767.00', '20301.00'),
(3, 'TXN-00001', '2', 'Maggi', 12, '1245.00', '14940.00'),
(4, 'TXN-00004', '2', 'Maggi', 12, '1245.00', '14940.00'),
(5, 'TXN-00005', '2', 'Maggi', 14, '1245.00', '17430.00'),
(6, 'TXN-00007', '3', 'Oreo', 40, '6032.00', '241280.00'),
(7, 'TXN-00010', '3', 'Oreo', 40, '6032.00', '241280.00'),
(8, 'TXN-00012', '3', 'Oreo', 1, '6032.00', '6032.00'),
(9, 'TXN-00013', '3', 'Oreo', 3, '6032.00', '18096.00'),
(10, 'TXN-00006', '4', 'Ariel', 40, '4095.00', '163800.00'),
(11, 'TXN-00008', '5', 'Coca Cola', 35, '6032.00', '211120.00'),
(12, 'TXN-00009', '5', 'Coca Cola', 4, '6032.00', '24128.00'),
(13, 'TXN-00011', '5', 'Coca Cola', 5, '6032.00', '30160.00'),
(14, 'TXN-00007', '6', 'Oreo', 40, '6032.00', '241280.00'),
(15, 'TXN-00010', '6', 'Oreo', 40, '6032.00', '241280.00'),
(16, 'TXN-00012', '6', 'Oreo', 1, '6032.00', '6032.00'),
(17, 'TXN-00013', '6', 'Oreo', 3, '6032.00', '18096.00'),
(18, 'TXN-00002', 'PRD-0001', 'Red Bull', 5, '100000.00', '500000.00'),
(19, 'TXN-00003', 'PRD-0001', 'Red Bull', 3, '100000.00', '300000.00'),
(20, 'TXN-00001', 'PRD-0002', 'Maggi', 12, '4095.00', '49140.00'),
(21, 'TXN-00004', 'PRD-0002', 'Maggi', 12, '4095.00', '49140.00'),
(22, 'TXN-00005', 'PRD-0002', 'Maggi', 14, '4095.00', '57330.00'),
(32, 'TXN-00014', '4', 'Ariel', 10, '4095.00', '40950.00'),
(34, 'TXN-1766135524', 'PRD-0003', 'Oreo', 20, '4095.00', '81900.00'),
(35, 'TXN-1766135976', 'PRD-0002', 'Maggi', 10, '4095.00', '40950.00'),
(36, 'TXN-1766136346', '4', 'Ariel', 1, '4095.00', '4095.00'),
(37, 'TXN-1766136361', '5', 'Coca Cola', 1, '6032.00', '6032.00');

-- --------------------------------------------------------

--
-- Table structure for table `sales_transactions`
--

CREATE TABLE `sales_transactions` (
  `transaction_id` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `served_by` int(11) DEFAULT NULL,
  `status` enum('completed','cancelled','refunded') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sales_transactions`
--

INSERT INTO `sales_transactions` (`transaction_id`, `customer_id`, `customer_name`, `date_time`, `total_amount`, `payment_method`, `served_by`, `status`, `created_at`) VALUES
('TXN-00001', NULL, 'James', '2025-12-05 07:58:18', '6767.00', 'GCash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00002', NULL, 'Dino', '2025-12-05 10:00:23', '9999.00', 'Cash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00003', NULL, 'Kris', '2025-12-05 10:05:40', '1200.00', 'GCash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00004', NULL, 'Migs', '2025-12-05 10:20:46', '867.00', 'Card', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00005', NULL, 'Remegio', '2025-12-05 10:22:37', '1023.00', 'GCash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00006', NULL, 'James', '2025-12-06 06:41:20', '4000.00', 'Cash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00007', NULL, 'James', '2025-12-06 06:42:40', '6000.00', 'Card', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00008', NULL, 'James', '2025-12-08 10:17:16', '8000.00', 'Cash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00009', NULL, 'James', '2025-12-08 14:32:33', '50.00', 'GCash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00010', NULL, 'James', '2025-12-09 09:54:47', '9000.00', 'Cash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00011', NULL, 'Kris', '2025-12-09 10:38:51', '10.00', 'GCash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00012', NULL, 'Miguelito', '2025-12-10 10:47:36', '6032.00', 'Card', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00013', NULL, 'gab', '2025-12-10 11:08:32', '18096.00', 'Cash', NULL, 'completed', '2025-12-19 03:12:07'),
('TXN-00014', 6, 'Shiori', '2025-12-19 06:12:46', '40950.00', 'GCash', 1, 'completed', '2025-12-19 06:12:46'),
('TXN-1766135524', 6, 'Shiori', '2025-12-19 09:12:04', '81900.00', 'GCash', 3, 'completed', '2025-12-19 09:12:04'),
('TXN-1766135976', 7, 'James', '2025-12-19 09:19:36', '40950.00', 'Card', 3, 'completed', '2025-12-19 09:19:36'),
('TXN-1766136346', 8, 'Kris', '2025-12-19 09:25:46', '4095.00', 'GCash', 3, 'completed', '2025-12-19 09:25:46'),
('TXN-1766136361', 9, 'Maya', '2025-12-19 09:26:01', '6032.00', 'GCash', 3, 'completed', '2025-12-19 09:26:01');

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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `idx_customer_name` (`customer_name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productID` (`productID`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `idx_category_group` (`category_group`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_date_time` (`date_time`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_served_by` (`served_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `fk_sale_product` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`productID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `sales_transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD CONSTRAINT `fk_sales_account` FOREIGN KEY (`served_by`) REFERENCES `account` (`account_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
