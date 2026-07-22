-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 10:35 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
  `type` varchar(50) NOT NULL,
  `owno` varchar(30) NOT NULL,
  `owname` varchar(30) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_info`
--

INSERT INTO `computer_info` (`id`, `sn`, `model`, `type`, `owno`, `owname`, `date`) VALUES
(21, 'PF4L50WX', 'LENOVO', 'student', '21RP06834', 'Mfitumukiza Eric', '2024-07-31'),
(22, 'PF4KMLM6', 'LENOVO', 'student', '21RP06780', 'Tuyishimire Gaspard', '2024-07-31'),
(23, 'PF4KLQML', 'LENOVO', 'student', '21RP03769', 'Gahozo Sosthene', '2024-07-31'),
(24, 'TRF004', 'HP', 'other', '1200080015190064', 'Uwimana', '2024-08-01'),
(25, 'TRF005', 'HP', 'staff', '1200280015190064', 'Rukundo', '2024-08-05'),
(27, 'wr12346', 'DELL', 'student', '21RP06836', 'Gahozo Sosthene', '2024-08-06');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `sn` varchar(100) NOT NULL,
  `model` varchar(1000) NOT NULL,
  `type` varchar(50) NOT NULL,
  `owno` varchar(100) NOT NULL,
  `owname` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `comment` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `sn`, `model`, `type`, `owno`, `owname`, `action`, `comment`, `date`) VALUES
(25, 'PF4KLQML', 'LENOVO', 'student', '21RP03769', 'Gahozo Sosthene', 'check-in', '', '2024-07-31 11:37:14'),
(26, 'PF4KMLM6', 'LENOVO', 'student', '21RP06780', 'Tuyishimire Gaspard', 'check-in', '', '2024-07-31 13:20:30'),
(27, 'PF4L50WX', 'LENOVO', 'student', '21RP06834', 'Mfitumukiza Eric', 'check-in', '', '2024-07-31 13:21:01'),
(28, 'PF4KLQML', 'LENOVO', 'student', '21RP03769', 'Gahozo Sosthene', 'check-out', '', '2024-07-31 13:21:36'),
(29, 'PF4KMLM6', 'LENOVO', 'student', '21RP06780', 'Tuyishimire Gaspard', 'check-out', '', '2024-07-31 13:22:25'),
(30, 'PF4L50WX', 'LENOVO', 'student', '21RP06834', 'Mfitumukiza Eric', 'check-out', '', '2024-07-31 13:24:52'),
(31, 'TRF005', 'HP', 'staff', '1200280015190064', 'Rukundo', 'check-in', 'Well done ✅', '2024-08-06 14:06:56'),
(32, 'wr12346', 'DELL', 'student', '21RP06836', 'Gahozo Sosthene', 'check-out', '', '2024-08-06 14:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `no` int(10) NOT NULL,
  `regnumber` varchar(10) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `sector` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `user_type` varchar(255) NOT NULL,
  `nid` varchar(255) NOT NULL,
  `names` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `nid`, `names`, `email`, `password`) VALUES
(8, 'Guest', '21RP03769', 'Gahozo Sosthene', 'gahozo909@gmail.com', '$2y$10$JTC3d7o2Lr2Faar1ImHCp.x.Z2AqahvgZCDH36XMEYmyu.f9q/NBC'),
(32, 'Admin', '21RP06834', 'mfitus', 'mfitumukizaeric3@gmail.com', '$2y$10$yAB2WkJHr9YRszgsB3mNfOsE1ckoxzH/1Yzs.shcsMR2O/rONjvx.'),
(33, 'Guest', '12345', 'mimi', 'mimi@gmail.com', '$2y$10$UIBhQSUx6N2M66xe3oBHk.WKDgB6ZGU3SXMudm0.o07sS4Id77D2S');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `computer_info`
--
ALTER TABLE `computer_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD UNIQUE KEY `owno` (`owno`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `regnumber` (`regnumber`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nid` (`nid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `computer_info`
--
ALTER TABLE `computer_info`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `no` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
