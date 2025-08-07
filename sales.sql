-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 07, 2025 at 11:09 PM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sales_spy`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_requests`
--

DROP TABLE IF EXISTS `access_requests`;
CREATE TABLE IF NOT EXISTS `access_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `access_requests`
--

INSERT INTO `access_requests` (`id`, `name`, `email`, `reason`, `created_at`) VALUES
(1, 'hammad.shahir@gmail.com', 'emmanuelfaruq002@gmail.com', 'hhhhhhhhhhhhhhhhhhhhhh', '2025-07-16 21:30:08'),
(2, 'hammad.shahir@gmail.com', 'emmanuelfaruq002@gmail.com', 'hhhhhhhhhhhhhhhhhhhhhh', '2025-07-16 21:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'de faruq', 'admin@gmail.com', '$2y$10$ZKuNdje2n00XrOLJERSTiuYljmfN3bIYm0jsfBC3ZzC94C2AALBUa', '2025-07-16 18:36:03');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `api_key` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `api_key`, `is_active`, `created_at`) VALUES
(11, 22, 'd6b21256230f621af463029876771a9225ed8416989bb8381ee569fe057af03a', 1, '2025-07-23 13:11:19'),
(14, 25, 'c8909fd36f74f42b82f494cebe526c640a1479ed4b463e875f0e83b4ea63c725', 1, '2025-07-23 16:09:49');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive','paused') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exports`
--

DROP TABLE IF EXISTS `exports`;
CREATE TABLE IF NOT EXISTS `exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `store_count` int(11) DEFAULT NULL,
  `exported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `store_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
CREATE TABLE IF NOT EXISTS `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('success','failed','locked','disabled') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `email`, `status`, `ip_address`, `country`, `region`, `city`, `latitude`, `longitude`, `browser`, `platform`, `device`, `timestamp`) VALUES
(1, 25, NULL, 'success', '::1', '', '', '', '', '', 'Chrome', 'Windows', 'Desktop', '2025-07-31 00:05:20');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_attempts`
--

DROP TABLE IF EXISTS `password_reset_attempts`;
CREATE TABLE IF NOT EXISTS `password_reset_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_reset_attempts`
--

INSERT INTO `password_reset_attempts` (`id`, `email`, `ip_address`, `created_at`) VALUES
(1, 'timileyinfaruq9@gmail.com', '::1', '2025-05-24 18:16:45');

-- --------------------------------------------------------

--
-- Table structure for table `payment_wallets`
--

DROP TABLE IF EXISTS `payment_wallets`;
CREATE TABLE IF NOT EXISTS `payment_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network` varchar(50) NOT NULL,
  `currency` varchar(20) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `instructions` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment_wallets`
--

INSERT INTO `payment_wallets` (`id`, `network`, `currency`, `wallet_address`, `instructions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'TRC-20', 'USDT', 'TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8QH', 'Send only USDT (TRC-20) to this address. Minimum confirmation required: 1.', 1, '2025-08-04 07:45:48', '2025-08-04 07:45:48');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `yearly_price` decimal(10,2) NOT NULL,
  `leads_per_month` int(11) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_popular` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `plan_name`, `description`, `monthly_price`, `yearly_price`, `leads_per_month`, `features`, `is_active`, `is_popular`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 'Perfect for small businesses just getting started with lead generation', '20.00', '192.00', 500, '[\"500 leads per month\", \"Basic filtering options\", \"Email and phone support\", \"Weekly database updates\"]', 1, 0, '2025-08-02 18:51:43', '2025-08-04 20:27:10'),
(2, 'Pro', 'Ideal for growing businesses with serious lead generation needs', '50.00', '480.00', 2000, '[\"2,000 leads per month\", \"Advanced filtering options\", \"Priority support\", \"Daily database updates\", \"CRM integration\", \"Email sequence automation\"]', 1, 1, '2025-08-02 18:51:43', '2025-08-03 09:31:20'),
(3, 'Enterprise', 'For large organizations with custom lead generation requirements', '150.00', '1440.00', 0, '[\"Unlimited leads\", \"Custom filtering options\", \"Dedicated account manager\", \"Real-time database updates\", \"Advanced API access\", \"Custom integration development\"]', 1, 0, '2025-08-02 18:51:43', '2025-08-02 18:51:43');

-- --------------------------------------------------------

--
-- Table structure for table `search_logs`
--

