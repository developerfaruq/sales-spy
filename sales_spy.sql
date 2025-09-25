-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for sales_spy
CREATE DATABASE IF NOT EXISTS `sales_spy` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sales_spy`;

-- Dumping structure for table sales_spy.access_requests
CREATE TABLE IF NOT EXISTS `access_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.access_requests: ~2 rows (approximately)
INSERT INTO `access_requests` (`id`, `name`, `email`, `reason`, `created_at`) VALUES
	(1, 'hammad.shahir@gmail.com', 'emmanuelfaruq002@gmail.com', 'hhhhhhhhhhhhhhhhhhhhhh', '2025-07-16 21:30:08'),
	(2, 'hammad.shahir@gmail.com', 'emmanuelfaruq002@gmail.com', 'hhhhhhhhhhhhhhhhhhhhhh', '2025-07-16 21:30:31');

-- Dumping structure for table sales_spy.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.admins: ~1 rows (approximately)
INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
	(1, 'de faruq', 'admin@gmail.com', '$2y$10$ZKuNdje2n00XrOLJERSTiuYljmfN3bIYm0jsfBC3ZzC94C2AALBUa', '2025-07-16 18:36:03');

-- Dumping structure for table sales_spy.admin_actions
CREATE TABLE IF NOT EXISTS `admin_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `target_user_id` int DEFAULT NULL,
  `target_type` enum('user','subscription','payment','system') DEFAULT 'user',
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `target_user_id` (`target_user_id`),
  KEY `action_type` (`action_type`),
  CONSTRAINT `admin_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_actions_ibfk_2` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.admin_actions: ~42 rows (approximately)
INSERT INTO `admin_actions` (`id`, `admin_id`, `action_type`, `target_user_id`, `target_type`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, 'subscription_plan_changed', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 20:47:04'),
	(3, 1, 'subscription_plan_changed', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"enterprise","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 20:47:28'),
	(5, 1, 'subscription_plan_changed', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"free","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 20:56:50'),
	(7, 1, 'subscription_plan_changed', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-20 21:04:29","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 21:04:29'),
	(8, 1, 'subscription_paused', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:14:32","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 21:14:32'),
	(9, 1, 'subscription_cancelled', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 21:17:11'),
	(10, 1, 'subscription_created', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"3months","start_date":"2025-08-20 21:49:02","end_date":"2025-11-20 21:49:02","credits":2000,"notes":"nnnnnnnnnnnnnn"}', NULL, NULL, '2025-08-20 21:49:02'),
	(11, 1, 'subscription_paused', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:50:02","user_name":"devfaruq","user_email":"timi@gmail.com"}', NULL, NULL, '2025-08-20 21:50:02'),
	(13, 1, 'subscription_created', 26, 'user', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"1month","start_date":"2025-08-20 21:52:08","end_date":"2025-09-20 21:52:08","credits":2000,"notes":"n"}', NULL, NULL, '2025-08-20 21:52:08'),
	(14, 1, 'subscription_paused', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:39:11","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:39:11'),
	(15, 1, 'subscription_plan_changed', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:39:26","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:39:26'),
	(16, 1, 'subscription_plan_changed', 28, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:40:20","user_name":"faru","user_email":"emmanuelfaru002@gmail.com"}', NULL, NULL, '2025-08-24 10:40:20'),
	(18, 1, 'subscription_cancelled', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:45:31'),
	(19, 1, 'subscription_paused', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:45:38","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:45:38'),
	(20, 1, 'subscription_cancelled', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:45:42'),
	(21, 1, 'subscription_created', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"free","duration":"1month","start_date":"2025-08-24 10:46:30","end_date":null,"credits":1000,"notes":"nnnnn"}', NULL, NULL, '2025-08-24 10:46:30'),
	(22, 1, 'subscription_plan_changed', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"free","end_date":null,"user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-24 10:59:34'),
	(23, 1, 'subscription_plan_changed', 38, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:59:46","user_name":"faru","user_email":"emman@gmail.com"}', NULL, NULL, '2025-08-24 10:59:46'),
	(24, 1, 'subscription_paused', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:56:21","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-25 15:56:21'),
	(25, 1, 'subscription_cancelled', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-25 15:57:23'),
	(26, 1, 'subscription_paused', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:57:29","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-08-25 15:57:29'),
	(27, 1, 'subscription_created', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"enterprise","duration":"3months","start_date":"2025-08-25 15:57:55","end_date":"2025-11-25 15:57:55","credits":10000,"notes":""}', NULL, NULL, '2025-08-25 15:57:55'),
	(28, 1, 'wallet_updated', NULL, 'system', '{"admin_id":1,"admin_name":"de faruq","action":"wallet_updated","wallet_name":"USDT Receiving Wallet","wallet_address":"TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8Qq","network":"TRC-20","currency":"USDT"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-25 16:44:50'),
	(29, 1, 'wallet_updated', NULL, 'system', '{"admin_id":1,"admin_name":"de faruq","action":"wallet_updated","wallet_name":"USDT Receiving Wallet","wallet_address":"TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8Qhh","network":"TRC-20","currency":"USDT"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 21:05:20'),
	(34, 1, 'subscription_cancelled', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-09-10 05:48:44'),
	(35, 1, 'subscription_plan_changed', 39, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-10-10 06:00:11","user_name":"faru","user_email":"emma@gmail.com"}', NULL, NULL, '2025-09-10 06:00:11'),
	(37, 1, 'subscription_paused', 27, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-10-10 19:57:12","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', NULL, NULL, '2025-09-10 19:57:12'),
	(38, 1, 'subscription_created', 27, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":27,"user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","plan":"Basic","duration":"1month","start_date":"2025-09-10 20:02:24","end_date":"2025-10-10 20:02:24","credits":1000,"notes":"review"}', NULL, NULL, '2025-09-10 20:02:24'),
	(39, 1, 'transaction_approved', 40, 'payment', '{"transaction_id":"17","amount":"20.00","plan":"Basic","user_name":"ada","user_email":"ada@gmail.com"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 23:14:59'),
	(40, 1, 'subscription_paused', 38, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"kkk","duration":"1month","pause_end":"2025-10-16 23:53:32","user_name":"faru","user_email":"emman@gmail.com"}', NULL, NULL, '2025-09-16 23:53:32'),
	(41, 1, 'transaction_declined', 40, 'payment', '{"transaction_id":18,"amount":"50.00","user_name":"ada","user_email":"ada@gmail.com","reason":"Declined by admin"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-17 19:22:58'),
	(42, 1, 'subscription_paused', 40, 'user', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"flaged","duration":"1month","pause_end":"2025-10-17 19:24:20","user_name":"ada","user_email":"ada@gmail.com"}', NULL, NULL, '2025-09-17 19:24:20'),
	(43, 1, 'transaction_approved', 40, 'payment', '{"transaction_id":19,"amount":"50.00","plan":"Pro","user_name":"ada","user_email":"ada@gmail.com"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-17 19:27:02'),
	(44, 1, 'transaction_approved', 40, 'payment', '{"transaction_id":20,"amount":"480.00","plan":"Pro","user_name":"ada","user_email":"ada@gmail.com","new_subscription_id":"41","had_existing_subscription":true,"credits_assigned":2000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:24:38'),
	(45, 1, 'transaction_declined', 27, 'payment', '{"transaction_id":21,"amount":"480.00","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","reason":"Declined by admin"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:31:55'),
	(46, 1, 'transaction_approved', 27, 'payment', '{"transaction_id":22,"amount":"480.00","plan":"Pro","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","new_subscription_id":"42","had_existing_subscription":true,"credits_assigned":2000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:33:33'),
	(47, 1, 'transaction_declined', 27, 'payment', '{"transaction_id":23,"amount":"480.00","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","reason":"Declined by admin"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:38:28'),
	(48, 1, 'transaction_approved', 27, 'payment', '{"transaction_id":24,"amount":"480.00","plan":"Pro","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","new_subscription_id":"43","had_existing_subscription":true,"credits_assigned":2000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:41:55'),
	(49, 1, 'transaction_approved', 27, 'payment', '{"transaction_id":25,"amount":"20.00","plan":"Basic","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","new_subscription_id":"44","had_existing_subscription":true,"credits_assigned":1000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:46:22'),
	(50, 1, 'transaction_declined', 27, 'payment', '{"transaction_id":26,"amount":"50.00","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","reason":"Declined by admin"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:56:02'),
	(51, 1, 'transaction_approved', 27, 'payment', '{"transaction_id":27,"amount":"50.00","plan":"Pro","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","new_subscription_id":"45","had_existing_subscription":true,"credits_assigned":2000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:57:49'),
	(52, 1, 'transaction_approved', 27, 'payment', '{"transaction_id":28,"amount":"480.00","plan":"Pro","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","new_subscription_id":"46","had_existing_subscription":true,"credits_assigned":2000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 20:59:25');

-- Dumping structure for table sales_spy.admin_notifications
CREATE TABLE IF NOT EXISTS `admin_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `type` enum('info','warning','error','success') DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `related_user_id` int DEFAULT NULL,
  `related_table` varchar(50) DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `related_user_id` (`related_user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_notifications_ibfk_2` FOREIGN KEY (`related_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.admin_notifications: ~0 rows (approximately)

-- Dumping structure for table sales_spy.api_keys
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `api_key` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.api_keys: ~15 rows (approximately)
INSERT INTO `api_keys` (`id`, `user_id`, `api_key`, `is_active`, `created_at`) VALUES
	(15, 26, '569b953dac8d71beff877c7673f8526055c9211a4e22acbd233764e649806ab2', 1, '2025-08-17 21:01:10'),
	(16, 27, 'bf73c1ecd66367e520c6b8eeb58c0d2968a37bb8d249d80afe7cef4d3da05934', 1, '2025-08-24 10:36:30'),
	(17, 28, 'd3008e01542b5b0d6040e641b902ff5eec791d7d65c4ab95cfa3c8e188ac642d', 1, '2025-08-24 10:36:47'),
	(18, 29, '3815f6ecc264a5a6d50223c568c514a00484ce07393773e158228098887770b3', 1, '2025-08-24 10:37:02'),
	(19, 30, '22d6af7d75d0a68e6efbfb1305161164724200cfa2d686505bd88c8ae028de2e', 1, '2025-08-24 10:37:15'),
	(20, 31, '580ca2cabaaf569bf92b7828361cfb03b0a11e90babf44ccbe8932d4fcef91c5', 1, '2025-08-24 10:37:25'),
	(21, 32, '3e577b5c666e86cd6bbd75153b05bdc00d719a8ebdfddf6cc7d117262e1ae622', 1, '2025-08-24 10:37:34'),
	(22, 33, '9e3576eb568e354fb59ac026b268f15122017dc6e319a13a84a0e8a986dd7e6f', 1, '2025-08-24 10:37:44'),
	(23, 34, 'bb6f9732876ed9903fc7030489f3bca2e333a7176684b2b007506536f5cb98df', 1, '2025-08-24 10:37:53'),
	(24, 35, 'ba50fbb9ef1009c1e39213dd5aade61b3f2b6abf4e0f47ec867d93ee18393af7', 1, '2025-08-24 10:38:03'),
	(25, 36, 'c695bb010260312f27b05b9512e727d64b94cc13a7fd1b303d96ac1866c32bed', 1, '2025-08-24 10:38:15'),
	(26, 37, '524bd20ca1395b2ed39a9259ff54e4546ccb283f1fbe9de8f90d771213f1f33b', 1, '2025-08-24 10:38:26'),
	(27, 38, 'df8b7ac2c9b04fffadb858c7077549c4234c5969bb8680b504efe556cd4a6819', 1, '2025-08-24 10:38:39'),
	(28, 39, 'aea2cedf54bc04418200c4422bec51988e3f11a5c272bec3233f986573634a42', 1, '2025-08-24 10:38:52'),
	(29, 40, 'fdea4fa9d37a8a18a5ca2ad9aba527ffa2873ab8b4dee715e8a9ceea32ba8cc1', 1, '2025-09-10 06:15:58');

-- Dumping structure for table sales_spy.campaigns
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive','paused') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.campaigns: ~0 rows (approximately)

-- Dumping structure for table sales_spy.competitor_data
CREATE TABLE IF NOT EXISTS `competitor_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_domain` varchar(255) DEFAULT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `product_data` longtext,
  `scraped_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_domain` (`store_domain`),
  KEY `idx_scraped_at` (`scraped_at`),
  KEY `idx_store_domain` (`store_domain`)
) ENGINE=InnoDB AUTO_INCREMENT=336 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.competitor_data: ~42 rows (approximately)
INSERT INTO `competitor_data` (`id`, `store_domain`, `store_name`, `product_data`, `scraped_at`) VALUES
	(1, 'fashionnova.com', 'Fashion Nova', '[{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Cropped Studded Work Jacket - Black","price":"62600.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Cropped Striped Button Up Shirt - Green","price":"50100.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Tyson Just Go With It Short Sleeve Tee - Black Wash","price":"43800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Wide Leg Straight Striped Trouser - Green","price":"56300.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Suits You Best Faux Suede Skort Set - Burgundy","price":"86000.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Over It Sheer Chiffon Maxi Dress - Magenta\\/combo","price":"140700.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Midnight Drifter Faux Leather Jacket - Charcoal\\/combo","price":"81300.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Maid To Order 5 Piece Halloween Costume - Black\\/White","price":"54800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Emersyn Draped Maxi Dress - Navy","price":"62600.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"As If I\'m Clueless 3 Piece Halloween Costume - Yellow","price":"54800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Wild Thoughts Denim Shirt - Medium Wash","price":"31300.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Gwen Buckle One Shoulder Mini Dress - Heather Grey","price":"93800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Act Right Plaid Flannel Shirt - Black\\/combo","price":"31300.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"No Plans Tonight Sweater Top - Wine","price":"31300.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Keeva Faux Suede Off Shoulder Top - Burgundy","price":"28200.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Hot N\' Spicy Faux Leather Pant - Black","price":"62600.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Need For Speed 2 Piece Halloween Costume - Black\\/Red","price":"87600.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Double Booked Pinstripe Pant Set - Grey","price":"86000.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Sleek Satin Wide Leg Jumpsuit - Blue","price":"93800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Out Of Office Low Rise Wide Leg Trouser Pant 33\\u2033 - Chocolate","price":"43800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"The Giselle Vest - Grey","price":"31300.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Not Your Only Houndstooth Jacket - Pink\\/combo","price":"43800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Brew Babe 3 Piece Oktoberfest Halloween Costume - White\\/combo","price":"54800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Draya Deep V Midi Dress - White","price":"101600.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Marlowe Tweed Pant Set - Black","price":"93800.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Time Of My Life Pinstripe Poplin Shirt - White\\/combo","price":"37600.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Non Disclosure Stretch Denim Jacket - Cream","price":"56300.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Fall Fling Turtle Neck Sweater Top - Chocolate","price":"31300.00","availability":"in_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Hot Stuff Faux Fur Halter Top - Cream","price":"37600.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"},{"platform":"Shopify","store_name":"fashionnova.com","product_title":"Sweet Poison Chiffon One Shoulder Bloom - Cream","price":"37600.00","availability":"out_of_stock","last_updated":"2025-09-18T20:43:34+00:00"}]', '2025-09-18 20:43:34'),
	(2, 'gymshark.com', 'Gymshark', '[{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Speed Jacket - Black","price":"55.00","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Olympic Bar Tank - Metal Grey","price":"32.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Training Leggings - Heavy Blue","price":"19.00","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Gymshark Lifestyle Club Cotton Thong - Black","price":"3.60","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Waffle Cropped Tank - Iron Blue","price":"22.80","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Strength Department Graphic Sports Bra - Weighted Purple","price":"17.00","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Adapt Animal X Whitney Seamless Shorts - Barbell Blue\\/Gentle Blue","price":"22.00","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Adapt Animal X Whitney Seamless Midi Tank - Soft Brown\\/Archive Brown","price":"35.20","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Everyday Camera Bag - Athletic Burgundy","price":"32.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Everyday Camera Bag - Cloud Pink","price":"32.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Everyday Camera Bag - Black\\/Oat White","price":"32.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Vital V Neck Sports Bra - Sour Pink\\/Marl","price":"19.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Vital Crop Top - Sour Pink\\/Marl","price":"15.20","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Legacy Lifting Gloves - Classic Blue","price":"11.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Leather Lifting Straps - Asphalt Grey","price":"14.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Leather Lifting Straps - Camo Brown","price":"16.80","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Old School Lifting Cap - Black","price":"14.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Figure 8 Lifting Straps - Oat White","price":"10.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Everyday Holdall Small - Refresh Yellow","price":"22.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Velcro Weightlifting Belt - Strength Green","price":"22.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Lever Lifting Belt - Cool Blue","price":"102.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Sport Cap - Rest Blue","price":"15.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Lifting Dept Velcro Patch - White","price":"7.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Sharkhead Velcro Patch - Black","price":"6.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Wordmark Velcro Patch - Black","price":"7.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Legacy Velcro Patch - Black","price":"6.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Double Bicep 9\\" Shorts - Black","price":"30.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Heritage Washed T-Shirt - Stone Grey","price":"40.00","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Oversized Crest Crew - Rest Blue","price":"26.60","availability":"in_stock","last_updated":"2025-09-18T20:42:59+00:00"},{"platform":"Shopify","store_name":"gymshark.com","product_title":"Gymshark Supersoft Tank - Black","price":"19.00","availability":"out_of_stock","last_updated":"2025-09-18T20:42:59+00:00"}]', '2025-09-18 20:42:59'),
	(4, 'allbirds.com', 'Allbirds', '[{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Tree Runner Go - Twilight White\\/Hanami Orange (Twilight White Sole)","price":"96.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Tree Runner Go - Twilight White\\/Hanami Orange (Twilight White Sole)","price":"96.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Cruiser Slip On Corduroy - Dark Navy (Stony Cream Sole)","price":"105.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Dark Navy (Dark Navy Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Natural Black (Natural Black Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Espresso (Espresso Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Forest Green (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Dark Navy (Dark Navy Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Dark Camel (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Cruiser Slip On Corduroy - Stony Cream (Stony Cream Sole)","price":"105.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Sapphire Blue (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Poppy Red (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Sulphur (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Natural Black (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Forest Green (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Cruiser Slip On Corduroy - Dark Tan (Stony Cream Sole)","price":"105.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Wool Cruiser - Light Grey (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Sunshine (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Runner NZ Corduroy - Stony Cream (Stony Cream Sole)","price":"120.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Sapphire Blue (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Kelly Green (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Runner NZ Corduroy - Dark Navy (Stony Cream Sole)","price":"120.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Burgundy (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Citron (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Runner NZ Corduroy - Dark Tan (Stony Cream Sole)","price":"120.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Cruiser Slip On Corduroy - Stony Cream (Stony Cream Sole)","price":"105.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Sulphur (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Cruiser Corduroy - Dark Tan (Stony Cream Sole)","price":"110.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Women\'s Wool Cruiser - Natural Black (Natural White Sole)","price":"100.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"},{"platform":"Shopify","store_name":"allbirds.com","product_title":"Men\'s Runner NZ Corduroy - Dark Tan (Stony Cream Sole)","price":"120.00","availability":"in_stock","last_updated":"2025-09-18T20:43:16+00:00"}]', '2025-09-18 20:43:16'),
	(7, 'bombas.com', 'Bombas', '[]', '2025-09-18 20:25:58'),
	(10, 'outdoorvoices.com', 'Outdoor Voices', '[]', '2025-09-18 20:26:33'),
	(12, 'everlane.com', 'Everlane', '[]', '2025-09-18 20:27:09'),
	(14, 'rothys.com', 'Rothy\'s', '[]', '2025-09-18 20:27:44'),
	(15, 'girlfriend.com', 'Girlfriend Collective', '[]', '2025-09-18 20:28:19'),
	(18, 'glossier.com', 'Glossier', '[]', '2025-09-18 21:08:29'),
	(20, 'kyliecosmetics.com', 'Kylie Cosmetics', '[{"platform":"Shopify","store_name":"kyliecosmetics.com","product_title":"","price":"","availability":"unknown","last_updated":"2025-09-18T21:09:03+00:00"},{"platform":"Shopify","store_name":"kyliecosmetics.com","product_title":"","price":"","availability":"unknown","last_updated":"2025-09-18T21:09:03+00:00"}]', '2025-09-18 21:09:03'),
	(22, 'fentybeauty.com', 'Fenty Beauty', '[{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Match Stix Correcting Skinstick \\u2014 Pumpkin","price":"32.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Gloss Bomb Oil Luminizing Lip Oil \'N Gloss \\u2014 $uperfine $uga","price":"26.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Plush Puddin\' Intensive Recovery Lip Mask \\u2014 Cacao","price":"24.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Skincare Lov\'rs Cleanser, Toner, SPF Moisturizer + Collector\'s Case","price":"49.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Lil Butta Dropz Mini Whipped Oil Body Cream Duo","price":"34.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Butta Drop Whipped Oil Body Cream with Tropical Oils + Shea Butter \\u2014 Vanilla Dream","price":"46.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Chocolit Treatz Lip Luminizer + Lip Oil Duo","price":"25.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Fenty Eau de Parfum 75ML + Decorative Logo Tray","price":"140.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Gloss Bomb Swirl Twisted Lip Luminizer \\u2014 $weet RiRi","price":"24.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Fenty\'s Finest 3-Piece Face, Eye + Lip Kit","price":"35.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Glow\'n Xtra 2-Piece Lip Set","price":"25.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Lined + Luminized 2-Piece Lip Set","price":"24.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Glossy Posse VIII 3-Piece Lip Luminizer Set","price":"45.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"The Gloss Bomb Vault 10-Piece Full-Size Lip Set","price":"150.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"The Edgy Ones 2-Piece Edge Styling Set","price":"27.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"The Hydrated + Hot Ones Leave-in Conditioner + Heat Protectant Duo","price":"38.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Fenty Eau de Parfum Gift Set","price":"42.00","availability":"out_of_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"The Rich Curls 3-Piece Curl-Defining Routine","price":"48.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Gloss Bomb Universal Lip Luminizer \\u2014 Gimme Space","price":"22.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Gloss Bomb Universal Lip Luminizer \\u2014 Rose Amber","price":"22.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Pro Filt\'r Instant Retouch Setting Powder \\u2014 Lavender","price":"36.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Gloss Bomb Heat Universal Lip Luminizer + Plumper Deluxe Sample \\u2014 Hot Chocolit","price":"0.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Fenty Skin Headband","price":"14.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Mocha","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Cinnamon","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Honey","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Cashew","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Banana","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Butter","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"},{"platform":"Shopify","store_name":"fentybeauty.com","product_title":"Set it Down Superfine Blurring Setting Powder \\u2014 Strawberry Milk","price":"37.00","availability":"in_stock","last_updated":"2025-09-18T21:09:09+00:00"}]', '2025-09-18 21:09:09'),
	(24, 'theordinary.com', 'The Ordinary', '[]', '2025-09-18 21:09:44'),
	(26, 'drunkelephant.com', 'Drunk Elephant', '[]', '2025-09-18 21:10:12'),
	(28, 'tatcha.com', 'Tatcha', '[]', '2025-09-18 21:10:50'),
	(30, 'peakdesign.com', 'Peak Design', '[{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro Max | Cirrus","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro | Cirrus","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro Max | Kelp","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro Max | Ibis","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro Max | Black","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro | Kelp","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro | Ibis","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Gnar Case | iPhone 17 Pro | Black","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"GNAR Case for iPhone 17","price":"59.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Loop Case | iPhone 17 Pro Max | Black","price":"59.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Loop Case | iPhone 17 Pro Max | Tan","price":"59.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Loop Case | iPhone 17 Pro | Black","price":"59.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Loop Case | iPhone 17 Pro | Tan","price":"59.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro Max | Black","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro Max | Tan","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro Max | Sage","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro Max | Charcoal","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro | Tan","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro | Black","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro | Sage","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Pro | Charcoal","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Air | Sage","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 Air | Charcoal","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 | Sage","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case | iPhone 17 | Charcoal","price":"49.95","availability":"in_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case for iPhone 17","price":"49.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Slide | Tallac","price":"34.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Messenger V1 | 15L | Tan","price":"100.00","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Sage | Nylon","price":"49.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"},{"platform":"Shopify","store_name":"peakdesign.com","product_title":"Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Charcoal","price":"49.95","availability":"out_of_stock","last_updated":"2025-09-10T19:06:30+00:00"}]', '2025-09-10 19:06:30'),
	(32, 'ankerdirect.com', 'Anker', '[]', '2025-09-10 19:06:40'),
	(34, 'mvmtwatches.com', 'MVMT', '[]', '2025-09-10 19:07:04'),
	(36, 'popsockets.com', 'Popsockets', '[]', '2025-09-10 19:07:18'),
	(38, 'bellroy.com', 'Bellroy', '[]', '2025-09-10 19:07:30'),
	(40, 'brooklinen.com', 'Brooklinen', '[{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Super-Plush 4-Piece Bath Towel Bundle Checkout","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Test Classic Percale Hardcore Sheet Bundle","price":"245.60","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Content: September Throw Blankets","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Marlow Fleece Travel Blanket","price":"25.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Content: Washed European Linen","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Marbled Square Incense Holder - Last Call","price":"25.60","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Washed European Linen Duvet Cover - Last Call","price":"191.40","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Washed European Linen Pillowcase Set - Last Call","price":"19.75","availability":"out_of_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Washed European Linen Core Sheet Set - Last Call","price":"209.40","availability":"out_of_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Case Study Houses - Last Call","price":"12.80","availability":"out_of_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"The Five Minute Journal - Last Call","price":"23.20","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Essential Oil Blends - Last Call","price":"36.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Oil Diffuser - Last Call","price":"86.40","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Toilet Brush - Last Call","price":"57.60","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Wash Dryer - Last Call","price":"120.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Rolling Slim Bathroom Cart With Handle - Last Call","price":"116.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Bath Brush with Knob - Last Call","price":"36.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Content: September Ribbed Matelasse","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Content: September Lightweight Cotton","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Dreamweave Waffle Bed Blanket - Last Call","price":"113.40","availability":"out_of_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Baby Swaddle Set - Last Call","price":"25.20","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Prism Alpaca Throw Blanket","price":"299.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Basketweave Alpaca Throw Blanket","price":"269.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Prism Alpaca Lumbar Pillow Cover","price":"129.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Cotton Velvet Lumbar Pillow Cover","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Super-Plush Turkish Cotton Bath Towels Set Two","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Super-Plush Turkish Cotton Bath Towels Set One","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Navidium Shipping Protection","price":"2.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Classic Bed and Bath Bundle","price":"204.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"},{"platform":"Shopify","store_name":"brooklinen.com","product_title":"Super-Plush 4-Piece Bath Towel Bundle","price":"198.00","availability":"in_stock","last_updated":"2025-09-10T18:47:40+00:00"}]', '2025-09-10 18:47:40'),
	(41, 'parachutehome.com', 'Parachute', '[{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Plaid Wool Alpaca Throw (Natural Plaid)","price":"329.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Hand Stitched Euro Pillow Cover (Ivory and Soft Black)","price":"89.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Hand Stitched Bolster Pillow Cover (Cream and Tobacco)","price":"109.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Marfa Stripe Lumbar Pillow Cover (Soft Black and Natural)","price":"89.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Soft Cotton Quilted Sham Set (Bone)","price":"79.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Soft Cotton Quilt (Bone)","price":"269.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"4+4 Organic Ladder Stripe Bath Bundle - Moss and Dusk","price":"372.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Organic Ladder Stripe Bath Bundle - Toast and Natural","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Organic Ladder Stripe Towels (Tobacco and Cream)","price":"34.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Organic Ladder Stripe Towels (Moss and Dusk)","price":"34.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Organic Ladder Stripe Towels (Toast and Natural)","price":"34.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"6+6 Organic Ladder Stripe Bath Bundle - Tobacco and Cream","price":"558.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"4+4 Organic Ladder Stripe Bath Bundle - Tobacco and Cream","price":"372.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Organic Ladder Stripe Bath Bundle - Tobacco and Cream","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"6+6 Organic Ladder Stripe Bath Bundle - Moss and Dusk","price":"558.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Organic Ladder Stripe Bath Bundle - Moss and Dusk","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"6+6 Organic Ladder Stripe Bath Bundle - Toast and Natural","price":"558.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"4+4 Organic Ladder Stripe Bath Bundle - Toast and Natural","price":"372.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Organic Tassel Bath Rug (Warm Grey and Soft Black)","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Organic Tassel Bath Rug (Natural and Toast)","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Soft Rib Towels (Dusk)","price":"19.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Soft Rib Tub Mat (Dusk)","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"6+6 Soft Rib Bath Bundle - Dusk","price":"558.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"4+4 Soft Rib Bath Bundle - Dusk","price":"372.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Soft Rib Bath Bundle - Dusk","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"Soft Rib Towels (Light Grey)","price":"89.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Big Sur Bath Bundle","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"6+6 Ojai Bath Bundle","price":"558.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"4+4 Ojai Bath Bundle","price":"372.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"},{"platform":"Shopify","store_name":"parachutehome.com","product_title":"2+2 Ojai Bath Bundle","price":"186.00","availability":"in_stock","last_updated":"2025-09-10T18:47:48+00:00"}]', '2025-09-10 18:47:48'),
	(42, 'casper.com', 'Casper', '[{"platform":"Shopify","store_name":"casper.com","product_title":"Hyperlite Sheet Set","price":"109.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Percale Pillowcase Set","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Element Mattress","price":"445.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Lightweight Humidity Fighting Duvet","price":"309.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Build Your Own Bundle","price":"834.75","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Essential 3-Inch Mattress Topper","price":"155.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Essential 2-Inch Mattress Topper","price":"125.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"The Humidity Fighting Duvet","price":"359.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Product fees","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cloud One","price":"999.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Snow Hybrid Mattress 2023","price":"1495.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Casper Foam 2023","price":"595.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cozy Woven Blanket","price":"199.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle","price":"288.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - CalKing\\/Indigo","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - CalKing\\/White","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - CalKing\\/Gray","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - King\\/Indigo","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - King\\/White","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - King\\/Gray","price":"587.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Queen\\/Indigo","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Queen\\/White","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Queen\\/Gray","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Full\\/Indigo","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Full\\/White","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Full\\/Gray","price":"497.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - TwinXL\\/Indigo","price":"288.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - TwinXL\\/White","price":"288.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - TwinXL\\/Gray","price":"288.00","availability":"in_stock","last_updated":"2025-09-10T18:47:53+00:00"},{"platform":"Shopify","store_name":"casper.com","product_title":"Cool Sleep Bundle - Twin\\/Indigo","price":"288.00","availability":"out_of_stock","last_updated":"2025-09-10T18:47:53+00:00"}]', '2025-09-10 18:47:53'),
	(43, 'article.com', 'Article', '[]', '2025-09-10 18:48:06'),
	(44, 'burrow.com', 'Burrow', '[]', '2025-09-10 18:48:17'),
	(45, 'tuftandneedle.com', 'Tuft & Needle', '[{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Zip Comfort Pillow","price":"70.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Box Foundation","price":"236.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Basics Bundle","price":"180.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Essential Platform Bed Frame","price":"190.75","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Essential Platform Bed Frame - White","price":"190.75","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Essential Platform Bed Frame - Black","price":"190.75","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Essential Headboard","price":"103.25","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Basics Bundle - Standard","price":"180.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Basics Bundle - King","price":"199.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Box Foundation","price":"236.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Recycling Fee RI","price":"20.50","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Recycling Fee CA","price":"16.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Recycling Fee OR","price":"22.50","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Recycling Fee CT","price":"16.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Quilt - Ochre","price":"110.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Hemp Duvet Cover - Morning","price":"240.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Hemp Duvet Cover - Midnight","price":"240.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Hemp Duvet Cover - Cloud","price":"240.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Hemp Sheet Set - Cloud","price":"220.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Hemp Sheet Set - Honeycomb","price":"220.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Down Alternative Pillow Set - Standard","price":"120.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Classic Percale Pillowcase Set","price":"70.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Classic Percale Sheet Set","price":"160.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Linen-Cotton Blend Body Pillow Cover","price":"65.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Linen-Cotton Blend Body Pillow Cover - Martini Olive","price":"65.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Linen-Cotton Blend Body Pillow Cover - Muted Clay","price":"65.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Classic Percale Body Pillow Cover","price":"55.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Down Alternative Pillow Set","price":"120.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Classic Percale Body Pillow Cover - Sage","price":"55.00","availability":"in_stock","last_updated":"2025-09-10T18:48:23+00:00"},{"platform":"Shopify","store_name":"tuftandneedle.com","product_title":"Classic Percale Body Pillow Cover - Cloud","price":"55.00","availability":"out_of_stock","last_updated":"2025-09-10T18:48:23+00:00"}]', '2025-09-10 18:48:23'),
	(115, 'mirror.co', 'Mirror', '[]', '2025-09-18 20:22:48'),
	(119, 'hydroflask.com', 'Hydro Flask', '[]', '2025-09-18 20:23:23'),
	(123, 'theragun.com', 'Theragun', '[]', '2025-09-18 20:23:59'),
	(127, 'onepeloton.com', 'Peloton', '[]', '2025-09-18 20:24:38'),
	(130, 'lululemon.com', 'Lululemon', '[]', '2025-09-18 20:25:13'),
	(200, 'thrivemarket.com', 'Thrive Market', '[]', '2025-09-10 18:49:04'),
	(201, 'deathwishcoffee.com', 'Death Wish Coffee', '[{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Divine Balance Tin Sign","price":"30.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Divine Balance Mug","price":"38.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Divine Balance Klean Kanteen","price":"25.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Varsity Brews Tee","price":"30.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Varsity Brews Hoodie","price":"45.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Pumpkin Chai Klean Kanteen","price":"25.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"On My Way Toiletry Bag","price":"20.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"On My Way Luggage Tag","price":"8.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Pumpkin Chai Coffee","price":"13.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Summit Brew Ground Bundle","price":"46.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Pumpkin Chai Single-Serve Pods","price":"12.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Summit Brew Single-Serve Pod Bundle","price":"45.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Shrouded Mountain Mug","price":"38.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Sunrise Summit Sling","price":"13.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Higher Ground Tee & Water Bottle Bundle","price":"35.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Trellis Test Product","price":"19.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Blueberry Coffee","price":"19.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Make Waves Mug","price":"38.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Boiling Point Wall Thermometer","price":"12.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Rock The Boat Waterproof Bag","price":"15.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Grinds & Glares Sunglasses","price":"12.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Fade to Black Tee","price":"30.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Crumb Coffin Seat Insert","price":"12.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Rub Some Grinds On It First Aid Kit","price":"6.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"The Classic Kanteen","price":"40.00","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Classic Logo Windbreaker","price":"46.67","availability":"out_of_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Espresso Roast Single-Serve Pods","price":"14.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Espresso Roast Coffee","price":"19.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Blueberry Single-Serve Pods","price":"15.99","availability":"in_stock","last_updated":"2025-09-10T18:49:12+00:00"},{"platform":"Shopify","store_name":"deathwishcoffee.com","product_title":"Mocha Latte","price":"2.57","availability":"out_of_stock","last_updated":"2025-09-10T18:49:12+00:00"}]', '2025-09-10 18:49:12'),
	(202, 'athleticgreens.com', 'Athletic Greens', '[]', '2025-09-10 18:49:23'),
	(203, 'magicspoon.com', 'Magic Spoon', '[{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Pumpkin Spice Cereal","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Free Labor Day Gift!","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Free Labor Day Gift!","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Free Labor Day Gift!","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Protein + Fiber Cereal - New! Variety 4-Pack","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Influencer Sample Bundle","price":"48.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Magic Spoon Fiber Cereal 3-Pack \\u2013 Fruity, Honey Nut & Cinnamon Toast \\u2013 High Fiber, Plant Protein, Low Sugar \\u2013 Breakfast Cereal TTS","price":"45.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Magic Spoon Fiber Cereal 3-Pack \\u2013 Honey Nut (2x) & Cinnamon Toast \\u2013 High Fiber, Plant Protein, Low Sugar \\u2013 Breakfast Cereal TTS","price":"45.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Magic Spoon Fiber Cereal 3-Pack \\u2013 Honey Nut & Cinnamon Toast (2x) \\u2013 High Fiber, Plant Protein, Low Sugar \\u2013 Breakfast Cereal TTS","price":"45.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Magic Spoon Fruity 4-Pack Protein Cereal and Spoon","price":"43.99","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Mini Treats Variety Pack - 32 Mini Protein Treats (4 boxes)","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Gift! S\'mores Cereal - 7oz Box","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Marshmallow Mini Treats - 6 pack (FAIRE)","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Marshmallow Mini Treats 18g","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Chocolate Peanut Butter Mini Treats 18g","price":"9.75","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Order Protection","price":"0.98","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Delish Duo - 1 case (4 boxes)","price":"39.00","availability":"out_of_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Apple Cinnamon Cereal","price":"0.00","availability":"out_of_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Brunch Club Bundle - 2 boxes, 1 bowl and spoon set","price":"34.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Sunrise Snack Trio - 2 Cereal, 1 Treats","price":"29.25","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"The Summer Discovery Kit - 2 Cereal, 2 Granola, 2 Treats","price":"54.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"The Brunch Bunch - 4 boxes, 1 bowl and spoon set","price":"54.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Summer Variety Pack - 1 case (6 boxes)","price":"54.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Peaches & Cream + French Toast - 1 case (4 boxes)","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"The Party Pack - 16 Cereal Treats (4 Boxes)","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Strawberry Milkshake - 4 Cereal Treats","price":"0.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Birthday Cake - 16 Cereal Treats (4 Boxes)","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"French Toast + Peaches and Cream - 2 boxes TTS","price":"25.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"Final Days! French Toast + Peaches and Cream - 2 boxes","price":"19.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"},{"platform":"Shopify","store_name":"magicspoon.com","product_title":"The Peach Pack - 2 boxes","price":"19.00","availability":"in_stock","last_updated":"2025-09-10T18:49:28+00:00"}]', '2025-09-10 18:49:28'),
	(213, 'nomadgoods.com', 'Nomad', '[]', '2025-09-10 19:08:06'),
	(214, 'secretlab.co', 'Secretlab', '[{"platform":"Shopify","store_name":"secretlab.co","product_title":"IPA","price":"9999.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Memory Foam Lumbar Pillow - One Piece Edition","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"MAGNUS EVO 17 RESERVE","price":"799.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"MAGNUS EVO 15 RESERVE","price":"799.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab OTTO\\u2122 Adjustable Legrest (Plushcell\\u2122 Memory Foam)","price":"299.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"SECRETLAB SPARE PARTS - EVO REGULAR SEATBASE (3)","price":"0.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"SECRETLAB SPARE PARTS - EVO REGULAR BACKREST (3)","price":"0.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"CORPORATE SALES DESKS","price":"588.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"CORPORATE SALES CHAIRS","price":"549.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Lumbar Pillow Pro - McLaren Edition","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Ergonomic Recliner Cushion (PlushCell\\u2122 Memory Foam)","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"SECRETLAB SPARE PARTS - VERTICAL MONITOR STAND","price":"0.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Protective Floor Mat","price":"89.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS Vertical Monitor Stand Parts","price":"0.00","availability":"out_of_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS Vertical Monitor Stand only","price":"129.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Monitor Arm Connector Attachment only","price":"39.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS Single Monitor Arm with Connector Add-On","price":"149.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS Vertical Monitor Stand Setup","price":"349.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS Metal Desk - 1.5m (B4M)","price":"549.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab MAGNUS 1.5m + Secretlab MAGPAD\\u2122 (Bundle) (B4M)","price":"588.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab TITAN Evo - XL - NanoGen\\u2122 Hybrid Leatherette (0108)","price":"849.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab TITAN Evo - Regular - NanoGen\\u2122 Hybrid Leatherette (0107)","price":"799.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Memory Foam Lumbar Pillow - Arcane Heimerdinger Edition","price":"69.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab SKINS Lite - XL","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab SKINS Lite - Regular","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab SKINS Lite - Small","price":"99.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Ergonomic Recliner Add-On (Plushcell\\u2122 Memory Foam)","price":"249.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Chair Token","price":"29.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Digital Gift Card for Physical Gift Box","price":"569.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"},{"platform":"Shopify","store_name":"secretlab.co","product_title":"Secretlab Magnus Pro XL 1.7m Metal Desk Top - Box 1 - Low Voltage","price":"450.00","availability":"in_stock","last_updated":"2025-09-10T19:08:12+00:00"}]', '2025-09-10 19:08:12'),
	(215, 'razer.com', 'Razer', '[]', '2025-09-10 19:08:36'),
	(216, 'blueyeti.com', 'Blue Yeti', '[]', '2025-09-10 19:08:43'),
	(239, 'mejuri.com', 'Mejuri', '[]', '2025-09-18 21:06:57'),
	(241, 'pandora.net', 'Pandora', '[]', '2025-09-18 21:07:32'),
	(242, 'baublebar.com', 'BaubleBar', '[]', '2025-09-18 21:07:46'),
	(243, 'gorjana.com', 'Gorjana', '[{"platform":"Shopify","store_name":"gorjana.com","product_title":"Bespoke Bar Adjustable Necklace (silver)","price":"80.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Bespoke Bar Adjustable Necklace (gold)","price":"80.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Good Vibrations Parker Charm","price":"48.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lightning Strikes Paracord Bracelet","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Atlas Delicate Lariat","price":"88.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Crew Stacked Hoops","price":"78.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Good Vibrations Paracord Bracelet","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Bryce Necklace","price":"118.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lose Yourself Parker Charm","price":"48.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lou Stacked Hoops","price":"78.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Love U Ring","price":"70.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Feeling Lucky Necklace","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lose Yourself Coin Necklace","price":"85.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lou Mixed Stacked Huggies","price":"68.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lightning Strikes Coin Necklace","price":"85.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Feeling Lucky Coin Necklace","price":"85.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lightning Strikes Parker Charm","price":"48.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Reed Link Bracelet","price":"68.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lou Stacked Huggies","price":"68.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Love U Coin Necklace","price":"85.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lou Mixed Stacked Hoops","price":"78.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Good Vibrations Coin Necklace","price":"85.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Feeling Lucky Paracord Bracelet","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Love U Paracord Bracelet","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Good Vibrations Ring","price":"70.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lose Yourself Necklace","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Feeling Lucky Parker Charm","price":"48.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lightning Strikes Ring","price":"70.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Atlas Lariat","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"},{"platform":"Shopify","store_name":"gorjana.com","product_title":"Lightning Strikes Necklace","price":"98.00","availability":"in_stock","last_updated":"2025-09-18T21:08:08+00:00"}]', '2025-09-18 21:08:08');

-- Dumping structure for table sales_spy.exports
CREATE TABLE IF NOT EXISTS `exports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `store_count` int DEFAULT NULL,
  `exported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `exports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.exports: ~0 rows (approximately)

-- Dumping structure for table sales_spy.intelligence_metadata
CREATE TABLE IF NOT EXISTS `intelligence_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last_collection_run` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `stores_collected` int DEFAULT '0',
  `products_collected` int DEFAULT '0',
  `categories_processed` text,
  `status` varchar(50) DEFAULT 'success',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.intelligence_metadata: ~12 rows (approximately)
INSERT INTO `intelligence_metadata` (`id`, `last_collection_run`, `stores_collected`, `products_collected`, `categories_processed`, `status`) VALUES
	(1, '2025-08-25 20:34:24', 5, 0, 'fitness,fashion', 'success'),
	(2, '2025-08-25 20:42:25', 5, 0, 'fitness,fashion', 'success'),
	(3, '2025-09-05 14:16:19', 4, 60, 'food,home', 'success'),
	(4, '2025-09-05 15:09:22', 5, 30, 'electronics,tech', 'success'),
	(5, '2025-09-07 17:43:54', 0, 0, 'beauty,jewelry', 'success'),
	(6, '2025-09-08 18:22:53', 10, 90, 'home,food', 'success'),
	(7, '2025-09-08 19:09:14', 4, 30, 'tech,electronics', 'success'),
	(8, '2025-09-10 18:49:59', 10, 180, 'home,food', 'success'),
	(9, '2025-09-10 19:09:14', 4, 30, 'tech,electronics', 'success'),
	(10, '2025-09-10 20:09:24', 0, 0, 'fitness,fashion', 'success'),
	(11, '2025-09-18 20:25:46', 0, 0, 'fitness,fashion', 'success'),
	(12, '2025-09-18 21:09:22', 0, 0, 'jewelry,beauty', 'success');

-- Dumping structure for table sales_spy.leads
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `store_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.leads: ~0 rows (approximately)

-- Dumping structure for table sales_spy.password_reset_attempts
CREATE TABLE IF NOT EXISTS `password_reset_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.password_reset_attempts: ~1 rows (approximately)
INSERT INTO `password_reset_attempts` (`id`, `email`, `ip_address`, `created_at`) VALUES
	(1, 'timileyinfaruq9@gmail.com', '::1', '2025-05-24 18:16:45');

-- Dumping structure for table sales_spy.payment_wallets
CREATE TABLE IF NOT EXISTS `payment_wallets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `network` varchar(50) NOT NULL,
  `currency` varchar(20) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `instructions` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.payment_wallets: ~3 rows (approximately)
INSERT INTO `payment_wallets` (`id`, `network`, `currency`, `wallet_address`, `instructions`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'TRC-20', 'USDT', 'TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8QH', 'Send only USDT (TRC-20) to this address. Minimum confirmation required: 1.', 0, '2025-08-04 07:45:48', '2025-08-25 16:44:50'),
	(2, 'TRC-20', 'USDT', 'TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8Qq', 'Send only USDT (TRC-20) to this address. Minimum confirmation required: 1.', 0, '2025-08-25 16:44:50', '2025-08-26 21:05:20'),
	(3, 'TRC-20', 'USDT', 'TVg1LJq6zQ7zvC3k4RCYf8gEJ7VuvHg8Qhh', 'Send only USDT (TRC-20) to this address. Minimum confirmation required: 1.', 1, '2025-08-26 21:05:20', '2025-08-26 21:05:20');

-- Dumping structure for table sales_spy.plans
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `yearly_price` decimal(10,2) NOT NULL,
  `leads_per_month` int NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_popular` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `credits_per_month` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.plans: ~3 rows (approximately)
INSERT INTO `plans` (`id`, `plan_name`, `description`, `monthly_price`, `yearly_price`, `leads_per_month`, `features`, `is_active`, `is_popular`, `created_at`, `updated_at`, `credits_per_month`) VALUES
	(1, 'Basic', 'Perfect for small businesses just getting started with lead generation', 20.00, 192.00, 500, '["Basic filtering options","Email and phone support","Weekly database updates","500 leads per month"]', 1, 0, '2025-08-02 18:51:43', '2025-09-18 17:36:36', 1000),
	(2, 'Pro', 'Ideal for growing businesses with serious lead generation needs', 50.00, 480.00, 2000, '["2,000 leads per month","Advanced filtering options","Priority support","Daily database updates","CRM integration","Email sequence automation"]', 1, 1, '2025-08-02 18:51:43', '2025-09-18 17:36:14', 2000),
	(3, 'Enterprise', 'For large organizations with custom lead generation requirements', 150.00, 1440.00, 0, '["Unlimited leads","Custom filtering options","Dedicated account manager","Real-time database updates","Advanced API access","Custom integration development"]', 1, 0, '2025-08-02 18:51:43', '2025-09-18 17:36:14', 10000);

-- Dumping structure for table sales_spy.search_logs
CREATE TABLE IF NOT EXISTS `search_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `filters_used` text,
  `search_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `search_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.search_logs: ~0 rows (approximately)

-- Dumping structure for table sales_spy.security_logs
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_security_logs_event_type` (`event_type`),
  CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.security_logs: ~59 rows (approximately)
INSERT INTO `security_logs` (`id`, `user_id`, `event_type`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
	(41, 26, 'account_suspended', NULL, NULL, '{"reason":"hhh","duration":"1week"}', '2025-08-18 20:22:52'),
	(42, 26, 'account_unsuspended', NULL, NULL, '{"reason":""}', '2025-08-18 20:23:00'),
	(44, 26, 'account_suspended', NULL, NULL, '{"reason":"","duration":"1week"}', '2025-08-20 11:53:25'),
	(45, 26, 'account_unsuspended', NULL, NULL, '{"reason":"time passed"}', '2025-08-20 12:24:39'),
	(46, 26, 'account_suspended', NULL, NULL, '{"reason":"flagged","duration":"indefinite"}', '2025-08-20 12:27:41'),
	(47, 26, 'account_unsuspended', NULL, NULL, '{"reason":"h"}', '2025-08-20 12:52:22'),
	(48, 26, 'account_suspended', NULL, NULL, '{"reason":"flagged","duration":"indefinite"}', '2025-08-20 12:52:43'),
	(49, 26, 'account_unsuspended', NULL, NULL, '{"reason":"h"}', '2025-08-20 12:53:05'),
	(50, 26, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 20:47:04'),
	(52, 26, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"enterprise","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 20:47:28'),
	(54, 26, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"free","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 20:56:50'),
	(56, 26, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-20 21:04:29","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 21:04:29'),
	(57, 26, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:14:32","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 21:14:32'),
	(58, 26, 'subscription_cancelled_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 21:17:11'),
	(59, 26, 'subscription_created_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"3months","start_date":"2025-08-20 21:49:02","end_date":"2025-11-20 21:49:02","credits":2000,"notes":"nnnnnnnnnnnnnn"}', '2025-08-20 21:49:02'),
	(60, 26, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:50:02","user_name":"devfaruq","user_email":"timi@gmail.com"}', '2025-08-20 21:50:02'),
	(62, 26, 'subscription_created_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"1month","start_date":"2025-08-20 21:52:08","end_date":"2025-09-20 21:52:08","credits":2000,"notes":"n"}', '2025-08-20 21:52:08'),
	(63, 39, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:39:11","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:39:11'),
	(64, 39, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:39:26","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:39:26'),
	(65, 28, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:40:20","user_name":"faru","user_email":"emmanuelfaru002@gmail.com"}', '2025-08-24 10:40:20'),
	(67, 39, 'subscription_cancelled_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:45:31'),
	(68, 39, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:45:38","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:45:38'),
	(69, 39, 'subscription_cancelled_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:45:42'),
	(70, 39, 'subscription_created_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"free","duration":"1month","start_date":"2025-08-24 10:46:30","end_date":null,"credits":1000,"notes":"nnnnn"}', '2025-08-24 10:46:30'),
	(71, 39, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"free","end_date":null,"user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-24 10:59:34'),
	(72, 38, 'plan_changed_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-09-24 10:59:46","user_name":"faru","user_email":"emman@gmail.com"}', '2025-08-24 10:59:46'),
	(73, 39, 'account_suspended', NULL, NULL, '{"reason":"","duration":"1week"}', '2025-08-25 15:56:10'),
	(74, 39, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:56:21","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-25 15:56:21'),
	(75, 39, 'subscription_cancelled_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-25 15:57:23'),
	(76, 39, 'subscription_paused_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:57:29","user_name":"faru","user_email":"emma@gmail.com"}', '2025-08-25 15:57:29'),
	(77, 39, 'subscription_created_by_admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"enterprise","duration":"3months","start_date":"2025-08-25 15:57:55","end_date":"2025-11-25 15:57:55","credits":10000,"notes":""}', '2025-08-25 15:57:55'),
	(78, 39, 'account_unsuspended', NULL, NULL, '{"reason":""}', '2025-08-25 15:58:55'),
	(79, 27, 'account_suspended', NULL, NULL, '{"reason":"thattt","duration":"1week"}', '2025-09-08 19:25:50'),
	(80, 27, 'account_unsuspended', NULL, NULL, '{"reason":"that"}', '2025-09-08 19:26:21'),
	(81, 27, 'account_suspended', NULL, NULL, '{"reason":"taht","duration":"1week"}', '2025-09-08 19:28:46'),
	(82, 27, 'account_unsuspended', NULL, NULL, '{"reason":"that"}', '2025-09-08 19:28:57'),
	(83, 27, 'account_suspended', NULL, NULL, '{"reason":"that","duration":"1week"}', '2025-09-08 19:34:13'),
	(84, 39, 'subscription_cancelled_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', '2025-09-10 05:48:44'),
	(85, 39, 'plan_changed_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"plan_changed","old_plan":"N\\/A","new_plan":"pro","end_date":"2025-10-10 06:00:11","user_name":"faru","user_email":"emma@gmail.com"}', '2025-09-10 06:00:11'),
	(87, 27, 'account_unsuspended', NULL, NULL, '{"reason":"that"}', '2025-09-10 18:01:58'),
	(88, 27, 'account_suspended', NULL, NULL, '{"reason":"suspicious activity","duration":"1week"}', '2025-09-10 18:05:11'),
	(89, 27, 'account_unsuspended', NULL, NULL, '{"reason":"timley"}', '2025-09-10 18:07:15'),
	(90, 27, 'account_unsuspended', NULL, NULL, '{"reason":"timley"}', '2025-09-10 18:25:47'),
	(91, 27, 'account_suspended', NULL, NULL, '{"reason":"suspicious activity","duration":"1week"}', '2025-09-10 18:26:46'),
	(92, 27, 'account_unsuspended', NULL, NULL, '{"reason":"timley"}', '2025-09-10 18:31:13'),
	(93, 27, 'account_suspended', NULL, NULL, '{"reason":"suspicious activity","duration":"1week"}', '2025-09-10 18:39:23'),
	(94, 27, 'account_unsuspended', NULL, NULL, '{"reason":"success"}', '2025-09-10 18:43:32'),
	(95, 27, 'account_suspended', NULL, NULL, '{"reason":"suspend","duration":"1week"}', '2025-09-10 18:43:56'),
	(96, 27, 'account_unsuspended', NULL, NULL, '{"reason":"success"}', '2025-09-10 18:45:05'),
	(97, 27, 'account_suspended', NULL, NULL, '{"reason":"suspend","duration":"1week"}', '2025-09-10 19:00:15'),
	(98, 27, 'account_unsuspended', NULL, NULL, '{"reason":"hhh"}', '2025-09-10 19:00:55'),
	(99, 27, 'account_suspended', NULL, NULL, '{"reason":"yyyyyyyyyy","duration":"1week"}', '2025-09-10 19:01:25'),
	(100, 27, 'subscription_paused_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-10-10 19:57:12","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', '2025-09-10 19:57:12'),
	(101, 27, 'subscription_created_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":27,"user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","plan":"Basic","duration":"1month","start_date":"2025-09-10 20:02:24","end_date":"2025-10-10 20:02:24","credits":1000,"notes":"review"}', '2025-09-10 20:02:24'),
	(102, 40, 'account_suspended', NULL, NULL, '{"reason":"hh","duration":"1week"}', '2025-09-15 15:53:42'),
	(103, 40, 'account_unsuspended', NULL, NULL, '{"reason":""}', '2025-09-15 15:54:34'),
	(104, 27, 'account_unsuspended', NULL, NULL, '{"reason":"h"}', '2025-09-15 15:55:00'),
	(105, 38, 'subscription_paused_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"kkk","duration":"1month","pause_end":"2025-10-16 23:53:32","user_name":"faru","user_email":"emman@gmail.com"}', '2025-09-16 23:53:32'),
	(106, 40, 'subscription_paused_by_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"flaged","duration":"1month","pause_end":"2025-10-17 19:24:20","user_name":"ada","user_email":"ada@gmail.com"}', '2025-09-17 19:24:20');

-- Dumping structure for table sales_spy.shopify_intelligence
CREATE TABLE IF NOT EXISTS `shopify_intelligence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_domain` varchar(255) DEFAULT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `product_title` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `tags` text,
  `scraped_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_scraped_at` (`scraped_at`),
  KEY `idx_store_domain` (`store_domain`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.shopify_intelligence: ~60 rows (approximately)
INSERT INTO `shopify_intelligence` (`id`, `store_domain`, `store_name`, `product_title`, `price`, `availability`, `product_type`, `vendor`, `tags`, `scraped_at`, `category`) VALUES
	(1, 'peakdesign.com', 'Peak Design', 'Everyday Messenger V1 | 15L | Tan', 100.00, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(2, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Sage | Nylon', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(3, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Charcoal', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(4, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Tan | Clarino', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(5, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(6, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Sage | Nylon', 49.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(7, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Charcoal | Nylon', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(8, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Tan | Clarino', 49.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(9, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and  Pro | Everyday Case Loop | Tan | Clarino', 59.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(10, 'peakdesign.com', 'Peak Design', 'Gnar Case | Pixel 10 Pro XL | Black', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(11, 'peakdesign.com', 'Peak Design', 'GNAR Case for Pixel 10', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(12, 'peakdesign.com', 'Peak Design', 'Gnar Case | Pixel 10 Standard and Pro | Black', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(13, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case Loop | Tan | Clarino', 59.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(14, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 30L | Kelp', 299.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(15, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Eclipse', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(16, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Ocean', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(17, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 30L | Ocean', 299.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(18, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Kelp', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(19, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Eclipse', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(20, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Ocean', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(21, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Eclipse', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(22, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Kelp', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(23, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 10L | Ocean', 169.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(24, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 10L | Eclipse', 169.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(25, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Ocean', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(26, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Kelp', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(27, 'peakdesign.com', 'Peak Design', 'Everyday Backpack Zip | 20L | Kelp', 229.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(28, 'peakdesign.com', 'Peak Design', 'Everyday Backpack Zip | 20L | Eclipse', 229.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(29, 'peakdesign.com', 'Peak Design', 'Everyday Totepack | 20L | Ocean', 189.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(30, 'peakdesign.com', 'Peak Design', 'Everyday Totepack | 20L | Eclipse', 189.95, 'in_stock', '', '', '', '2025-09-05 13:53:24', 'tech'),
	(31, 'peakdesign.com', 'Peak Design', 'Everyday Messenger V1 | 15L | Tan', 100.00, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(32, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Sage | Nylon', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(33, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Charcoal', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(34, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and Pro | Everyday Case | Tan | Clarino', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(35, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(36, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Sage | Nylon', 49.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(37, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Charcoal | Nylon', 49.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(38, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case | Tan | Clarino', 49.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(39, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Standard and  Pro | Everyday Case Loop | Tan | Clarino', 59.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(40, 'peakdesign.com', 'Peak Design', 'Gnar Case | Pixel 10 Pro XL | Black', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(41, 'peakdesign.com', 'Peak Design', 'GNAR Case for Pixel 10', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(42, 'peakdesign.com', 'Peak Design', 'Gnar Case | Pixel 10 Standard and Pro | Black', 59.95, 'out_of_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(43, 'peakdesign.com', 'Peak Design', 'Everyday Case for Pixel 10 Pro XL | Everyday Case Loop | Tan | Clarino', 59.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(44, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 30L | Kelp', 299.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(45, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Eclipse', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(46, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Ocean', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(47, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 30L | Ocean', 299.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(48, 'peakdesign.com', 'Peak Design', 'Everyday Backpack | 20L | Kelp', 279.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(49, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Eclipse', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(50, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Ocean', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(51, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Eclipse', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(52, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 3L | Kelp', 99.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(53, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 10L | Ocean', 169.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(54, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 10L | Eclipse', 169.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(55, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Ocean', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(56, 'peakdesign.com', 'Peak Design', 'Everyday Sling | 6L | Kelp', 129.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(57, 'peakdesign.com', 'Peak Design', 'Everyday Backpack Zip | 20L | Kelp', 229.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(58, 'peakdesign.com', 'Peak Design', 'Everyday Backpack Zip | 20L | Eclipse', 229.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(59, 'peakdesign.com', 'Peak Design', 'Everyday Totepack | 20L | Ocean', 189.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech'),
	(60, 'peakdesign.com', 'Peak Design', 'Everyday Totepack | 20L | Eclipse', 189.95, 'in_stock', '', '', '', '2025-09-05 13:53:43', 'tech');

-- Dumping structure for table sales_spy.stores
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `language` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `product_count` int DEFAULT NULL,
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
  `facebook_ads_count` int DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.stores: ~3 rows (approximately)
INSERT INTO `stores` (`id`, `domain`, `title`, `description`, `language`, `country`, `currency`, `product_count`, `avg_price`, `categories`, `tech_stack`, `shipping_destinations`, `payment_methods`, `contact_email`, `contact_phone`, `whatsapp_number`, `facebook_url`, `instagram_url`, `twitter_url`, `youtube_url`, `tiktok_url`, `pinterest_url`, `facebook_ads_count`, `date_added`) VALUES
	(7, 'demo-store1.com', 'Demo Store 1', 'This is a mock store for testing purposes.', 'en', 'Nigeria', 'NGN', 150, 2500.50, 'Fashion, Shoes, Accessories', 'Shopify, Paystack', 'Nigeria, Ghana', 'Credit Card, Bank Transfer, Paystack', 'demo1@example.com', '+2348012345678', '+2348098765432', 'https://facebook.com/demo_store1', 'https://instagram.com/demo_store1', 'https://twitter.com/demo_store1', 'https://youtube.com/@demo_store1', 'https://tiktok.com/@demo_store1', 'https://pinterest.com/demo_store1', 12, '2025-09-05 15:25:20'),
	(8, 'demo-store2.com', 'Demo Store 2', 'Another mock store with sample data.', 'en', 'United States', 'USD', 300, 45.75, 'Electronics, Gadgets', 'WooCommerce, Stripe', 'USA, Canada, UK', 'PayPal, Stripe, Credit Card', 'demo2@example.com', '+12025550123', '+12025559876', 'https://facebook.com/demo_store2', 'https://instagram.com/demo_store2', 'https://twitter.com/demo_store2', 'https://youtube.com/@demo_store2', 'https://tiktok.com/@demo_store2', 'https://pinterest.com/demo_store2', 25, '2025-09-05 15:25:20'),
	(9, 'demo-store3.com', 'Demo Store 3', 'Third test store inserted as mock data.', 'fr', 'France', 'EUR', 500, 75.20, 'Home, Kitchen, Furniture', 'Magento, PayPal', 'France, Belgium, Switzerland', 'PayPal, Credit Card', 'demo3@example.com', '+33123456789', '+33765432109', 'https://facebook.com/demo_store3', 'https://instagram.com/demo_store3', 'https://twitter.com/demo_store3', 'https://youtube.com/@demo_store3', 'https://tiktok.com/@demo_store3', 'https://pinterest.com/demo_store3', 40, '2025-09-05 15:25:20');

-- Dumping structure for table sales_spy.subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plan_name` enum('free','basic','pro','enterprise') DEFAULT NULL,
  `credits_remaining` int NOT NULL DEFAULT '1250',
  `credits_total` int NOT NULL DEFAULT '2000',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `leads_balance` int DEFAULT '1000',
  `status` enum('active','expired','cancelled','paused') DEFAULT 'active',
  `pause_end_date` datetime DEFAULT NULL,
  `pause_reason` text,
  `last_modified_by` int DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `last_modified_by` (`last_modified_by`),
  KEY `idx_subscriptions_status` (`status`),
  KEY `idx_subscriptions_plan_name` (`plan_name`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`last_modified_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.subscriptions: ~15 rows (approximately)
INSERT INTO `subscriptions` (`id`, `user_id`, `plan_name`, `credits_remaining`, `credits_total`, `start_date`, `end_date`, `is_active`, `leads_balance`, `status`, `pause_end_date`, `pause_reason`, `last_modified_by`, `updated_at`) VALUES
	(17, 26, 'pro', 2000, 2000, '2025-08-20 20:52:08', '2025-09-20 20:52:08', 1, 1000, 'active', NULL, NULL, NULL, '2025-08-20 21:52:08'),
	(19, 28, 'pro', 2000, 2000, '2025-08-24 10:36:47', '2025-09-24 09:40:20', 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:40:20'),
	(20, 29, 'free', 1000, 1000, '2025-08-24 10:37:02', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:02'),
	(21, 30, 'free', 1000, 1000, '2025-08-24 10:37:15', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:15'),
	(22, 31, 'free', 1000, 1000, '2025-08-24 10:37:24', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:24'),
	(23, 32, 'free', 1000, 1000, '2025-08-24 10:37:34', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:34'),
	(24, 33, 'free', 1000, 1000, '2025-08-24 10:37:43', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:43'),
	(25, 34, 'free', 1000, 1000, '2025-08-24 10:37:53', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:37:53'),
	(26, 35, 'free', 1000, 1000, '2025-08-24 10:38:03', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:38:03'),
	(27, 36, 'free', 1000, 1000, '2025-08-24 10:38:15', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:38:15'),
	(28, 37, 'free', 1000, 1000, '2025-08-24 10:38:26', NULL, 1, 1000, 'active', NULL, NULL, NULL, '2025-08-24 10:38:26'),
	(29, 38, 'pro', 2000, 2000, '2025-08-24 10:38:39', '2025-09-24 09:59:46', 1, 1000, 'paused', NULL, NULL, NULL, '2025-09-16 23:53:32'),
	(32, 39, 'pro', 2000, 2000, '2025-08-25 14:57:55', '2025-10-10 05:00:11', 0, 1000, 'cancelled', NULL, NULL, NULL, '2025-09-10 06:00:11'),
	(41, 40, 'pro', 2000, 2000, '2025-09-18 20:24:38', '2026-09-18 19:24:38', 1, 1000, 'active', NULL, NULL, NULL, '2025-09-18 20:24:38'),
	(46, 27, 'pro', 2000, 2000, '2025-09-18 20:59:25', '2026-09-18 19:59:25', 1, 1000, 'active', NULL, NULL, NULL, '2025-09-18 20:59:25');

-- Dumping structure for table sales_spy.subscription_history
CREATE TABLE IF NOT EXISTS `subscription_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `subscription_id` int DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `old_plan` varchar(50) DEFAULT NULL,
  `new_plan` varchar(50) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `details` text,
  `admin_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `admin_id` (`admin_id`),
  KEY `event_type` (`event_type`),
  CONSTRAINT `subscription_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_history_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscription_history_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.subscription_history: ~31 rows (approximately)
INSERT INTO `subscription_history` (`id`, `user_id`, `subscription_id`, `event_type`, `old_plan`, `new_plan`, `old_status`, `new_status`, `details`, `admin_id`, `amount`, `created_at`) VALUES
	(1, 26, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:14:32","user_name":"devfaruq","user_email":"timi@gmail.com"}', 1, NULL, '2025-08-20 21:14:32'),
	(2, 26, NULL, 'cancelled', NULL, NULL, NULL, NULL, '{"admin_id":"1","admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"devfaruq","user_email":"timi@gmail.com"}', 1, NULL, '2025-08-20 21:17:11'),
	(3, 26, NULL, 'created', NULL, NULL, NULL, NULL, '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"3months","start_date":"2025-08-20 21:49:02","end_date":"2025-11-20 21:49:02","credits":2000,"notes":"nnnnnnnnnnnnnn"}', 1, NULL, '2025-08-20 21:49:02'),
	(4, 26, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":"1","admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-20 21:50:02","user_name":"devfaruq","user_email":"timi@gmail.com"}', 1, NULL, '2025-08-20 21:50:02'),
	(6, 26, NULL, 'created', NULL, NULL, NULL, NULL, '{"admin_id":"1","admin_name":"de faruq","action":"subscription_created","user_id":"26","user_name":"devfaruq","user_email":"timi@gmail.com","plan":"pro","duration":"1month","start_date":"2025-08-20 21:52:08","end_date":"2025-09-20 21:52:08","credits":2000,"notes":"n"}', 1, NULL, '2025-08-20 21:52:08'),
	(7, 39, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:39:11","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-24 10:39:11'),
	(8, 39, NULL, 'cancelled', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-24 10:45:31'),
	(9, 39, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-24 10:45:38","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-24 10:45:38'),
	(10, 39, NULL, 'cancelled', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-24 10:45:42'),
	(11, 39, NULL, 'created', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"free","duration":"1month","start_date":"2025-08-24 10:46:30","end_date":null,"credits":1000,"notes":"nnnnn"}', 1, NULL, '2025-08-24 10:46:30'),
	(12, 39, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:56:21","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-25 15:56:21'),
	(13, 39, NULL, 'cancelled', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-25 15:57:23'),
	(14, 39, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-09-25 15:57:29","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-08-25 15:57:29'),
	(15, 39, NULL, 'created', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":39,"user_name":"faru","user_email":"emma@gmail.com","plan":"enterprise","duration":"3months","start_date":"2025-08-25 15:57:55","end_date":"2025-11-25 15:57:55","credits":10000,"notes":""}', 1, NULL, '2025-08-25 15:57:55'),
	(16, 39, NULL, 'cancelled', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_cancelled","reason":"","user_name":"faru","user_email":"emma@gmail.com"}', 1, NULL, '2025-09-10 05:48:44'),
	(18, 27, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"","duration":"1month","pause_end":"2025-10-10 19:57:12","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, NULL, '2025-09-10 19:57:12'),
	(19, 27, NULL, 'created', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_created","user_id":27,"user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","plan":"Basic","duration":"1month","start_date":"2025-09-10 20:02:24","end_date":"2025-10-10 20:02:24","credits":1000,"notes":"review"}', 1, NULL, '2025-09-10 20:02:24'),
	(20, 38, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"kkk","duration":"1month","pause_end":"2025-10-16 23:53:32","user_name":"faru","user_email":"emman@gmail.com"}', 1, NULL, '2025-09-16 23:53:32'),
	(21, 40, NULL, 'paused', NULL, NULL, NULL, NULL, '{"admin_id":1,"admin_name":"de faruq","action":"subscription_paused","reason":"flaged","duration":"1month","pause_end":"2025-10-17 19:24:20","user_name":"ada","user_email":"ada@gmail.com"}', 1, NULL, '2025-09-17 19:24:20'),
	(22, 40, NULL, 'deleted_for_upgrade', 'free', NULL, 'paused', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":20,"old_subscription_id":37,"old_plan_name":"free","old_credits_remaining":100,"old_start_date":"2025-09-10 07:15:58","old_end_date":null,"admin_name":"de faruq","user_name":"ada","user_email":"ada@gmail.com"}', 1, 480.00, '2025-09-18 20:24:38'),
	(23, 40, 41, 'created', NULL, 'Pro', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":20,"plan_name":"Pro","credits_total":2000,"credits_remaining":2000,"start_date":"2025-09-18 20:24:38","end_date":"2026-09-18 20:24:38","duration":"12_months","amount_paid":"480.00","admin_name":"de faruq","user_name":"ada","user_email":"ada@gmail.com","had_existing_subscription":true}', 1, 480.00, '2025-09-18 20:24:38'),
	(24, 27, NULL, 'deleted_for_upgrade', 'free', NULL, 'paused', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":22,"old_subscription_id":18,"old_plan_name":"free","old_credits_remaining":1000,"old_start_date":"2025-08-24 11:36:30","old_end_date":null,"admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, 480.00, '2025-09-18 20:33:33'),
	(25, 27, NULL, 'created', NULL, 'Pro', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":22,"plan_name":"Pro","credits_total":2000,"credits_remaining":2000,"start_date":"2025-09-18 20:33:33","end_date":"2026-09-18 20:33:33","duration":"12_months","amount_paid":"480.00","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","had_existing_subscription":true}', 1, 480.00, '2025-09-18 20:33:33'),
	(26, 27, NULL, 'deleted_for_upgrade', 'pro', NULL, 'active', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":24,"old_subscription_id":42,"old_plan_name":"pro","old_credits_remaining":2000,"old_start_date":"2025-09-18 21:33:33","old_end_date":"2026-09-18 20:33:33","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, 480.00, '2025-09-18 20:41:55'),
	(27, 27, NULL, 'created', NULL, 'Pro', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":24,"plan_name":"Pro","credits_total":2000,"credits_remaining":2000,"start_date":"2025-09-18 20:41:55","end_date":"2026-09-18 20:41:55","duration":"12_months","amount_paid":"480.00","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","had_existing_subscription":true}', 1, 480.00, '2025-09-18 20:41:55'),
	(28, 27, NULL, 'deleted_for_upgrade', 'pro', NULL, 'active', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":25,"old_subscription_id":43,"old_plan_name":"pro","old_credits_remaining":2000,"old_start_date":"2025-09-18 21:41:55","old_end_date":"2026-09-18 20:41:55","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, 20.00, '2025-09-18 20:46:22'),
	(29, 27, NULL, 'created', NULL, 'Basic', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":25,"plan_name":"Basic","credits_total":1000,"credits_remaining":1000,"start_date":"2025-09-18 20:46:22","end_date":"2025-10-18 20:46:22","duration":"1_month","amount_paid":"20.00","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","had_existing_subscription":true}', 1, 20.00, '2025-09-18 20:46:22'),
	(30, 27, NULL, 'deleted_for_upgrade', 'basic', NULL, 'active', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":27,"old_subscription_id":44,"old_plan_name":"basic","old_credits_remaining":1000,"old_start_date":"2025-09-18 21:46:22","old_end_date":"2025-10-18 20:46:22","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, 50.00, '2025-09-18 20:57:49'),
	(31, 27, NULL, 'created', NULL, 'Pro', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":27,"plan_name":"Pro","credits_total":2000,"credits_remaining":2000,"start_date":"2025-09-18 20:57:49","end_date":"2025-10-18 20:57:49","duration":"1_month","amount_paid":"50.00","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","had_existing_subscription":true}', 1, 50.00, '2025-09-18 20:57:49'),
	(32, 27, NULL, 'deleted_for_upgrade', 'pro', NULL, 'active', NULL, '{"reason":"Deleted due to new payment approval","transaction_id":28,"old_subscription_id":45,"old_plan_name":"pro","old_credits_remaining":2000,"old_start_date":"2025-09-18 21:57:49","old_end_date":"2025-10-18 20:57:49","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com"}', 1, 480.00, '2025-09-18 20:59:25'),
	(33, 27, 46, 'created', NULL, 'Pro', NULL, 'active', '{"reason":"Created due to payment approval","transaction_id":28,"plan_name":"Pro","credits_total":2000,"credits_remaining":2000,"start_date":"2025-09-18 20:59:25","end_date":"2026-09-18 20:59:25","duration":"12_months","amount_paid":"480.00","admin_name":"de faruq","user_name":"faru","user_email":"emmanuelfaruq002@gmail.com","had_existing_subscription":true}', 1, 480.00, '2025-09-18 20:59:25');

-- Dumping structure for view sales_spy.subscription_summary
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `subscription_summary` (
	`user_id` INT NOT NULL,
	`full_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`user_created` TIMESTAMP NOT NULL,
	`plan_name` ENUM('free','basic','pro','enterprise') NULL COLLATE 'utf8mb4_0900_ai_ci',
	`status` ENUM('active','expired','cancelled','paused') NULL COLLATE 'utf8mb4_0900_ai_ci',
	`start_date` TIMESTAMP NULL,
	`end_date` TIMESTAMP NULL,
	`credits_remaining` INT NULL,
	`credits_total` INT NULL,
	`monthly_revenue` INT NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for table sales_spy.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `txid` varchar(100) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `order_id` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.transactions: ~15 rows (approximately)
INSERT INTO `transactions` (`id`, `user_id`, `txid`, `payment_type`, `amount`, `status`, `created_at`, `screenshot_path`, `order_id`) VALUES
	(14, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e12', 'crypto', 20.00, 'success', '2025-09-06 10:59:59', 'uploads/payment_screenshots/ss_68bc142fe24b3_anniversary.jpg', '#ORD-2025-001'),
	(15, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e34', 'crypto', 50.00, 'failed', '2025-09-07 17:38:48', 'uploads/payment_screenshots/ss_68bdc32883082_dev.jpg', '#ORD-2025-002'),
	(16, 25, 'f4184fc596403b9d638783cf57adfe4c75c605f6356fbc91338530e9831e9e31', 'crypto', 50.00, 'success', '2025-09-07 17:43:18', 'uploads/payment_screenshots/ss_68bdc4367caca_logo.jpg', '#ORD-2025-003'),
	(17, 40, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e31', 'crypto', 20.00, 'success', '2025-09-16 23:14:26', 'uploads/payment_screenshots/ss_68c9ef5232ec1_Screenshot 2024-12-03 232756.png', '#ORD-2025-004'),
	(18, 40, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e33', 'crypto', 50.00, 'failed', '2025-09-17 00:00:40', 'uploads/payment_screenshots/ss_68c9fa28aa5c7_Screenshot 2024-12-03 232756.png', '#ORD-2025-005'),
	(19, 40, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e35', 'crypto', 50.00, 'success', '2025-09-17 19:26:48', 'uploads/payment_screenshots/ss_68cb0b78ce6d1_Screenshot 2024-12-03 232756.png', '#ORD-2025-006'),
	(20, 40, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e55', 'crypto', 480.00, 'success', '2025-09-18 17:22:53', 'uploads/payment_screenshots/ss_68cc3fedbd64e_Screenshot 2024-12-21 113654.png', '#ORD-2025-007'),
	(21, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e54', 'crypto', 480.00, 'failed', '2025-09-18 20:27:27', 'uploads/payment_screenshots/ss_68cc6b2fdc946_Screenshot 2024-12-03 232756.png', '#ORD-2025-008'),
	(22, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e59', 'crypto', 480.00, 'success', '2025-09-18 20:33:22', 'uploads/payment_screenshots/ss_68cc6c92dc036_Screenshot 2024-12-03 232756.png', '#ORD-2025-009'),
	(23, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e58', 'crypto', 480.00, 'failed', '2025-09-18 20:38:20', 'uploads/payment_screenshots/ss_68cc6dbcad5d7_Screenshot 2024-12-03 232756.png', '#ORD-2025-010'),
	(24, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f63569bc91338530e9831e9e92', 'crypto', 480.00, 'success', '2025-09-18 20:41:47', 'uploads/payment_screenshots/ss_68cc6e8b43962_Screenshot 2024-12-21 113654.png', '#ORD-2025-011'),
	(25, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f64569bc91338530e9831e9e58', 'crypto', 20.00, 'success', '2025-09-18 20:46:16', 'uploads/payment_screenshots/ss_68cc6f9843079_Screenshot 2024-12-03 232756.png', '#ORD-2025-012'),
	(26, 27, 'f4184fc596403b9d638783cf57adfe4c75c605e63569bc91338530e9831e9e33', 'crypto', 50.00, 'failed', '2025-09-18 20:55:12', 'uploads/payment_screenshots/ss_68cc71b0a4991_Screenshot 2024-12-21 115918.png', '#ORD-2025-013'),
	(27, 27, 'f4184fc596403b9d638783cf67adfe4c75c605f63569bc91338530e9831e9e55', 'crypto', 50.00, 'success', '2025-09-18 20:57:39', 'uploads/payment_screenshots/ss_68cc72439e81d_Screenshot 2024-12-21 115918.png', '#ORD-2025-014'),
	(28, 27, 'f4184fc596403b9d638783cf57adfe4c75c605f73569bc91338530e9831e9e58', 'crypto', 480.00, 'success', '2025-09-18 20:59:14', 'uploads/payment_screenshots/ss_68cc72a2b7a89_Screenshot 2024-12-21 113654.png', '#ORD-2025-015');

-- Dumping structure for table sales_spy.txid_requests
CREATE TABLE IF NOT EXISTS `txid_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `txid` varchar(255) NOT NULL,
  `plan_id` int DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.txid_requests: ~0 rows (approximately)

-- Dumping structure for table sales_spy.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','enterprise') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `failed_attempts` int DEFAULT '0',
  `last_failed_attempt` datetime DEFAULT NULL,
  `credits` int NOT NULL DEFAULT '1250',
  `profile_picture` varchar(255) DEFAULT NULL,
  `twofa_secret` varchar(255) DEFAULT NULL,
  `twofa_enabled` tinyint(1) DEFAULT '0',
  `twofa_backup_codes` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_disabled` tinyint(1) DEFAULT '0',
  `account_status` enum('active','locked','disabled','deleted') NOT NULL DEFAULT 'active',
  `unlock_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_account_status` (`account_status`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.users: ~15 rows (approximately)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `created_at`, `reset_token`, `reset_token_expiry`, `failed_attempts`, `last_failed_attempt`, `credits`, `profile_picture`, `twofa_secret`, `twofa_enabled`, `twofa_backup_codes`, `ip_address`, `city`, `is_disabled`, `account_status`, `unlock_time`) VALUES
	(26, 'devfaruq', 'timi@gmail.com', '09027985407', '$2y$10$f9ed9bKl6u7gnfGwj5AYmeAA85m4LS1WOGcKLDuG0YCa3jjSZ1Pci', 'user', '2025-08-17 21:01:10', NULL, NULL, 0, NULL, 1250, 'uploads/profile_pictures/profile_26_1755464558.jpg', NULL, 0, NULL, '127.0.0.1', NULL, 0, 'active', NULL),
	(27, 'faru', 'emmanuelfaruq002@gmail.com', '08116533387', '$2y$10$wBJt3dEmU7f.3oqQDcSc5uNJE3e/gNFRNE0wgDbOVsicpguuT.tp.', 'user', '2025-08-24 10:36:30', NULL, NULL, 0, NULL, 2000, 'uploads/profile_pictures/profile_27_1758228152.jpg', NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(28, 'faru', 'emmanuelfaru002@gmail.com', '08116533386', '$2y$10$oARJjIlir6Gi/ZBco1vB8ONpHgt6qDmAxZqFP19kIoFj.vcuB1//S', 'user', '2025-08-24 10:36:47', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(29, 'faru', 'emmanuelfar002@gmail.com', '08116533389', '$2y$10$9YrTnxsTC.na0EW0Cte2Qu0izT3rT2MCfIUgKKF2.DM6tntV6H7ii', 'user', '2025-08-24 10:37:02', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(30, 'faru', 'emmanuelfar02@gmail.com', '08116533310', '$2y$10$PME2OYBktW33hh1W0WnRkeXfTcYUrC0C5DK0JDCBXJlbIw3FDvsh.', 'user', '2025-08-24 10:37:15', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(31, 'faru', 'emmanuelfar2@gmail.com', '08116533311', '$2y$10$LY5i22CF5uJ/378oLrLABOQgUncuvm5TA5jFgc5qtmhhyE1EV1bFG', 'user', '2025-08-24 10:37:24', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(32, 'faru', 'emmanuelfar@gmail.com', '08116533312', '$2y$10$LQuqE0YDwXFnMZdPN5BKwe.0ROqfCptl0Z.BPdVv.K4lLIZUWZ7TO', 'user', '2025-08-24 10:37:34', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(33, 'faru', 'emmanuelfa@gmail.com', '08116533313', '$2y$10$oTaYg5Hga5HKkoKYUpnVGOptcdQKCP7P69QYyUJPEGq2mdf/uEbQq', 'user', '2025-08-24 10:37:43', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(34, 'faru', 'emmanuelf@gmail.com', '08116533314', '$2y$10$reK9lqZ2oTBRp0t6PL9wVuIFTjVw1a.3l7gzuGCZra2MAIxSL9V22', 'user', '2025-08-24 10:37:53', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(35, 'faru', 'emmanuel@gmail.com', '08116533315', '$2y$10$TQfZGm8RWWE0zDIu9oWKQOE5LkwZrQxlwLG.4N/3fdqiuT7BNRb.m', 'user', '2025-08-24 10:38:03', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(36, 'faru', 'emmanue@gmail.com', '08116533316', '$2y$10$xedTceTXg2dONnURvAyyXunFZFw.F/wziByeT/MPlvpdv9f.uokmq', 'user', '2025-08-24 10:38:15', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(37, 'faru', 'emmanu@gmail.com', '08116533317', '$2y$10$Oi01PN1gM2/DmXO78ofgD.d7nEKNyjph/wHhJRTkTH7/gmcmJzRMS', 'user', '2025-08-24 10:38:26', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 0, 'active', NULL),
	(38, 'faru', 'emman@gmail.com', '08116533318', '$2y$10$.d/qyGNEiYnJQzaemcwylehCmkVX4bAqsqETQ.6uTQpYnTzVULjGy', 'user', '2025-08-24 10:38:39', NULL, NULL, 0, NULL, 1250, NULL, NULL, 0, NULL, '::1', NULL, 1, 'deleted', NULL),
	(39, 'faru', 'emma@gmail.com', '08116533319', '$2y$10$zKbY2wapS6DqyY/lVKP6Qe7MqnW0v5TDgLYRALH/6J0gya5flluAi', 'user', '2025-08-24 10:38:52', NULL, NULL, 0, NULL, 1250, 'uploads/profile_pictures/profile_39_1756032497.jpg', NULL, 0, NULL, '::1', NULL, 1, 'active', NULL),
	(40, 'ada', 'ada@gmail.com', '08111111223', '$2y$10$xHYCynuw0/bcF3PvW1PKs.7KjgXHW5SMW3GxJNxhneGZOmaipOoF6', 'user', '2025-09-10 06:15:58', NULL, NULL, 0, NULL, 2000, 'uploads/profile_pictures/profile_40_1757485214.jpg', NULL, 0, NULL, '127.0.0.1', NULL, 0, 'active', NULL);

-- Dumping structure for table sales_spy.user_2fa
CREATE TABLE IF NOT EXISTS `user_2fa` (
  `user_id` int NOT NULL,
  `secret` varchar(32) DEFAULT NULL,
  `backup_codes` text,
  `method` enum('none','app','sms','email') DEFAULT 'none',
  `phone` varchar(20) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_2fa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.user_2fa: ~0 rows (approximately)

-- Dumping structure for table sales_spy.user_sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_agent` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.user_sessions: ~3 rows (approximately)
INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `user_agent`, `ip_address`, `last_active`, `created_at`, `city`, `country`) VALUES
	(35, 26, '5fis7bo25ndcdpgb7obpqb97uu', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '127.0.0.1', '2025-08-17 22:02:38', '2025-08-17 22:01:32', 'Unknown', 'Unknown'),
	(37, 39, '5trndreicbp69ofjhiv69vd4tj', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '::1', '2025-08-25 17:08:35', '2025-08-24 11:48:03', 'Unknown', 'Unknown'),
	(48, 27, '3o4h0k1g5epc74ofkg4vnv0l9b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '127.0.0.1', '2025-09-18 21:58:01', '2025-09-18 21:26:35', 'Unknown', 'Unknown');

-- Dumping structure for table sales_spy.user_stats
CREATE TABLE IF NOT EXISTS `user_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `leads_count` int DEFAULT '0',
  `campaigns_count` int DEFAULT '0',
  `credits_used` int DEFAULT '0',
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.user_stats: ~0 rows (approximately)

-- Dumping structure for table sales_spy.user_tokens
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table sales_spy.user_tokens: ~0 rows (approximately)

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `subscription_summary`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `subscription_summary` AS select `u`.`id` AS `user_id`,`u`.`full_name` AS `full_name`,`u`.`email` AS `email`,`u`.`created_at` AS `user_created`,`s`.`plan_name` AS `plan_name`,`s`.`status` AS `status`,`s`.`start_date` AS `start_date`,`s`.`end_date` AS `end_date`,`s`.`credits_remaining` AS `credits_remaining`,`s`.`credits_total` AS `credits_total`,(case when ((`s`.`status` = 'active') and (`s`.`plan_name` = 'pro')) then 50 when ((`s`.`status` = 'active') and (`s`.`plan_name` = 'enterprise')) then 150 when ((`s`.`status` = 'active') and (`s`.`plan_name` = 'basic')) then 20 else 0 end) AS `monthly_revenue` from (`users` `u` left join `subscriptions` `s` on((`u`.`id` = `s`.`user_id`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
