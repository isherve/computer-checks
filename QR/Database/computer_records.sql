-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2024 at 09:54 AM
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
-- Database: `computer_records`
--

-- --------------------------------------------------------

--
-- Table structure for table `computer_info`
--

CREATE TABLE `computer_info` (
  `id` int(50) NOT NULL,
  `sn` varchar(20) NOT NULL,
  `model` varchar(30) NOT NULL,
  `owno` varchar(30) NOT NULL,
  `owname` varchar(30) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_info`
--

INSERT INTO `computer_info` (`id`, `sn`, `model`, `owno`, `owname`, `date`) VALUES
(1, 'PF4KMLM6', 'Lenovo', '21UTB06780', 'Tuyishimire Gaspard', '2024-03-19'),
(2, 'PF4KMLM6', 'Lenovo', '21UTB06780', 'Tuyishimire Gaspard', '2024-03-19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `user_type` varchar(255) NOT NULL,
  `nid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `nid`, `name`, `email`, `password`) VALUES
(6, 'Admin', '21UTB06834', 'Mfitumukiza Eric', 'mfitumukizaeric3@gmail.com', '$2y$10$bD3W3M/bb/AFoOX6sOASiO6SF3HUn4pxlTw1zWOT2Sa12FdZ6joNi'),
(7, 'Admin', '21UTB06780', 'Tuyishimire Gaspard', 'tuyishimiregaspard20@gmail.com', '$2y$10$2MuRCnXvgXG5KSCEhp50pu.QZ.FSNZaG6W/lGOFpkuQNZZPrSxsFO'),
(8, 'Guest', '21UTB03769', 'Gahozo Sosthene', 'gahozo909@gmail.com', '$2y$10$JTC3d7o2Lr2Faar1ImHCp.x.Z2AqahvgZCDH36XMEYmyu.f9q/NBC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `computer_info`
--
ALTER TABLE `computer_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `computer_info`
--
ALTER TABLE `computer_info`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