DROP TABLE IF EXISTS `search_logs`;
CREATE TABLE IF NOT EXISTS `search_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `filters_used` text,
  `search_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `language` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `product_count` int(11) DEFAULT NULL,
  `avg_price` decimal(10,2) DEFAULT NULL,
  `categories` text,
  `tech_stack` text,
  `shipping_destinations` text,
  `payment_methods` text,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `whatsapp_number` varchar(50) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `tiktok_url` varchar(255) DEFAULT NULL,
  `pinterest_url` varchar(255) DEFAULT NULL,
  `facebook_ads_count` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_name` enum('free','pro','enterprise') NOT NULL DEFAULT 'free',
  `credits_remaining` int(11) NOT NULL DEFAULT '1250',
  `credits_total` int(11) NOT NULL DEFAULT '2000',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `leads_balance` int(11) DEFAULT '1000',
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_name`, `credits_remaining`, `credits_total`, `start_date`, `end_date`, `is_active`, `leads_balance`, `status`) VALUES
(10, 22, 'free', 1000, 1000, '2025-07-23 13:11:19', NULL, 1, 1000, 'active'),
(13, 25, 'free', 1000, 1000, '2025-08-02 20:11:34', NULL, 1, 1000, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `txid` varchar(100) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `screenshot_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `txid`, `payment_type`, `amount`, `status`, `created_at`, `screenshot_path`) VALUES
(1, 25, 'TXN12345678hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', 'crypto', '49.99', 'success', '2025-08-04 23:40:30', NULL),
(2, 25, 'TXN87654321', 'crupto', '19.99', 'failed', '2025-08-04 23:40:30', NULL),
(3, 25, 'TXN99900011', 'crypto', '29.99', 'pending', '2025-08-04 23:40:30', NULL),
(4, 25, 'TXN22244455', 'crypto', '9.99', 'success', '2025-08-04 23:40:30', NULL),
(5, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e16', 'crypto', '20.00', 'pending', '2025-08-07 21:52:48', 'uploads/payment_screenshots/ss_689520300ce19_profile_25_1754594853.jpg'),
(6, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e19', 'crypto', '20.00', 'pending', '2025-08-07 22:48:26', 'uploads/payment_screenshots/ss_68952d3a4605c_profile_25_1754594853.jpg'),
(7, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e10', 'crypto', '20.00', 'pending', '2025-08-07 23:04:26', 'uploads/payment_screenshots/ss_689530fa27344_ss_68952ec083cf2_1754607296.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `txid_requests`
--

DROP TABLE IF EXISTS `txid_requests`;
CREATE TABLE IF NOT EXISTS `txid_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `txid` varchar(255) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','enterprise') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT '0',
  `last_failed_attempt` datetime DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `credits` int(11) NOT NULL DEFAULT '1250',
  `profile_picture` varchar(255) DEFAULT NULL,
  `twofa_secret` varchar(255) DEFAULT NULL,
  `twofa_enabled` tinyint(1) DEFAULT '0',
  `twofa_backup_codes` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_disabled` tinyint(1) DEFAULT '0',
  `account_status` enum('active','locked','disabled') DEFAULT 'active',
  `unlock_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `created_at`, `reset_token`, `reset_token_expiry`, `failed_attempts`, `last_failed_attempt`, `avatar_url`, `credits`, `profile_picture`, `twofa_secret`, `twofa_enabled`, `twofa_backup_codes`, `ip_address`, `city`, `is_disabled`, `account_status`, `unlock_time`) VALUES
(22, 'faru', 'emm@gmail.com', '08116533387', '$2y$10$Ya39CvXgc9jOxEOpi1u93O/.sgMGN5Wpq7N3Uljm7B4lnp2ucvjWO', 'user', '2025-07-23 13:11:19', NULL, NULL, 0, NULL, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
(25, 'faruq', 'ada@gmail.com', '08116533380', '$2y$10$A3lH0LMXTQ7NQ45oR6L/rO9E.okbHnZAqW8QxKWjKMsiKGmdUGKl2', 'user', '2025-07-23 16:09:49', NULL, NULL, 0, NULL, NULL, 1250, 'uploads/profile_pictures/profile_25_1754608123.jpg', NULL, 0, NULL, '::1', NULL, 0, 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_2fa`
--

DROP TABLE IF EXISTS `user_2fa`;
CREATE TABLE IF NOT EXISTS `user_2fa` (
  `user_id` int(11) NOT NULL,
  `secret` varchar(32) DEFAULT NULL,
  `backup_codes` text,
  `method` enum('none','app','sms','email') DEFAULT 'none',
  `phone` varchar(20) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_agent` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `user_agent`, `ip_address`, `last_active`, `created_at`, `city`, `country`) VALUES
(32, 25, '5u4jej2f7ffivdg9tui6373j5l', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-08-04 23:35:12', '2025-08-04 22:24:33', 'Unknown', 'Unknown'),
(33, 25, 'ksu7132hdbgni837a6nvds2vjg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '::1', '2025-08-07 23:08:46', '2025-08-07 20:29:29', 'Unknown', 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
CREATE TABLE IF NOT EXISTS `user_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `leads_count` int(11) DEFAULT '0',
  `campaigns_count` int(11) DEFAULT '0',
  `credits_used` int(11) DEFAULT '0',
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `exports`
--
ALTER TABLE `exports`
  ADD CONSTRAINT `exports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `search_logs`
--
ALTER TABLE `search_logs`
  ADD CONSTRAINT `search_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_2fa`
--
ALTER TABLE `user_2fa`
  ADD CONSTRAINT `user_2fa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
