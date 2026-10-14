-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:5396
-- Generation Time: Oct 14, 2026 at 04:57 AM
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
-- Database: `groupupdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `accts`
--

CREATE TABLE `accts` (
  `AID` int(11) NOT NULL,
  `EMAIL` varchar(42) NOT NULL,
  `FULLNAME` varchar(50) NOT NULL,
  `USERNAME` varchar(30) NOT NULL,
  `PASS_HASH` varchar(255) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `memlist`
--

CREATE TABLE `memlist` (
  `GID` int(11) NOT NULL,
  `AID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studygroups`
--

CREATE TABLE `studygroups` (
  `GID` int(11) NOT NULL,
  `DESCRIPT` varchar(120) DEFAULT ' ',
  `LOCATION` varchar(60) NOT NULL,
  `DAYTME` datetime NOT NULL,
  `TAGS` varchar(120) DEFAULT ' '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accts`
--
ALTER TABLE `accts`
  ADD PRIMARY KEY (`AID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `memlist`
--
ALTER TABLE `memlist`
  ADD PRIMARY KEY (`GID`,`AID`),
  ADD KEY `AID` (`AID`);

--
-- Indexes for table `studygroups`
--
ALTER TABLE `studygroups`
  ADD PRIMARY KEY (`GID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accts`
--
ALTER TABLE `accts`
  MODIFY `AID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studygroups`
--
ALTER TABLE `studygroups`
  MODIFY `GID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `memlist`
--
ALTER TABLE `memlist`
  ADD CONSTRAINT `memlist_ibfk_1` FOREIGN KEY (`AID`) REFERENCES `accts` (`AID`),
  ADD CONSTRAINT `memlist_ibfk_2` FOREIGN KEY (`GID`) REFERENCES `studygroups` (`GID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
