-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2026 at 09:53 AM
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
-- Database: `clinicphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `expiration_date` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `type`, `description`, `quantity`, `expiration_date`) VALUES
(38, 'AMOXICILLIN 100MG/ML', 'DROPS', 'used to treat bacterial infections, such as respiratory, ear, throat, urinary, and skin infections.', 10, '2026-05'),
(39, 'MULTIVITAMINS 15ML', 'DROPS', 'used as a dietary supplement to support overall health, boost immunity, and prevent vitamin deficiencies.', 50, '2026-06'),
(40, 'CETIRICINE 15ML', 'DROPS', 'used to relieve allergy symptoms such as runny nose, sneezing, itching, watery eyes, and hives.', 48, '2026-03'),
(41, 'ASCORBIC ACID (APCEE) 15ML', 'DROPS', 'used to boost immunity, promote wound healing, and prevent or treat vitamin C deficiency.', 50, '2026-02'),
(42, 'CETIRICINE (ALLECURE P) 10ML', 'DROPS', 'used to relieve allergy symptoms such as sneezing, runny nose, itchy or watery eyes, and hives.', 1, '2026-02'),
(43, 'PARACETAMOL TEMPRA 5ML', 'DROPS', 'used to relieve fever and mild to moderate pain in children.', 7, '2026'),
(44, 'SEFALEXIN 10ML', 'DROPS', 'used to treat bacterial infections such as respiratory tract infections, skin infections, ear infections, and urinary tract infections', 4, '2026-05'),
(45, 'PARACETAMOL 15ML', 'DROPS', 'used to reduce fever and relieve mild to moderate pain, such as headaches, toothaches, and muscle aches', 4, '2026-05'),
(46, 'BASCORBIC ACID (MYREVIT-C)', 'DROPS', 'used to boost immunity, promote wound healing, and prevent or treat vitamin C deficiency.', 3, '2026-03'),
(47, 'AMOXICILLIN (MOXYLOR) 10ML', 'DROPS', 'used to treat bacterial infections such as respiratory, ear, throat, urinary, and skin infections', 5, '2026-10'),
(48, 'PARACETAMOL (100) 15ML', 'DROPS', 'used to reduce fever and relieve mild to moderate pain, such as headaches, toothaches, and muscle aches.', 18, '2026-03'),
(49, 'AMOXICILLIN (AXMEL) 250 MG/5ML', 'SYRUP', 'used to treat various bacterial infections, such as respiratory, ear, and urinary tract infections.', 4, '2027-04'),
(50, 'PARACETAMOL (PAEA 250) 250 MG/5ML', 'SYRUP', 'used to relieve pain and reduce fever', 20, '2026'),
(51, 'SALBUTAMOL (SULFATE) 2MG/5ML', 'SYRUP', 'used to relieve and prevent symptoms of asthma and other respiratory conditions by opening up the airways.', 2, '2026-02'),
(52, 'MEFENAMIC ACID (MEFECHM) 50MG/5L', 'SYRUP', 'used to relieve pain, inflammation, and reduce fever.', 4, '2027-01'),
(53, 'CETIRICINE (HYDROCHLORIDE) 5MG/5ML', 'SYRUP', 'used to relieve allergy symptoms such as runny nose, sneezing, itching, and watery eyes, as well as itching and swelling caused by hives.', 45, '2026-03'),
(54, 'VITEX NEGUNDO L. 30MG/5ML', 'SYRUP', 'used to relieve symptoms of pain, inflammation, and respiratory issues, as well as for its potential anti-inflammatory and antioxidant properties.', 26, '2026-06'),
(55, 'MULTIVITAMINS 120 ML', 'SYRUP', 'used to support overall health and fill nutritional gaps in the diet.', 10, '2026-05'),
(56, 'ASCORBIC ACID (NOVACEE) 100MG/5ML', 'SYRUP', 'used to prevent or treat vitamin C deficiency and support immune system function.', 12, '2026-07'),
(57, 'PARACETAMOL (ALAGESIC) 250MG/5ML', 'CAPSULE', 'used to relieve mild to moderate pain and reduce fever.', 4, '2028-01'),
(58, 'MEFENAMIC ACID ( MEFEIN) 500MG TABLET', 'CAPSULE', 'used to relieve pain, inflammation, and reduce fever, often prescribed for conditions like menstrual pain or arthritis.', 5, '2028-03'),
(59, 'MORINGA OLEIFERA LAM 500MG CAPSULE', 'CAPSULE', 'used as a dietary supplement to support overall health, providing antioxidants, vitamins, and minerals that may help boost energy, reduce inflammation, and improve immune function.', 17, '2025-11'),
(60, 'CEFALEXIN (EDIXIM) 500MG', 'CAPSULE', 'used to treat various bacterial infections, such as respiratory, skin, and urinary tract infections.', 2, '2027-03'),
(61, 'NAPROXEN	', 'CAPSULE', 'used to relieve pain, inflammation, and reduce fever, commonly used for conditions like arthritis, muscle pain, and menstrual cramps.', 1, '2025-04'),
(62, 'DOXYCYCLINE', 'CAPSULE', 'used to treat a wide range of bacterial infections, including respiratory, urinary tract, and skin infections, as well as conditions like acne and Lyme disease.	', 2, '2026-02'),
(63, 'VITEX NEGUNDO L.', 'CAPSULE', 'used to relieve symptoms of pain, inflammation, and respiratory issues, as well as for its potential anti-inflammatory and antioxidant properties.', 9, '2026-03'),
(78, 'qwerty', 'SYRUP', 'QWERTYUIO', 50, '2030-11'),
(79, 'qwerty', 'SYRUP', 'QWERTYUIO', 50, '2030-11'),
(80, 'qwerty', 'DROPS', 'asdfghjkl;', 50, '2030-09'),
(81, 'qwerty', 'CAPSULE', 'asdfghjkl', 34, '2033-04');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_logs`
--

