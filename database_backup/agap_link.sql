-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 06:23 PM
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
-- Database: `agap_link`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `last_login`) VALUES
(1, 'System Admin', 'admin@agap-link.com', '$2y$10$by9oEeew9Hufd4OF2IkRlOPU9OUTLlwxQqiNVOFKNQdnTV97W3xSq', '2026-06-22 00:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `agency_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`agency_id`, `name`, `contact_number`, `type`, `category_id`, `created_at`) VALUES
(19, 'Bacolod City Engineering Office', '034-432-0835', 'Government', 1, '2026-02-20 03:11:15'),
(20, 'Metro Bacolod Chamber of Commerce (Roads)', '034-432-3516', 'Government', 1, '2026-02-20 03:11:15'),
(21, 'Bacolod City Environment and Natural Resources Office (CENRO)', '034-432-0835', 'Government', 2, '2026-02-20 03:11:15'),
(22, 'Bacolod City Solid Waste Management Office', '034-432-0835', 'Government', 2, '2026-02-20 03:11:15'),
(23, 'Bacolod City Water District (BACIWA)', '034-432-4148', 'Government', 3, '2026-02-20 03:11:15'),
(24, 'Bacolod City Drainage & Flood Control', '034-432-0835', 'Government', 3, '2026-02-20 03:11:15'),
(25, 'Bacolod City Police Office (BCPO)', '034-432-0588', 'Government', 4, '2026-02-20 03:11:15'),
(26, 'Bureau of Fire Protection - Bacolod', '034-432-1165', 'Government', 4, '2026-02-20 03:11:15'),
(27, 'Bacolod City CENRO (Environment)', '034-432-0835', 'Government', 5, '2026-02-20 03:11:15'),
(28, 'Department of Environment and Natural Resources (DENR) Region VI', '033-337-9070', 'Government', 5, '2026-02-20 03:11:15'),
(29, 'Negros Power (formerly MORE Power Iloilo / NEC)', '034-432-3516', 'Government', 6, '2026-02-20 03:11:15'),
(30, 'PECO (Private)', '034-432-1093', 'Private', 6, '2026-02-20 03:11:15'),
(31, 'Land Transportation Office (LTO) - Bacolod', '034-433-1020', 'Government', 7, '2026-02-20 03:11:15'),
(32, 'Bacolod Traffic Authority Office (BTAO)', '034-432-0835', 'Government', 7, '2026-02-20 03:11:15'),
(33, 'Bacolod City Health Office', '034-432-0894', 'Government', 8, '2026-02-20 03:11:15'),
(34, 'Department of Health (DOH) Region VI', '033-337-8138', 'Government', 8, '2026-02-20 03:11:15'),
(35, 'Bacolod City Parks & Playground Division', '034-432-0835', 'Government', 9, '2026-02-20 03:11:15'),
(36, 'National Housing Authority (NHA) - Bacolod', '034-432-1010', 'Government', 9, '2026-02-20 03:11:15');

-- --------------------------------------------------------

--
-- Table structure for table `agency_users`
--

CREATE TABLE `agency_users` (
  `agency_user_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agency_users`
--

INSERT INTO `agency_users` (`agency_user_id`, `agency_id`, `username`, `email`, `password_hash`, `full_name`, `contact_number`, `is_active`, `created_at`, `last_login`) VALUES
(1, 19, 'engineering', 'engineering@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'Engineering Office Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', '2026-06-20 14:22:26'),
(2, 21, 'cenro', 'cenro@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'CENRO Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(3, 23, 'baciwa', 'baciwa@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'BACIWA Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(4, 25, 'pnp', 'pnp@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'Bacolod City Police Office (BCPO) Coordinator', '+639123456789', 1, '2026-03-03 23:46:31', NULL),
(5, 26, 'bfp', 'bfp@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'Bureau of Fire Protection - Bacolod Coordinator', '+639123456789', 1, '2026-03-03 23:46:31', '2026-06-09 20:49:01'),
(6, 27, 'cenro_env', 'cenro.env@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'CENRO Environment Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(7, 28, 'denr', 'denr@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'DENR Region VI Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(8, 29, 'negrospower', 'negrospower@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'Negros Power Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(9, 31, 'lto', 'lto@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'LTO Bacolod Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', '2026-04-23 09:14:58'),
(10, 32, 'btao', 'btao@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'Bacolod Traffic Authority Office Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(11, 33, 'healthoffice', 'healthoffice@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'Bacolod City Health Office Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(12, 35, 'parks', 'parks@agap-link.gov.ph', '$2y$10$jdqAAYQQ399iNWxnmcopfedzDhqhYFjzFgxqjhQFseZnCJLcxFYpa', 'Bacolod Parks & Playground Division Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL),
(13, 24, 'drrmo', 'drrmo@agap-link.gov.ph', '$2y$10$BsHHR4ivyPS6RSJl.3lF4OrggqR0HlQ6q0tjrQtg8kPXLs2ixTQaO', 'Drainage & Flood Control Coordinator', '+639123456789', 1, '2026-03-04 00:10:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `image_path`, `created_by`, `created_at`, `updated_at`) VALUES
(2, '09022005', '09022005', 'ann_69c1da5211feb.jpg', 1, '2026-03-24 00:26:58', '2026-03-24 00:26:58'),
(3, 'Oil Price Hike', 'Oil Price rises up to 5 pesos as of today.', 'ann_69c1dc6ec0c00.jpg', 1, '2026-03-24 00:35:58', '2026-03-24 00:35:58'),
(4, 'Spider-Man Brand New Day', 'Peter Parker obtains organic webbing as his genes begin to mutate and as he goes through a lot of trauma.', 'ann_69c1dd588a5dc.jpg', 1, '2026-03-24 00:39:52', '2026-03-24 00:39:52'),
(5, 'Spider-Man Brand New Day', 'Peter Parker obtains organic webbing as his genes begin to mutate and as he goes through a lot of trauma.', 'ann_69c1ddfb5b5e5.jpg', 1, '2026-03-24 00:42:35', '2026-03-24 00:42:35'),
(6, 'Spider-Man Brand New Day', 'Spider-Man', 'ann_69c1ef5715bab.jpg', 1, '2026-03-24 01:56:39', '2026-03-24 01:56:39'),
(7, 'Spider-Man 1994', 'Spider-Man', 'ann_69c1f3659bc65.jpg', 1, '2026-03-24 02:13:57', '2026-03-24 02:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Infrastructure', 'Roads, bridges, sidewalks, street lights, public infrastructure', '2026-02-09 09:35:06'),
(2, 'Waste Management', 'Garbage collection, illegal dumping, littering, waste disposal', '2026-02-09 09:35:06'),
(3, 'Water & Sanitation', 'Water supply issues, drainage, sewage problems, flooding', '2026-02-09 09:35:06'),
(4, 'Public Safety', 'Crime, unsafe areas, missing street signs, security concerns', '2026-02-09 09:35:06'),
(5, 'Environment', 'Pollution, illegal logging, environmental hazards, air quality', '2026-02-09 09:35:06'),
(6, 'Utilities', 'Power outages, damaged electrical posts, utility line issues', '2026-02-09 09:35:06'),
(7, 'Traffic', 'Traffic violations, road hazards, parking issues, traffic management', '2026-02-09 09:35:06'),
(8, 'Public Health', 'Health hazards, pest control, sanitation, disease prevention', '2026-02-09 09:35:06'),
(9, 'Community Facilities', 'Parks, playgrounds, public buildings, recreational facilities', '2026-02-09 09:35:06');

-- --------------------------------------------------------

--
-- Table structure for table `login_tokens`
--

CREATE TABLE `login_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_tokens`
--

INSERT INTO `login_tokens` (`token_id`, `user_id`, `token_code`, `expires_at`, `used`, `created_at`) VALUES
(19, 16, '068888', '2026-03-29 23:36:53', 0, '2026-03-29 23:31:53'),
(20, 17, '206709', '2026-03-29 23:45:33', 0, '2026-03-29 23:40:33'),
(23, 5, '847288', '2026-04-19 02:34:06', 0, '2026-04-19 02:29:06'),
(26, 18, '571543', '2026-04-19 18:45:54', 1, '2026-04-19 18:40:54'),
(30, 15, '059022', '2026-04-19 20:58:27', 1, '2026-04-19 20:53:27'),
(40, 11, '869532', '2026-04-22 21:13:56', 1, '2026-04-22 21:08:56'),
(43, 19, '805586', '2026-04-22 22:17:53', 1, '2026-04-22 22:12:53'),
(68, 20, '856444', '2026-06-21 17:41:27', 1, '2026-06-21 17:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `otp_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `channel` enum('sms','email') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_otps`
--

INSERT INTO `password_reset_otps` (`otp_id`, `user_id`, `otp_code`, `channel`, `expires_at`, `used`, `created_at`) VALUES
(22, 11, '414715', 'email', '2026-04-19 12:12:44', 0, '2026-04-19 12:07:44'),
(24, 19, '497684', 'sms', '2026-04-22 22:14:12', 1, '2026-04-22 22:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `registration_verifications`
--

CREATE TABLE `registration_verifications` (
  `verification_id` int(11) NOT NULL,
  `temp_first_name` varchar(100) NOT NULL,
  `temp_middle_initial` varchar(5) DEFAULT NULL,
  `temp_last_name` varchar(100) NOT NULL,
  `temp_email` varchar(150) NOT NULL,
  `temp_phone` varchar(20) NOT NULL,
  `temp_password_hash` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assigned_agency_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `gps_lat` decimal(10,8) DEFAULT NULL,
  `gps_long` decimal(11,8) DEFAULT NULL,
  `status` enum('Pending','Verified','Forwarded','Ongoing','Resolved') DEFAULT 'Pending',
  `priority` enum('Low','Medium','Critical') DEFAULT 'Low',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = active, 1 = archived (soft-deleted)',
  `archived_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `agency_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `user_id`, `category_id`, `assigned_agency_id`, `description`, `address`, `photo_path`, `gps_lat`, `gps_long`, `status`, `priority`, `created_at`, `updated_at`, `is_archived`, `archived_at`, `is_verified`, `agency_verified_at`) VALUES
(3, 5, 1, 19, 'fhdfhf', 'La Salle Avenue, Villamonte, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_69a79c49e61b03.30825166.png', 10.67971734, 122.96084690, 'Verified', 'Medium', '2026-03-04 02:43:21', '2026-06-20 06:22:52', 0, NULL, 1, NULL),
(4, 5, 1, 19, '67', 'B.S. Aquino Drive, Sunflower, Barangay 7, Villamonte, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_69a79eed1056d1.00393859.png', 10.67899620, 122.95932770, 'Pending', 'Medium', '2026-03-04 02:54:37', '2026-06-10 06:08:25', 1, '2026-06-10 14:08:25', 0, NULL),
(15, 20, 7, 31, 'Guba nga dalan.', 'Estefania, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_69e94a52bef576.75924458.jpg', 10.65899908, 122.99639531, 'Ongoing', 'Medium', '2026-04-22 22:23:14', '2026-04-23 00:28:13', 0, NULL, 1, NULL),
(16, 20, 7, 31, 'Test', 'Estefania, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_69e968c6d5def4.35991887.jpg', 10.65897901, 122.99643979, 'Resolved', 'Medium', '2026-04-23 00:33:10', '2026-04-23 01:14:34', 0, NULL, 1, NULL),
(17, 20, 1, NULL, 'Lorem Ipsum', 'Museo de La Salle, La Salle Avenue, La Salleville, Villamonte, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_6a2f708515c647.33879383.png', 10.67862240, 122.96244082, 'Pending', 'Medium', '2026-06-15 03:24:53', '2026-06-15 03:24:53', 0, NULL, 0, NULL),
(18, 20, 1, NULL, 'Broken Building', 'Estefania, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_6a3603e62533f2.37104308.jpg', 10.65898968, 122.99643372, 'Pending', 'Medium', '2026-06-20 03:07:18', '2026-06-20 03:07:18', 0, NULL, 0, NULL),
(19, 20, 7, NULL, 'Manhole', 'Estefania, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', '/agap_link/uploads/report_6a360442555b60.56994849.jpg', 10.65899486, 122.99656667, 'Pending', 'Medium', '2026-06-20 03:08:50', '2026-06-20 03:08:50', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `report_logs`
--

CREATE TABLE `report_logs` (
  `log_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `status_change` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_logs`
--

INSERT INTO `report_logs` (`log_id`, `report_id`, `status_change`, `remarks`, `timestamp`) VALUES
(1, 3, 'Verified', NULL, '2026-03-04 02:44:22'),
(2, 3, 'Verified', NULL, '2026-03-04 02:44:24'),
(3, 3, 'Verified', NULL, '2026-03-04 02:44:26'),
(10, 15, 'Forwarded', NULL, '2026-04-22 22:24:27'),
(11, 15, 'Forwarded', NULL, '2026-04-22 22:25:54'),
(12, 15, 'Ongoing', NULL, '2026-04-22 22:26:14'),
(13, 15, 'Pending', NULL, '2026-04-22 22:47:20'),
(14, 15, 'Forwarded', NULL, '2026-04-22 23:37:51'),
(15, 15, 'Ongoing', 'Updated by agency: Land Transportation Office (LTO) - Bacolod', '2026-04-23 00:28:13'),
(16, 16, 'Resolved', 'Updated by agency: Land Transportation Office (LTO) - Bacolod', '2026-04-23 01:14:34'),
(17, 16, 'Resolved', 'Updated by agency: Land Transportation Office (LTO) - Bacolod', '2026-04-23 01:15:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_initial`, `last_name`, `email`, `phone_number`, `password_hash`, `created_at`, `last_login`) VALUES
(5, 'Peter', NULL, 'Parker', 'peterparker@email.com', '09123456789', '$2y$10$7Hrm88DdYzTvhuQtdunfOeOdQMrtpr3ORCTSR86VlXNsl3IM3wXQu', '2026-02-10 03:46:37', '2026-04-19 02:29:11'),
(8, 'LEO CERLYN', 'A', 'TACSAGON', 'alvinferrer691@gmail.com', '09087053123', '$2y$10$LF6GU32okdUXO6vQpyQSO.gzSTf3gL5HgDEmT.i1HF1QdiqJL7E1a', '2026-02-20 02:19:04', NULL),
(16, 'Jayram', NULL, 'Garcia', 'jaryam123@gmail.com', '09207828624', '$2y$10$SaLqtLIeAy/tdGk3HaOEmu4T501Od4m/sfOmNU5dRJs1Ib7M/4i.u', '2026-03-29 23:31:24', NULL),
(17, 'David', NULL, 'Buala', 'davidbuala@email.com', '09942017579', '$2y$10$20.9ECHwiycO.I2tkhnsfORoVN1L4C85YJ6FtTHwWd4Zajtg6t7dm', '2026-03-29 23:40:24', NULL),
(18, 'Matthew Justin', NULL, 'Intoy', 'intoymatthewjustin@gmail.com', '09949926492', '$2y$10$CwqIvJHJb8..qStx/MpdHeYxrKEW1C6r55STaVnE2FrbzJ9NP5uwu', '2026-04-19 18:37:46', '2026-04-19 18:40:57'),
(20, 'Matthew Justin', NULL, 'Intoy', 's2402438@usls.edu.ph', '09949926492', '$2y$10$6mc44VhBQnhj9SLvyCleGORshk/as7l/q/C8UuJufiAmUZYQEUel6', '2026-04-22 22:13:57', '2026-06-21 17:36:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`agency_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `agency_users`
--
ALTER TABLE `agency_users`
  ADD PRIMARY KEY (`agency_user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `idx_user_token` (`user_id`,`token_code`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `idx_user_otp` (`user_id`,`otp_code`);

--
-- Indexes for table `registration_verifications`
--
ALTER TABLE `registration_verifications`
  ADD PRIMARY KEY (`verification_id`),
  ADD UNIQUE KEY `temp_email` (`temp_email`),
  ADD KEY `idx_email_otp` (`temp_email`,`otp_code`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `assigned_agency_id` (`assigned_agency_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_reports_archived` (`is_archived`);

--
-- Indexes for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_last_name` (`last_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `agency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `agency_users`
--
ALTER TABLE `agency_users`
  MODIFY `agency_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `registration_verifications`
--
ALTER TABLE `registration_verifications`
  MODIFY `verification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `report_logs`
--
ALTER TABLE `report_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agencies`
--
ALTER TABLE `agencies`
  ADD CONSTRAINT `agencies_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `agency_users`
--
ALTER TABLE `agency_users`
  ADD CONSTRAINT `agency_users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`agency_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`assigned_agency_id`) REFERENCES `agencies` (`agency_id`) ON DELETE SET NULL;

--
-- Constraints for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD CONSTRAINT `report_logs_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`report_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
