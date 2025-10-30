-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-10-18 10:18:53
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `waterland`
--

-- --------------------------------------------------------

--
-- 表的结构 `carts`
--

CREATE TABLE `carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `package_id` int(10) UNSIGNED NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `package_id`, `qty`, `created_at`, `updated_at`) VALUES
(22, 1, 2, 1, '2025-10-16 19:51:22', '2025-10-16 19:51:22');

-- --------------------------------------------------------

--
-- 表的结构 `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `role_snapshot` enum('guest','customer') NOT NULL,
  `package_name` varchar(120) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `user_id`, `role_snapshot`, `package_name`, `rating`, `message`, `guest_name`, `guest_email`, `created_at`) VALUES
(2, NULL, 'guest', 'hotel+ticket', 5, 'Nice', 'yuheng', 'yuheng@gmail.com', '2025-08-27 03:43:39'),
(3, NULL, 'guest', 'park ticket', 5, 'good experience', 'azad', 'azadshamsul01@gmail.com', '2025-08-27 03:44:25'),
(4, NULL, 'guest', 'hotel', 3, 'nice', 'jaden', 'leexingjue0908@gmail.com', '2025-09-11 12:14:48'),
(5, 1, 'customer', 'hotel', 5, 'nice', NULL, NULL, '2025-09-16 14:21:57');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `bill_code` varchar(100) DEFAULT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `payment_channel` varchar(50) DEFAULT NULL,
  `total_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `status`, `bill_code`, `payment_ref`, `payment_channel`, `total_usd`, `customer_name`, `customer_email`, `customer_phone`, `created_at`, `updated_at`) VALUES
(1, 1, 'pending', NULL, NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 15:21:03', '2025-09-22 15:21:03'),
(2, 1, 'pending', NULL, NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-22 20:48:47', '2025-09-22 20:48:47'),
(3, 1, 'pending', NULL, NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-22 20:55:10', '2025-09-22 20:55:10'),
(4, 1, 'pending', NULL, NULL, NULL, 599.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-22 20:56:13', '2025-09-22 20:56:13'),
(5, 1, 'pending', NULL, NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 20:58:19', '2025-09-22 20:58:19'),
(6, 1, 'pending', '9bd6wpkh', NULL, NULL, 899.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 20:59:30', '2025-09-24 13:00:03'),
(7, 1, 'pending', 'e1llmwio', NULL, NULL, 899.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 21:03:48', '2025-09-24 13:00:01'),
(8, 1, 'paid', 'u4ay5pge', NULL, NULL, 899.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 21:17:30', '2025-09-22 21:18:48'),
(9, 1, 'paid', '7jpyzu7o', NULL, NULL, 899.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-22 21:19:43', '2025-09-22 21:22:06'),
(10, 4, 'paid', 'qvzfmh5w', NULL, NULL, 219.00, 'jinqian', 'chengjinqian@gmail.com', '0184690020', '2025-09-22 21:58:08', '2025-09-22 21:58:25'),
(11, 1, 'pending', 'lbsz9y5n', NULL, NULL, 1118.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-23 11:25:08', '2025-09-23 11:25:09'),
(12, 1, 'paid', 'ytddsyhe', NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-23 15:31:00', '2025-09-23 15:31:35'),
(13, 1, 'paid', 'excf790y', NULL, NULL, 1118.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-24 11:34:13', '2025-09-24 11:34:25'),
(14, 1, 'paid', 'd80p559q', NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-09-24 12:02:04', '2025-09-24 12:02:21'),
(15, 1, 'pending', 'ppibe7fc', NULL, NULL, 219.00, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-24 12:57:06', '2025-09-24 13:00:05'),
(16, 1, 'paid', 'zh2s9ib9', NULL, NULL, 0.01, 'jadenlee', 'leexingjue0908@gmail.com', '0184690020', '2025-09-24 12:58:31', '2025-09-24 12:59:12'),
(17, 1, 'paid', 'qgczk04s', NULL, NULL, 649.00, 'jadenlee', 'leexingjue0908@gmail.com', '', '2025-10-12 16:23:38', '2025-10-12 16:23:57');

-- --------------------------------------------------------

--
-- 表的结构 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `package_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `package_id`, `title`, `unit_price`, `qty`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-22 15:21:03'),
(2, 2, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-22 20:48:47'),
(3, 3, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-22 20:55:10'),
(4, 4, 4, 'Southwest Vacations Bundle', 599.00, 1, 599.00, '2025-09-22 20:56:13'),
(5, 5, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-22 20:58:19'),
(6, 6, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-22 20:59:30'),
(7, 7, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-22 21:03:48'),
(8, 8, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-22 21:17:30'),
(9, 9, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-22 21:19:43'),
(10, 10, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-22 21:58:08'),
(11, 11, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-23 11:25:08'),
(12, 11, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-23 11:25:08'),
(13, 12, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-23 15:31:00'),
(14, 13, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-24 11:34:13'),
(15, 13, 2, 'Dining Card Vacation Package', 899.00, 1, 899.00, '2025-09-24 11:34:13'),
(16, 14, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-24 12:02:04'),
(17, 15, 1, 'Hotel + Park Tickets', 219.00, 1, 219.00, '2025-09-24 12:57:06'),
(18, 16, 1, 'Hotel + Park Tickets', 0.01, 1, 0.01, '2025-09-24 12:58:31'),
(19, 17, 3, 'Costco Travel Bundle', 649.00, 1, 649.00, '2025-10-12 16:23:38');

-- --------------------------------------------------------

--
-- 表的结构 `packages`
--

CREATE TABLE `packages` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `packages`
--

INSERT INTO `packages` (`id`, `title`, `short_desc`, `price_usd`, `status`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'Hotel + Park Tickets', 'Bundle an on-site hotel stay with park admission. Early Park Admission on select dates.', 0.01, 'active', 'uploads/packages/pkg_20250917_023444_3843618f.jpeg', '2025-09-04 14:47:26', '2025-09-24 12:58:07'),
(2, 'Dining Card Vacation Package', 'Stay 4–5 nights and receive $300–$1,000 Dining Card credits by hotel tier & nights.', 899.00, 'active', 'uploads/packages/pkg_20250917_023452_e88e329f.jpeg', '2025-09-04 14:47:26', '2025-09-17 08:34:52'),
(3, 'Costco Travel Bundle', 'Theme park tickets + Early Park Admission + Costco digital shop card.', 649.00, 'active', 'uploads/packages/pkg_20250917_023507_1b116a13.jpeg', '2025-09-04 14:47:26', '2025-09-17 08:35:07'),
(4, 'Southwest Vacations Bundle', 'Flight + Hotel + Car + Multi-park tickets. Flexible length of stay.', 599.00, 'active', 'uploads/packages/pkg_20250917_023513_61cbd1ae.jpeg', '2025-09-04 14:47:26', '2025-09-17 08:35:13');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `otp_sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar`, `password`, `role`, `is_verified`, `otp_code`, `otp_expires`, `reset_otp`, `reset_expires`, `created_at`, `deleted_at`, `otp_sent_at`) VALUES
(1, 'jadenlee', 'leexingjue0908@gmail.com', NULL, '$2y$10$/H8oPKYVyt323eMxYDXB6.498J42/0IvSesmxPganOV8stt0J42uu', 'customer', 1, NULL, NULL, '574222', '2025-09-19 15:43:45', '2025-08-09 08:50:12', NULL, NULL),
(3, 'azhad', 'azadshamsul01@gmail.com', NULL, '$2y$10$nWFyAo7y9bupg0Drm/dBiedWRElfG4SMRF3NCO.ZAsRfkeZ06PBAS', 'customer', 1, NULL, NULL, NULL, NULL, '2025-08-09 09:13:12', NULL, NULL),
(4, 'jinqian', 'chengjinqian@gmail.com', NULL, '$2y$10$jpA/W/Hgp5vRPK5N4CITX.w9DocCjW2RRMhNLKyULaqdiO7pWHvoy', 'customer', 1, NULL, NULL, NULL, NULL, '2025-08-09 09:18:48', NULL, NULL),
(6, 'System Admin', 'admin@example.com', NULL, '$2y$10$iuBAWzxGZ/3ar7y/ELBRwuSCmAYO.Pj9wzEdC2Rdjp8d/2aLIOEXm', 'admin', 1, '900567', '2025-08-27 13:06:52', NULL, NULL, '2025-08-12 06:40:10', NULL, NULL),
(7, 'Park Staff', 'staff@example.com', NULL, '$2y$10$tss562YRoyHPd8QyqFSGuuiNTSRyRpjmbNMyPh8Ds3AEMfOHxncF.', 'staff', 1, NULL, NULL, NULL, NULL, '2025-08-12 06:40:10', NULL, NULL),
(8, 'jon', 'jon@email.com', NULL, '$2y$10$W1LmB1xhu0fz.e8Lb137IOSZBXanQIfhURZrKO9wzohC61R51fdce', 'staff', 1, NULL, NULL, NULL, NULL, '2025-08-27 03:52:31', NULL, NULL),
(9, 'Azad Admin', 'azadshamsul02@gmail.com', NULL, '111111\r\n', 'admin', 1, NULL, NULL, NULL, NULL, '2025-08-27 05:00:24', NULL, NULL),
(12, 'Admin User', 'admin2@example.com', NULL, '$2y$10$FCJ2cJX3Bfkh.pZzHN6dq.bB87NihLd35uKOojG6n7uVjIZktYaeW', 'admin', 1, NULL, NULL, NULL, NULL, '2025-08-27 11:30:48', NULL, NULL),
(15, 'System Admin', 'leexingjue@graduate.utm.my', NULL, '$2y$10$EFi8YGG1FhXC4nW9ByVtq.QnWNTRFuaWPl54fyPnHLvyaH4azFWGm', 'admin', 1, NULL, NULL, '689848', '2025-09-24 12:45:00', '2025-08-27 12:28:52', NULL, NULL),
(17, 'qayyum', 'qayqayqay97@gmail.com', NULL, '$2y$10$fjpoPPDqx.Wgo4JZDbB.O.sYCBv6O.KRQgmkxfo6q4d1Rq9kmND/S', 'customer', 1, NULL, NULL, NULL, NULL, '2025-09-11 12:19:25', '2025-09-24 12:53:08', NULL),
(23, 'System Admin', 'newadmin@example.com', NULL, '$2y$10$bqLEagVzALhFuzEomXJzlOuDe3S9mY5nDrDkD3q48qqB3DO7yE9l.', 'admin', 1, NULL, NULL, NULL, NULL, '2025-09-17 10:32:50', NULL, NULL),
(27, 'bo', 'bob@gmail.com', NULL, '$2y$10$6Ft0/xp9yc6ywndBgfZbk.LBaqmyag6VDQ/x0T/hhPh7HL65AD2wC', 'staff', 1, NULL, NULL, NULL, NULL, '2025-09-19 07:09:26', NULL, NULL),
(31, 'jadenlee0908', 'jadenlee09082005@gmail.com', NULL, '$2y$10$a6rYb1OfB1MHedKz2wnHXuQ2W19eBZ85wUTq96AtfbvmDEmVfNKdK', 'staff', 1, NULL, NULL, NULL, NULL, '2025-09-24 04:53:42', NULL, NULL);

--
-- 转储表的索引
--

--
-- 表的索引 `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_package` (`user_id`,`package_id`),
  ADD KEY `fk_carts_pack` (`package_id`);

--
-- 表的索引 `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- 表的索引 `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_reset_expires` (`reset_expires`),
  ADD KEY `idx_users_role_deleted` (`role`,`deleted_at`),
  ADD KEY `idx_users_name_email` (`name`,`email`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 使用表AUTO_INCREMENT `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- 限制导出的表
--

--
-- 限制表 `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_pack` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `fk_feedbacks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 限制表 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