CREATE TABLE `medicine_logs` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(255) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `patient_name` varchar(255) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `log_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_logs`
--

INSERT INTO `medicine_logs` (`id`, `medicine_name`, `action_type`, `quantity`, `patient_name`, `expiration_date`, `log_date`) VALUES
(1, 'AMOXICILLIN 100MG/ML', 'Released', 10, 'Roj angelo Charita', NULL, '2026-01-03 09:51:55'),
(2, 'PARACETAMOL (PAEA 250) 250 MG/5ML', 'Updated', 20, NULL, '2026-04-01', '2026-01-03 09:52:25'),
(3, 'qwerty', 'Added', 50, NULL, '2030-09-01', '2026-01-03 09:58:05'),
(4, 'MULTIVITAMINS 15ML', 'Released', 6, 'qwerty', NULL, '2026-01-05 09:58:00'),
(5, 'AMOXICILLIN 100MG/ML', 'Released', 10, 'bagasol, kit marjohn', NULL, '2026-01-06 09:16:41'),
(6, 'ASCORBIC ACID (APCEE) 15ML', 'Updated', 35, NULL, '2026-12-01', '2026-01-06 11:13:23'),
(7, 'CETIRICINE (ALLECURE P) 10ML', 'Updated', 1, NULL, '2026-02-01', '2026-01-06 11:13:43'),
(8, 'PARACETAMOL TEMPRA 5ML', 'Updated', 7, NULL, '2026-02-01', '2026-01-06 11:13:55'),
(9, 'AMOXICILLIN 100MG/ML', 'Updated', 19, NULL, '2026-01-01', '2026-01-06 11:39:17'),
(10, 'AMOXICILLIN 100MG/ML', 'Updated', 19, NULL, '2026-03-01', '2026-01-07 08:25:47'),
(11, 'AMOXICILLIN 100MG/ML', 'Updated', 19, NULL, '2026-04-01', '2026-01-07 08:26:08'),
(12, 'AMOXICILLIN 100MG/ML', 'Updated', 19, NULL, '2027-01-01', '2026-01-07 08:28:30'),
(13, 'ASCORBIC ACID (APCEE) 15ML', 'Expired', 35, 'System Auto-Detect', '0000-00-00', '2026-01-07 08:55:43'),
(14, 'PARACETAMOL TEMPRA 5ML', 'Expired', 7, 'System Auto-Detect', '0000-00-00', '2026-01-07 08:55:43'),
(15, 'PARACETAMOL (PAEA 250) 250 MG/5ML', 'Expired', 20, 'System Auto-Detect', '0000-00-00', '2026-01-07 08:55:43'),
(16, 'AMOXICILLIN 100MG/ML', 'Released', 2, 'Charita, Roj angelo', NULL, '2026-01-07 09:14:17'),
(17, 'AMOXICILLIN 100MG/ML', 'Released', 7, 'Charita, Roj angelo 1', NULL, '2026-01-07 09:18:24'),
(18, 'qwerty', 'Added', 34, NULL, '2033-04-01', '2026-01-07 09:19:10'),
(19, 'AMOXICILLIN 100MG/ML', 'Expired', 10, 'System Auto-Detect', '0000-00-00', '2026-01-07 09:19:16'),
(20, 'AMOXICILLIN 100MG/ML', 'Updated', 10, 'Admin Edit', '0000-00-00', '2026-01-07 09:22:59'),
(21, 'AMOXICILLIN 100MG/ML', 'Updated', 10, 'Admin Edit', '0000-00-00', '2026-01-07 09:23:55'),
(22, 'MULTIVITAMINS 15ML', 'Updated', 60, 'Admin Edit', '0000-00-00', '2026-01-07 09:24:38'),
(23, 'AMOXICILLIN 100MG/ML', 'Updated', 10, 'Admin Edit', '0000-00-00', '2026-01-07 09:57:27'),
(24, 'MORINGA OLEIFERA LAM 500MG CAPSULE', 'Released', 7, 'Charita, Roj angelo 2', NULL, '2026-01-07 09:58:14'),
(25, 'MULTIVITAMINS 15ML', 'Released', 10, 'Charita, Roj angelo', NULL, '2026-01-09 08:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `patient_medical_logs`
--

CREATE TABLE `patient_medical_logs` (
  `log_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `blood_pressure` varchar(50) DEFAULT NULL,
  `heart_rate` varchar(50) DEFAULT NULL,
  `temperature` varchar(50) DEFAULT NULL,
  `height` varchar(50) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `bmi` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `checkup_doctor` varchar(100) DEFAULT NULL,
  `date_logged` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_medical_logs`
--

INSERT INTO `patient_medical_logs` (`log_id`, `patient_id`, `blood_pressure`, `heart_rate`, `temperature`, `height`, `weight`, `bmi`, `description`, `checkup_doctor`, `date_logged`) VALUES
(1, 512, '120/80', '100', '37', '152', '40', '17.3', 'mild cold\r\n\r\nparacetamol 3x a day ', 'Krystal Mae Anarna', '2026-01-04 08:28:48'),
(2, 512, '125/85', '90', '39', '150', '50', '22.2', 'Bacterial infection\r\n\r\nCEFALEXIN (EDIXIM) 500MG 3x a day ', 'John Paul Dela Cruz', '2026-01-04 09:24:23'),
(3, 512, '125/85', '90', '39', '150', '50', '22.2', 'Bacterial infection\r\n\r\nCEFALEXIN (EDIXIM) 500MG 3x a day ', 'John Paul Dela Cruz', '2026-01-04 10:35:41'),
(4, 512, '125/85', '90', '39', '150', '50', '22.2', 'Bacterial infection\r\n\r\nCEFALEXIN (EDIXIM) 500MG 3x a day ', 'John Paul Dela Cruz', '2026-01-04 10:35:50'),
(5, 512, '125/85', '90', '39', '150', '50', '22.2', 'Bacterial infection\r\n\r\nCEFALEXIN (EDIXIM) 500MG 3x a day ', 'John Paul Dela Cruz', '2026-01-05 09:21:41'),
(6, 512, '', '', '', '', '', 'N/A', '', '', '2026-01-07 09:46:27'),
(7, 337, '120/80', '120', '36', '120', '80', '55.6', 'qwerty', 'Dr. Adelinno Labro', '2026-01-07 10:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `appointment_number` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `user_id`, `patient_name`, `appointment_date`, `appointment_number`, `created_at`, `is_completed`) VALUES
(56, 653, 'Princess Rilaine', '2025-02-14 00:00:00', 2, '0000-00-00 00:00:00', 0),
(57, 790, 'anthoneth estrope', '2025-02-14 00:00:00', 3, '0000-00-00 00:00:00', 0),
(122, 503, 'Noela quirante', '2025-05-07 00:00:00', 1, '0000-00-00 00:00:00', 1),
(125, 653, 'Princess Rilaine', '2025-05-23 00:00:00', 2, '0000-00-00 00:00:00', 1),
(126, 769, 'Noela quirante', '2025-05-22 00:00:00', 1, '0000-00-00 00:00:00', 0),
(127, 769, 'Noela quirante', '2025-05-23 00:00:00', 3, '0000-00-00 00:00:00', 0),
(128, 580, 'Rilaine charita', '2025-05-25 00:00:00', 1, '0000-00-00 00:00:00', 0),
(129, 580, 'Rilaine charita', '2025-05-28 00:00:00', 1, '0000-00-00 00:00:00', 0),
(130, 580, 'Rilaine charita', '2025-05-27 00:00:00', 1, '0000-00-00 00:00:00', 0),
(132, 125, 'jayden regulto', '2025-09-03 00:00:00', 1, '0000-00-00 00:00:00', 0),
(134, 893, 'Roj angelo Charita', '2025-10-26 00:00:00', 1, '0000-00-00 00:00:00', 0),
(153, 512, 'Roj angelo Charita', '2025-12-17 00:00:00', 1, '0000-00-00 00:00:00', 0),
(155, 975, 'kit marjohn bagasol', '2025-12-17 00:00:00', 3, '0000-00-00 00:00:00', 0),
(156, 337, 'jin clarence hular', '2025-12-17 00:00:00', 4, '0000-00-00 00:00:00', 0),
(157, 541, 'anthoneth estrope', '2025-12-17 00:00:00', 5, '0000-00-00 00:00:00', 0),
(158, 180, 'rewytu weqrt', '2025-12-17 00:00:00', 6, '0000-00-00 00:00:00', 0),
(159, 513, 'Roj angelo 1 Charita', '2025-12-17 00:00:00', 7, '0000-00-00 00:00:00', 0),
(160, 514, 'Roj angelo 2 Charita', '2025-12-17 00:00:00', 8, '0000-00-00 00:00:00', 0),
(161, 515, 'Roj angelo 3 Charita', '2025-12-17 00:00:00', 9, '0000-00-00 00:00:00', 0),
(162, 516, 'Roj angelo 4 Charita', '2025-12-17 00:00:00', 10, '0000-00-00 00:00:00', 0),
(163, 517, 'Roj angelo 5 Charita', '2025-12-17 00:00:00', 11, '0000-00-00 00:00:00', 0),
(164, 518, 'Roj angelo 6 Charita', '2025-12-17 00:00:00', 12, '0000-00-00 00:00:00', 0),
(165, 519, 'Roj angelo 7 Charita', '2025-12-17 00:00:00', 13, '0000-00-00 00:00:00', 0),
(166, 520, 'Roj angelo 8 Charita', '2025-12-17 00:00:00', 14, '0000-00-00 00:00:00', 0),
(167, 521, 'Roj angelo 9 Charita', '2025-12-17 00:00:00', 15, '0000-00-00 00:00:00', 0),
(168, 522, 'Roj angelo 10 Charita', '2025-12-17 00:00:00', 16, '0000-00-00 00:00:00', 0),
(169, 523, 'Roj angelo 11 Charita', '2025-12-17 00:00:00', 17, '0000-00-00 00:00:00', 0),
(170, 524, 'Roj angelo 12 Charita', '2025-12-17 00:00:00', 18, '0000-00-00 00:00:00', 0),
(171, 525, 'Roj angelo 13 Charita', '2025-12-17 00:00:00', 19, '0000-00-00 00:00:00', 0),
(172, 526, 'Roj angelo 14 Charita', '2025-12-17 00:00:00', 20, '0000-00-00 00:00:00', 0),
(173, 921, 'albert ragol', '2025-12-18 00:00:00', 1, '0000-00-00 00:00:00', 0),
(192, 512, 'Roj angelo Charita', '2026-01-02 00:00:00', 1, '0000-00-00 00:00:00', 1),
(194, 921, 'albert ragol', '2026-01-02 00:00:00', 2, '0000-00-00 00:00:00', 0),
(196, 647, 'roshaine Charita', '2026-01-03 00:00:00', 1, '0000-00-00 00:00:00', 1),
(197, 512, 'Roj angelo Charita', '2026-01-03 00:00:00', 2, '0000-00-00 00:00:00', 0),
(200, 512, 'Roj angelo Charita', '2026-01-09 00:00:00', 1, '0000-00-00 00:00:00', 0),
(201, 512, 'Roj angelo Charita', '2026-01-10 00:00:00', 1, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `middleInitial` varchar(10) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `checkup` varchar(100) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `gender` enum('male','female','rather_not_say') DEFAULT NULL,
  `civilStatus` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `existingMedical` text DEFAULT NULL,
  `currentMedication` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `familyMedical` text DEFAULT NULL,
  `bloodPressure` varchar(20) DEFAULT NULL,
  `heartRate` varchar(20) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `guardian` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `guardianContact` varchar(15) DEFAULT NULL,
  `philhealth` tinyint(1) DEFAULT 0,
  `seniorCitizen` tinyint(1) DEFAULT 0,
  `emergency_contact_number` varchar(15) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `profile_edited` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `surname`, `middleInitial`, `firstName`, `birthday`, `password`, `email`, `contactNumber`, `age`, `address`, `description`, `checkup`, `admin`, `gender`, `civilStatus`, `occupation`, `existingMedical`, `currentMedication`, `allergies`, `familyMedical`, `bloodPressure`, `heartRate`, `temperature`, `height`, `weight`, `guardian`, `relationship`, `guardianContact`, `philhealth`, `seniorCitizen`, `emergency_contact_number`, `status`, `profile_edited`) VALUES
(180, 'weqrt', 'ewrty', 'rewytu', '2008-11-18', '$2y$10$mAZOmThnfQ0dFXUY3uSreOgYf6RDgzuoEnXO3Wr80VuH8mhfLdyTC', 'weqrw33@gmail.com', '+631234567898', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'pending', 0),
(337, 'hular', '', 'jin clarence', '2008-12-17', '$2y$10$OhEBVsYIqTJxTBb2ovQ8J.I13ts8kS46tHO59UzapjD4Ic73BkV4W', 'jin1232@gmail.com', '+639765578636', 20, 'wqerty', 'qwerty', 'Dr. Adelinno Labro', 0, 'male', 'Single', '', '', '', '', '', '120/80', '120', '36', '120', '80', '', '', '+63', 1, 0, '+63', 'approved', 0),
(512, 'Charita', 'Fornillos', 'Roj angelo', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'male', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'approved', 1),
(513, 'Charita', 'Fornillos', 'Roj angelo 1', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 1),
(514, 'Charita', 'Fornillos', 'Roj angelo 2', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'approved', 1),
(515, 'Charita', 'n/a', 'Roj angelo 3', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'crew worker', 'ergtdsd', 'eqwe', '', '', NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+639765578636', 'approved', 1),
(516, 'Charita', 'Fornillos', 'Roj angelo 4\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(517, 'Charita', 'Fornillos', 'Roj angelo 5\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(518, 'Charita', 'Fornillos', 'Roj angelo 6\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(519, 'Charita', 'Fornillos', 'Roj angelo 7\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(520, 'Charita', 'Fornillos', 'Roj angelo 8\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(521, 'Charita', 'Fornillos', 'Roj angelo 9\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(522, 'Charita', 'Fornillos', 'Roj angelo 10\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(523, 'Charita', 'Fornillos', 'Roj angelo 11\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(524, 'Charita', 'Fornillos', 'Roj angelo 12\r\n\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(525, 'Charita', 'Fornillos', 'Roj angelo 13\r\n\r\n\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(526, 'Charita', 'Fornillos', 'Roj angelo 14\r\n\r\n\r\n', '2004-07-08', '$2y$10$DSDoYPz6BLc7esA80ZzSJuUAQElialgyjZ7wVouc6yMK6XqazB6Z6', 'rcharita2@gmail.com', '+639765578636', 21, 'brgy banlic looc', NULL, NULL, 0, 'female', 'Single', 'None', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'roshaine angela ', 'grandmother', '+639765578636', 1, 1, '+63', 'pending', 0),
(541, 'estrope', 'p', 'anthoneth', '2008-12-18', '$2y$10$U1XhyHH8JM6VHWoXDKDbROTQAMdYDuGBvuhASXrnN45ygrMkNZupy', 'anthoneth@gmail.com', '+639765578612', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'pending', 0),
(647, 'Charita', 'fornillos', 'roshaine', '2013-02-22', '$2y$10$jZeAb5TkliwYGpTOz.a4s.99AXrtziRIH3Fa2xpqc7ZjzpfSC5vFy', 'rcharita2@gmail.com', '+639765578636', 0, '', NULL, NULL, 0, 'male', 'Single', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 0, 0, '', 'pending', 0),
(921, 'ragol', 'norca', 'albert', '2004-08-07', '$2y$10$U87rrgdc9f3likoINZlFa.no8Ghm.sZw4NErbJZ9i0A50nA/525dO', 'albert123@gmail.com', '+639765578636', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'pending', 0),
(975, 'bagasol', '', 'kit marjohn', '2008-12-18', '$2y$10$Rl9sdfvGCo.Svve4dVVXbOoKQksIvEEJGJw2V1tPdzYCGl1BQk.iO', 'kit@gmail.com', '+639765578636', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'pending', 0),
(999, 'Admin', 'admin', 'Admin', '2025-04-24', '$2y$10$q6lSh9pGE0MLyP6qOjej.efDqNLtjkp6IM1i1PiIS2UwzIczDrxwW', 'admin@gmail.com', '+630999999999', 20, 'BRGY. Looc Clinic', '', '', 1, 'male', 'Widowed', '', '', '', '', '', '', '', '', '', '', '', '', '+63', 0, 0, '+63', 'pending', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicine_logs`
--
ALTER TABLE `medicine_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_medical_logs`
--
ALTER TABLE `patient_medical_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `appointment_date` (`appointment_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `medicine_logs`
--
ALTER TABLE `medicine_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `patient_medical_logs`
--
ALTER TABLE `patient_medical_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `patient_medical_logs`
--
ALTER TABLE `patient_medical_logs`
  ADD CONSTRAINT `patient_medical_logs_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
