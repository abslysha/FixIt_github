-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 06:25 PM
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
-- Database: `fixit`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `name`, `email`, `password`) VALUES
('A001', 'alifah', 'alifah@gmail.com', '$2y$10$.n385UBtTIsVbj17tSReR.4Ab3YwSFNwSUEarmW3f0FC5JZGYS0eq');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `staffID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`staffID`, `name`, `email`, `password`) VALUES
('S001', 'amsyar', 'amsyar@gmail.com', '$2y$10$zfg6P/bWi52Zo19.6kJbNu6c4cLODwr7mgz2Qc1P55JoP9d0M/lHa'),
('S002', 'faziq', 'faziq@gmail.com', '$2y$10$gnmj7npphbXkFLgQfJPTQe4JwGZUuOx7YprkA.bxULXCQJEm4b0.2');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `reportID` varchar(100) NOT NULL,
  `adminID` varchar(50) DEFAULT NULL,
  `userID` varchar(50) NOT NULL,
  `staffID` varchar(50) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `DateReported` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','In Progress','Completed','Rejected') DEFAULT 'Pending',
  `location` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `proof_photo` varchar(255) DEFAULT NULL,
  `reject_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`reportID`, `adminID`, `userID`, `staffID`, `title`, `description`, `DateReported`, `status`, `location`, `attachment`, `proof_photo`, `reject_reason`) VALUES
('R001', NULL, 'U002', 'S002', 'broken window', 'window broken by stone', '2026-06-24 23:24:44', 'In Progress', 'lab Multimedia 2', 'uploads/report_6a3bf6bc6fed72.97295316.jpeg', NULL, NULL),
('R002', NULL, 'U002', 'S001', 'tile crack', 'The floor suddenly rose on its own.', '2026-06-24 23:26:13', 'In Progress', 'Database lab', 'uploads/report_6a3bf715d74755.00835822.jpeg', NULL, NULL),
('R003', NULL, 'U002', 'S002', 'power cable', 'power cable is broken', '2026-06-24 23:27:24', 'Completed', 'Software Lab', 'uploads/report_6a3bf75cc788c1.46349531.jpeg', 'uploads/proof_6a3bf8ea41aab2.58937011.jpeg', NULL),
('R004', NULL, 'U001', 'S002', 'Fan switch', 'speed selector is disconnected', '2026-06-24 23:28:36', 'Completed', 'Surau Lelaki', 'uploads/report_6a3bf7a4116935.10780209.jpeg', 'uploads/proof_6a3bf8b62d9ec5.30545186.jpeg', NULL),
('R005', NULL, 'U001', 'S001', 'Plug', 'No power plug', '2026-06-24 23:29:52', 'Completed', 'Common Room', 'uploads/report_6a3bf7f0961ef1.04014735.jpeg', 'uploads/proof_6a3bf9e595e886.40138184.jpg', NULL),
('R006', NULL, 'U001', NULL, 'Monitor', 'The monitor cannot be turned on.', '2026-06-24 23:30:41', 'Pending', 'Lab Game', 'uploads/report_6a3bf821d13612.19504516.jpeg', NULL, NULL),
('R007', NULL, 'U002', NULL, 'FAKE', 'THIS IS FAKE REPORT', '2026-06-24 23:36:29', 'Rejected', 'bilik ict', NULL, NULL, 'REPORT REJECTED');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `name`, `email`, `phone`, `password`) VALUES
('U001', 'irdina', 'iridina@staff.utem.edu.my', '01137948234', '$2y$10$5vBGwnYtQK9Jt.55FECdx.Yk1uzm0M76AaCAKpjkZogX0BUlaBNYq'),
('U002', 'abby', 'abby@student.utem.edu.my', '0197070987', '$2y$10$fMrOD1Zs06C/nD3/WQ5H5.2FPOeLL9OqR7C/vTEEN.1vXd2AekLJy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`staffID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`),
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `report_ibfk_3` FOREIGN KEY (`staffID`) REFERENCES `maintenance` (`staffID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
