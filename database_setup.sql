-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 16, 2026 at 12:03 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Indie'),
(2, 'Co-op'),
(3, 'Horror'),
(4, 'Racing');

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

DROP TABLE IF EXISTS `library`;
CREATE TABLE IF NOT EXISTS `library` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `users_id` int NOT NULL,
  `products_id` int NOT NULL,
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `users_id` (`users_id`),
  KEY `products_id` (`products_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `library`
--

INSERT INTO `library` (`order_id`, `users_id`, `products_id`, `purchase_date`, `price`) VALUES
(1, 4, 7, '2026-04-15 23:36:29', 69.99),
(2, 5, 1, '2026-04-15 23:41:12', 59.99),
(3, 2, 2, '2026-04-15 23:43:33', 14.99),
(4, 2, 7, '2026-04-15 23:43:51', 69.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `fk_category` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `image_url`, `video_url`, `created_at`, `category_id`) VALUES
(1, 'Mario Kart 8 Deluxe\r\n', 'Fun racing game', 59.99, 'Mario Kart 8 Deluxe .jpg', 'https://www.youtube.com/watch?v=3UdRDlyJXLg&list=RD3UdRDlyJXLg&start_radio=1', '2026-04-12 18:29:12', 4),
(2, 'Stardew Valley', 'A peaceful country-life RPG where you build the farm of your dreams.', 14.99, 'stardewValley.jpg', 'https://www.youtube.com/watch?v=ot7uXNQskhs', '2026-04-12 18:33:47', 1),
(3, 'Resident Evil 4', 'Survival is just the beginning. A daring rescue mission in a secluded European village.', 59.99, 'resident_evil_4.jpg', 'https://www.youtube.com/watch?v=j5Xv2lM9wes', '2026-04-12 18:36:06', 3),
(4, 'Hollow Knight', 'An epic action-adventure through a vast ruined kingdom of insects and heroes.', 14.99, 'hollow_knight.jpg', 'https://www.youtube.com/watch?v=6XGeJwsUP9c', '2026-04-12 18:40:19', 1),
(5, 'Terraria', 'Dig, fight, explore, build! Nothing is impossible in this action-packed adventure game.', 9.99, 'terraria.jpg', 'https://www.youtube.com/watch?v=w7uOhFTrrq0', '2026-04-15 22:53:04', 1),
(6, 'Cry of Fear', 'A psychological single-player and co-op horror game set in a deserted town filled with horrific creatures.', 0.00, 'cry_of_fear.jpg', 'https://www.youtube.com/watch?v=-lKZuJ0Novc', '2026-04-15 22:56:16', 3),
(7, 'Silent Hill 2', 'Experience a master-class in psychological survival horror. Having received a letter from his deceased wife, James returns to where they made so many memories.', 69.99, 'sh2_remake.jpg', 'https://www.youtube.com/watch?v=pyC_qiW_4ZY', '2026-04-15 23:04:40', 3),
(8, 'Forza Horizon 5', 'Your ultimate Horizon adventure awaits! Explore the vibrant and ever-evolving open world landscapes of Mexico with limitless, fun driving action.', 59.99, 'forza5.jpg', 'https://www.youtube.com/watch?v=SsZoqBH3aYw', '2026-04-15 23:08:03', 4),
(9, 'Rocket League', 'Soc-car! High-octane PvP action that combines arcade-style soccer with vehicular mayhem. Easy to learn, difficult to master.', 0.00, 'rocket_league.jpg', 'https://www.youtube.com/watch?v=P8eTMmw85Ug', '2026-04-15 23:09:58', 4),
(10, 'It Takes Two', 'Embark on the craziest journey of your life in this genre-bending platform adventure created purely for co-op.', 39.99, 'it_takes_two.jpg', 'https://www.youtube.com/watch?v=ohClxMmNLQQ', '2026-04-15 23:13:59', 2),
(11, 'Split Fiction', 'A mind-bending co-op adventure where players must literally split the world to solve puzzles and defeat enemies.', 29.99, 'split_fiction.jpg', 'https://www.youtube.com/watch?v=vRDXNJJ5Y3c', '2026-04-15 23:16:51', 2),
(12, 'A Way Out', 'A co-op only adventure where you play the role of one of two prisoners making their daring escape from prison.', 29.99, 'a_way_out.jpg', 'https://www.youtube.com/watch?v=-r5fY05t_7g', '2026-04-15 23:18:27', 2);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` int DEFAULT NULL,
  `products_id` int DEFAULT NULL,
  `rating` tinyint(1) NOT NULL,
  `review` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `products_id` (`products_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `pwd`, `email`, `profile_pic`, `created_at`) VALUES
(1, 'janedoe11', 'Jane23', 'janedoe@gmail.com', 'jane_pfp.jpg', '2026-04-01 22:48:41'),
(2, 'GamerPro99', 'securePassword123', 'gamerpro01@gmail.com', 'default_avatar.jpg', '2026-04-16 01:24:31'),
(3, 'Ghostface_26', 'password456', 'ghostFace@gmail.com', 'user_ghost.png', '2026-04-16 01:28:53'),
(4, 'MarcoD', 'password123', 'marcd@gmail.com', 'user_marc.jpg', '2026-04-16 01:31:10'),
(5, 'amine213', 'monPass99', 'aminee@gmail.com', 'amine_profile.jpg', '2026-04-16 01:32:05'),
(6, 'mxlekk02', 'pass02', 'malek02@gmail.com', 'malek_profile.jpg', '2026-04-16 01:33:13'),
(7, 'douaa01', 'pass021', 'douaa01@gmail.com', 'douaa_profile.jpg', '2026-04-16 01:33:48');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
