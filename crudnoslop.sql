-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 02:08 AM
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
-- Database: `crudnoslop`
--

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `cat_id` bigint(20) UNSIGNED NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `cat_type` enum('รายรับ','รายจ่าย') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`cat_id`, `cat_name`, `cat_type`, `created_at`, `updated_at`) VALUES
(3, 'ขยะฟาร์ม', 'รายรับ', '2026-09-08 06:50:05', '2026-09-08 07:29:18'),
(4, 'อาหาร', 'รายรับ', '2026-09-08 06:50:38', '2026-09-08 07:29:10'),
(5, 'ค่ายาฟาร์ม', 'รายจ่าย', '2026-09-08 07:03:28', '2026-09-08 07:03:28'),
(6, 'วัตถุดิบทำอาหาร', 'รายจ่าย', '2026-09-08 07:13:33', '2026-09-08 07:13:33'),
(7, 'วัตถุดิบทำยา', 'รายจ่าย', '2026-09-08 07:13:42', '2026-09-08 07:13:42'),
(8, 'ยา', 'รายรับ', '2026-09-08 07:30:25', '2026-09-08 07:30:25');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_09_07_222331_create_expense_categories_table', 1),
(2, '2026_09_07_222332_create_transactions_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `ts_id` bigint(20) UNSIGNED NOT NULL,
  `cat_id` bigint(20) UNSIGNED NOT NULL,
  `ts_amount` decimal(16,2) NOT NULL,
  `ts_date` date NOT NULL,
  `ts_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`ts_id`, `cat_id`, `ts_amount`, `ts_date`, `ts_note`, `created_at`, `updated_at`) VALUES
(4, 3, 2610000000.00, '2026-08-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(5, 3, 3720000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(6, 3, 3170000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(7, 3, 3760000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(8, 3, 5740000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(9, 3, 3780000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(10, 3, 4230000000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(11, 3, 3210000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(12, 3, 3440000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(13, 3, 3840000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(14, 3, 3410000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(15, 3, 4390000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(16, 3, 3700000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(17, 3, 3650000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(18, 3, 3640000000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:22:35', '2026-09-08 14:22:35'),
(19, 5, 218795000.00, '2026-08-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(20, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(21, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(22, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(23, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(24, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(25, 5, 218795000.00, '2026-06-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(26, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(27, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(28, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(29, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(30, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(31, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(32, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56'),
(33, 5, 218795000.00, '2026-05-08', 'ฟาร์ม (นำเข้าจาก Garmoth)', '2026-09-08 14:27:56', '2026-09-08 14:27:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`ts_id`),
  ADD KEY `transactions_cat_id_foreign` (`cat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `cat_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `ts_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_cat_id_foreign` FOREIGN KEY (`cat_id`) REFERENCES `expense_categories` (`cat_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
