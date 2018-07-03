-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2017 at 05:19 AM
-- Server version: 10.1.26-MariaDB
-- PHP Version: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coinalert`
--

-- --------------------------------------------------------

--
-- Table structure for table `candlestick`
--

CREATE TABLE `candlestick` (
  `id` int(10) UNSIGNED NOT NULL,
  `candlestick` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candlestick`
--

INSERT INTO `candlestick` (`id`, `candlestick`, `created_at`, `updated_at`) VALUES
(1, 1800, '2017-11-16 08:40:14', '2017-11-24 04:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `config_coin`
--

CREATE TABLE `config_coin` (
  `id` int(10) UNSIGNED NOT NULL,
  `cryptocurrency` int(11) NOT NULL,
  `coin_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sma_period` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `ema_period` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `config_coin`
--

INSERT INTO `config_coin` (`id`, `cryptocurrency`, `coin_name`, `sma_period`, `created_at`, `updated_at`, `is_active`, `ema_period`) VALUES
(5, 1, 'ETH', 50, '2017-11-17 07:28:34', '2017-11-21 08:49:59', 1, 30),
(8, 1, 'BCH', 50, '2017-11-18 02:41:07', '2017-11-21 08:23:24', 1, 30),
(9, 1, 'ETC', 50, '2017-11-21 03:55:57', '2017-11-21 08:24:11', 1, 30),
(10, 1, 'XRP', 50, '2017-11-21 07:07:27', '2017-11-21 08:24:29', 1, 30),
(11, 1, 'STR', 50, '2017-11-21 08:24:52', '2017-11-21 08:24:52', 1, 30),
(12, 1, 'LTC', 50, '2017-11-21 08:25:05', '2017-11-21 08:25:05', 1, 30),
(13, 1, 'XMR', 50, '2017-11-21 08:25:16', '2017-11-21 08:25:16', 1, 30),
(14, 1, 'DASH', 50, '2017-11-21 08:25:24', '2017-11-21 08:25:24', 1, 30),
(15, 1, 'FCT', 50, '2017-11-21 08:25:31', '2017-11-21 08:25:31', 1, 30),
(16, 1, 'ZEC', 50, '2017-11-21 08:25:39', '2017-11-21 08:25:39', 1, 30),
(17, 1, 'XEM', 50, '2017-11-21 08:25:46', '2017-11-21 08:25:46', 1, 30),
(18, 1, 'LSK', 50, '2017-11-21 08:26:03', '2017-11-21 08:26:03', 1, 30),
(19, 1, 'BTS', 50, '2017-11-21 08:26:32', '2017-11-21 08:26:32', 1, 30),
(20, 1, 'DGB', 50, '2017-11-21 08:26:44', '2017-11-21 08:26:44', 1, 30),
(21, 1, 'SC', 50, '2017-11-21 08:27:21', '2017-11-21 08:27:21', 1, 30),
(22, 1, 'REP', 50, '2017-11-21 08:27:30', '2017-11-21 08:27:30', 1, 30);

-- --------------------------------------------------------

--
-- Table structure for table `config_line_group`
--

CREATE TABLE `config_line_group` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `config_line_group`
--

INSERT INTO `config_line_group` (`id`, `group_id`, `created_at`, `updated_at`) VALUES
(1, 'C84cbdab92208f1a8a19af9b8121f0f36', '2017-11-17 03:29:25', '2017-11-17 03:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `line_users`
--

CREATE TABLE `line_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `line_users`
--

INSERT INTO `line_users` (`id`, `user_id`, `display_name`, `created_at`, `updated_at`) VALUES
(1, 'Ud954ad654d39ba19de354885bfebb3bd', 'Pahgon', '2017-11-13 07:55:15', '2017-11-13 07:55:15'),
(2, 'U85aca22857d918bb5f21f0b5d2000c32', 'ABE', '2017-11-13 07:55:15', '2017-11-13 07:55:15'),
(3, 'Ucd26a423d7ac63b2f8cf384e1c3214b0', 'ABE', '2017-11-13 07:55:15', '2017-11-13 07:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `line_webhook_calls`
--

CREATE TABLE `line_webhook_calls` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci,
  `exception` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_content`
--

CREATE TABLE `message_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_type` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_content`
--

INSERT INTO `message_content` (`id`, `content`, `content_type`, `created_at`, `updated_at`) VALUES
(1, '【買いのシグナル】\n\n[CoinName]の買いのタイミングです！\nレート: [Rate]', 1, '2017-11-20 07:02:52', '2017-11-23 03:55:06'),
(2, '【売いのシグナル】\n\n[CoinName]コイン名の売りのタイミングです！\nレート: [Rate]', 2, '2017-11-20 07:03:00', '2017-11-23 03:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(13, '2014_10_12_000000_create_users_table', 1),
(14, '2014_10_12_100000_create_password_resets_table', 1),
(15, '2017_10_16_164342_create_config_coin_table', 1),
(16, '2017_10_16_164441_create_config_line_group_table', 1),
(17, '2017_10_16_164636_create_message_content_table', 1),
(18, '2017_11_13_104916_create_line_webhook_calls_table', 2),
(19, '2017_11_13_143733_create_line_users_table', 3),
(20, '2017_11_16_151520_create_candlestick_table', 4),
(21, '2017_11_27_153332_Create_TradeHistory_Table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trade_history`
--

CREATE TABLE `trade_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `coin_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pair` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buy_price` decimal(18,14) NOT NULL,
  `sell_price` decimal(18,14) NOT NULL,
  `profit` decimal(18,14) DEFAULT NULL,
  `bought_at` datetime NOT NULL,
  `sold_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trade_history`
--

INSERT INTO `trade_history` (`id`, `coin_name`, `pair`, `buy_price`, `sell_price`, `profit`, `bought_at`, `sold_at`, `created_at`, `updated_at`) VALUES
(1, 'ETH', 'BTC', '0.05329173000000', '0.04916120000000', NULL, '2017-11-26 06:00:00', '2017-11-27 17:30:00', '2017-11-27 10:41:33', '2017-11-27 10:41:33'),
(2, 'BCH', 'BTC', '0.17123349000000', '0.16932000000000', NULL, '2017-11-27 16:00:00', '2017-11-27 17:30:00', '2017-11-27 10:41:35', '2017-11-27 10:41:35'),
(3, 'ETC', 'BTC', '0.00236573000000', '0.00225336000000', NULL, '2017-11-26 12:30:00', '2017-11-27 17:30:00', '2017-11-27 10:41:38', '2017-11-27 10:41:38'),
(4, 'XRP', 'BTC', '0.00002892000000', '0.00002572000000', NULL, '2017-11-25 20:00:00', '2017-11-27 17:30:00', '2017-11-27 10:41:48', '2017-11-27 10:41:48'),
(5, 'STR', 'BTC', '0.00000502000000', '0.00000523000000', NULL, '2017-11-26 03:00:00', '2017-11-27 17:30:00', '2017-11-27 10:41:56', '2017-11-27 10:41:56'),
(6, 'LTC', 'BTC', '0.00967000000000', '0.00926882000000', NULL, '2017-11-26 18:00:00', '2017-11-27 17:30:00', '2017-11-27 10:42:20', '2017-11-27 10:42:20'),
(7, 'XMR', 'BTC', '0.01906500000000', '0.01677797000000', NULL, '2017-11-25 19:30:00', '2017-11-27 17:30:00', '2017-11-27 10:42:25', '2017-11-27 10:42:25'),
(8, 'DASH', 'BTC', '0.00000000000000', '0.00000000000000', NULL, '1970-01-01 08:00:00', '1970-01-01 08:00:00', '2017-11-27 10:42:28', '2017-11-27 10:42:28'),
(9, 'FCT', 'BTC', '0.00263542000000', '0.00231500000000', NULL, '2017-11-26 01:30:00', '2017-11-27 17:30:00', '2017-11-27 10:42:34', '2017-11-27 10:42:34'),
(10, 'LTC', 'BTC', '0.00930300000000', '0.00934770000000', NULL, '2017-11-28 09:00:00', '2017-11-28 09:30:00', '2017-11-28 02:45:22', '2017-11-28 02:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_root_admin` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `is_root_admin`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', '$2y$10$Xpl1QS7FER3potfX.72AcO6rmtprIwU2JH6FOgPrk/e5.WemmChea', 1, 1, NULL, NULL, '2017-11-24 08:16:48'),
(2, 'tuong pham', 'tuongpv', '$2y$10$ocz8hEFapNNuyy61vx8MWuw5HVsiJaYlT5z.nD4WYtE2ld0APgHTG', 0, 1, NULL, '2017-10-27 04:31:50', '2017-11-06 04:03:57'),
(3, 'tuongpv', 'tuongpv2', '$2y$10$kj4NfGSJQyVJDyu2ktsZ1emtMvCodbm/A.hrWnSFAd4CUBJP4JFm.', 0, 1, NULL, '2017-10-27 04:51:25', '2017-11-24 08:29:11'),
(4, 'test', 'test', '$2y$10$Gkq4uBtFkn2Vc/m8ff7AB.5UO8wLQqcelGRovUmXdhxnZRJSbFXde', 0, 1, NULL, '2017-11-17 07:19:47', '2017-11-17 07:19:47'),
(5, 'test02', 'test02', '$2y$10$sOEdyWKO1z.eAbSOHHC7cuJPKKPkRGP6pVz2tSG3vmwVikdsijP/G', 0, 1, NULL, '2017-11-17 10:22:43', '2017-11-17 10:22:43'),
(6, 'testtuong', 'testtuong', '$2y$10$lvXlBCVPXwr74KN3vqJr..fdnDNnQyZRnNSp1sKOxA56BDCHg9Jsu', 0, 1, NULL, '2017-11-18 02:04:46', '2017-11-18 02:04:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candlestick`
--
ALTER TABLE `candlestick`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config_coin`
--
ALTER TABLE `config_coin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config_line_group`
--
ALTER TABLE `config_line_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `line_users`
--
ALTER TABLE `line_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `line_webhook_calls`
--
ALTER TABLE `line_webhook_calls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_content`
--
ALTER TABLE `message_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `trade_history`
--
ALTER TABLE `trade_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candlestick`
--
ALTER TABLE `candlestick`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `config_coin`
--
ALTER TABLE `config_coin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `config_line_group`
--
ALTER TABLE `config_line_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `line_users`
--
ALTER TABLE `line_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `line_webhook_calls`
--
ALTER TABLE `line_webhook_calls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_content`
--
ALTER TABLE `message_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `trade_history`
--
ALTER TABLE `trade_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
