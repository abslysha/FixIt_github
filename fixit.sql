-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 12:10 PM
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
('A001', 'alifah', 'alifah@gmail.com', '$2y$10$L8XIlzSKzt9QqGFhAQlcL./nEK7C4biV5m6rfcCn2mvRBNqzJtJOS');

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
('S001', 'faziq', 'faziq@gmail.com', '$2y$10$RSEKFav/4qe5t3lYWstcveslCcKumKwz1sY6kN/yIXEvyFgU/JEGy');

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
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `location` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`reportID`, `adminID`, `userID`, `staffID`, `title`, `description`, `DateReported`, `status`, `location`, `attachment`) VALUES
('R001', NULL, 'U002', 'S001', 'pc problem', 'bang pc ni tkle on', '2026-06-21 17:23:03', 'Pending', 'bilik dekan', 'uploads/report_6a37ad770b7d12.07346785.jpeg'),
('R002', NULL, 'U002', 'S001', 'plug rosak', 'rosak rosak', '2026-06-21 17:39:14', 'Completed', 'depan bilik ficts', 'uploads/report_6a37b142ae70f8.44003598.jpeg'),
('R003', NULL, 'U002', 'S001', 'cable rosak', 'cable disediakan rosak', '2026-06-21 17:41:49', 'In Progress', 'mm3', 'uploads/report_6a37b1dd01e264.47666149.jpeg'),
('R004', NULL, 'U002', NULL, 'wifi down', 'wifi couldnt connect', '2026-06-21 18:03:17', 'Pending', 'bk3', 'uploads/report_6a37b6e523dcd9.86847086.jpeg'),
('R005', NULL, 'U002', NULL, 'broken window', 'need to be fix asap', '2026-06-21 18:04:12', 'Pending', 'bk1', 'uploads/report_6a37b71c161609.63696102.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `name`, `email`, `password`) VALUES
('U001', 'amsyar', 'amsyar@gmail.com', '$2y$10$mWZPR3bFAeeYO98sBURTaeR7UGHxxQWeUtIrOqDC8zOWRoirMMjl6'),
('U002', 'abby', 'abby@gmail.com', '$2y$10$9nC7VrYcSI3wQbFPFLPz1un/6CwyWMqhe6OV1sEfqoDa2iNzej1.2');

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
