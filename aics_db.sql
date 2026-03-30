-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 05:41 AM
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
-- Database: `aics_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aics_record`
--

CREATE TABLE `aics_record` (
  `id` int(11) NOT NULL,
  `control_number` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `beneficiary_id` int(11) DEFAULT NULL,
  `assistance_type_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `diagnoses` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `office_control` varchar(100) DEFAULT NULL,
  `date_released` date DEFAULT NULL,
  `status` enum('pending','released','cancelled') DEFAULT 'pending',
  `encoder_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assistance_type`
--

CREATE TABLE `assistance_type` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistance_type`
--

INSERT INTO `assistance_type` (`id`, `name`, `description`, `max_amount`) VALUES
(1, 'Medical Assistance', NULL, 5000.00),
(2, 'Burial Assistance', NULL, 10000.00),
(3, 'Educational Assistance', NULL, 3000.00),
(4, 'Food Assistance', NULL, 2000.00),
(5, 'Transportation Assistance', NULL, 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `municipality` varchar(100) DEFAULT 'Your Municipality',
  `province` varchar(100) DEFAULT 'Your Province'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `beneficiary`
--

CREATE TABLE `beneficiary` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `relationship` varchar(80) DEFAULT NULL,
  `claimant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claimant`
--

CREATE TABLE `claimant` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','encoder','viewer') DEFAULT 'encoder',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'Administrator', 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', 1, '2026-03-30 03:32:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aics_record`
--
ALTER TABLE `aics_record`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `control_number` (`control_number`),
  ADD KEY `claimant_id` (`claimant_id`),
  ADD KEY `beneficiary_id` (`beneficiary_id`),
  ADD KEY `assistance_type_id` (`assistance_type_id`),
  ADD KEY `encoder_id` (`encoder_id`);

--
-- Indexes for table `assistance_type`
--
ALTER TABLE `assistance_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `beneficiary`
--
ALTER TABLE `beneficiary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claimant_id` (`claimant_id`);

--
-- Indexes for table `claimant`
--
ALTER TABLE `claimant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aics_record`
--
ALTER TABLE `aics_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assistance_type`
--
ALTER TABLE `assistance_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `beneficiary`
--
ALTER TABLE `beneficiary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claimant`
--
ALTER TABLE `claimant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aics_record`
--
ALTER TABLE `aics_record`
  ADD CONSTRAINT `aics_record_ibfk_1` FOREIGN KEY (`claimant_id`) REFERENCES `claimant` (`id`),
  ADD CONSTRAINT `aics_record_ibfk_2` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiary` (`id`),
  ADD CONSTRAINT `aics_record_ibfk_3` FOREIGN KEY (`assistance_type_id`) REFERENCES `assistance_type` (`id`),
  ADD CONSTRAINT `aics_record_ibfk_4` FOREIGN KEY (`encoder_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `beneficiary`
--
ALTER TABLE `beneficiary`
  ADD CONSTRAINT `beneficiary_ibfk_1` FOREIGN KEY (`claimant_id`) REFERENCES `claimant` (`id`);

--
-- Constraints for table `claimant`
--
ALTER TABLE `claimant`
  ADD CONSTRAINT `claimant_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
