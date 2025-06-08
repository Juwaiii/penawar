-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 08:02 AM
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
-- Database: `doctor_appointment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancelled_by` enum('doctor','patient') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `reason`, `created_at`, `cancelled_by`) VALUES
(1, 1, 1, '2025-06-10', '12:00:00', 'Cancelled', 'i feel bad pain in my ear and sometimes it bleeds', '2025-06-07 20:07:10', NULL),
(2, 1, 1, '2025-06-10', '12:00:00', 'Cancelled', 'i feel pain in my ear and sometimes its bleed', '2025-06-08 01:37:35', 'doctor'),
(3, 1, 2, '2025-06-09', '14:00:00', 'Pending', 'period pain', '2025-06-08 04:52:37', NULL),
(4, 1, 2, '2025-06-18', '09:00:00', 'Cancelled', 'operation inspection', '2025-06-08 04:53:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `billing_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `qualification` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization`, `phone`, `address`, `qualification`) VALUES
(1, 12, 'ear', '01932456733', 'klinik penawar', ''),
(2, 13, 'stomach', '010898675256', 'klinik penawar', '');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `last_restocked` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `treatment` text NOT NULL,
  `notes` text DEFAULT NULL,
  `record_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `blood_group` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `dob`, `gender`, `phone`, `address`, `blood_group`) VALUES
(1, 4, '2003-05-25', 'Female', '01111434032', 'no 23 jln pdv3 taman pd villa teluk kemang 71050 port dickson negeri sembilan', 'A+'),
(2, 5, '2003-05-12', 'Female', '0197987156', 'Kolej Kediaman Tun Hussein Onn', 'O+');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `medical_record_id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `ic_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `current_medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `first_name`, `last_name`, `created_at`) VALUES
(4, 'juwai', '$2y$10$Pblk5Q7SCUdwtpArAvUORuVt.PIi3OcuHOYqZg0qkVGAsaIjJM/2y', 'patient', 'juwaimuhdsin@gmail.com', 'Juwairiah', 'Muhammad sin', '2025-06-07 16:10:53'),
(5, 'hana', '$2y$10$UUgOj9nCstTtkiLmDo5JYuwqnOg6FKsbcsU.8Uu27i9RxlMtmVPXW', 'patient', 'hanamumtaz03@gmail.com', 'Hana', 'Hassan', '2025-06-07 17:03:55'),
(8, 'admin', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'admin', 'admin@example.com', 'Admin', 'User', '2025-06-07 18:01:20'),
(9, 'Admin1', '$2y$10$UjhTtWoDLtL.EoayrJW9g.lsOjMshAMZiSpCQuK.WJA/G0L5AuJU.', 'admin', 'msfsjj@gmail.com', 'Admin1', 'user', '2025-06-07 18:18:57'),
(10, 'Dr John', '$2y$10$CaZbNKDsImfSHp4jqJK.duW8J5MVL59qkBBURPBIIeo/47R77vLMK', 'doctor', 'john@gmail.com', 'John', 'Smith', '2025-06-07 19:00:28'),
(11, 'adik', '$2y$10$M.lSreqsAClOD1/0aVZ8Oei5aCgiwQfdq5oO2ZK2L0sXb32XrjwIW', 'patient', 'juhaidah@gmail.com', 'Juhaidah', 'Muhammad sin', '2025-06-07 19:15:00'),
(12, 'Dr Aida', '$2y$10$fEkdKX3CPlWC7RvscDiSYe47hv9FZMGA9qXuGqXEn5Cuzy7wjWMAi', 'doctor', 'Aida@gmail.com', 'Aida', 'Husin', '2025-06-07 19:48:25'),
(13, 'Dr Ica', '$2y$10$H51ACROwTA/7L4sb.c7J/ugMwEtZ.PAvzWWnBsG6hcWHpatr5pIsC', 'doctor', 'ica@gmail.co', 'AIsyah', 'Muhammad', '2025-06-07 20:14:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medical_record_id` (`medical_record_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
