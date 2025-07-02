-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 06:15 PM
-- Server version: 8.0.40
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `period` varchar(50) NOT NULL COMMENT 'Class period (e.g., P1, P2)',
  `subject` varchar(100) DEFAULT NULL COMMENT 'Subject name from timetable',
  `status` enum('Present','Absent','Late') DEFAULT 'Present',
  `device_id` varchar(50) DEFAULT NULL COMMENT 'ESP32 device ID',
  `sync_status` tinyint(1) DEFAULT '1' COMMENT '0=Offline, 1=Synced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`id`, `student_id`, `name`, `timestamp`, `period`, `subject`, `status`, `device_id`, `sync_status`) VALUES
(42, 1, 'Student One', '2025-05-19 16:01:06', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(43, 2, 'Student Two', '2025-05-19 16:01:21', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(44, 3, 'Student Three', '2025-05-19 16:01:33', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(45, 1, 'Student One', '2025-05-19 19:42:15', 'P5: 1:45-2:45', 'Java lab', 'Present', NULL, 1),
(46, 2, 'Student Two', '2025-05-19 19:42:21', 'P5: 1:45-2:45', 'ACD lab', 'Present', NULL, 1),
(47, 3, 'Student Three', '2025-05-19 19:42:29', 'P5: 1:45-2:45', 'ACD lab', 'Present', NULL, 1),
(48, 1, 'Student One', '2025-05-19 19:45:09', 'P2: 10-11', 'DBMS', 'Late', NULL, 1),
(49, 2, 'Student Two', '2025-05-19 19:45:13', 'P2: 10-11', 'DBMS', 'Late', NULL, 1),
(50, 3, 'Student Three', '2025-05-19 19:45:22', 'P2: 10-11', 'DBMS', 'Late', NULL, 1),
(51, 3, 'Student Three', '2025-05-28 09:00:00', 'P1: 9-10', 'Java', 'Late', NULL, 1),
(52, 2, 'Student Two', '2025-05-28 09:00:00', 'P1: 9-10', 'Java', 'Present', NULL, 1),
(53, 3, 'Student Three', '2025-05-28 10:00:00', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(54, 2, 'Student Two', '2025-05-28 10:00:00', 'P2: 10-11', 'MIV', 'Late', NULL, 1),
(55, 3, 'Student Three', '2025-05-28 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(56, 2, 'Student Two', '2025-05-28 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(57, 3, 'Student Three', '2025-05-28 12:45:00', 'P4: 12:45-1:45', 'MIV', 'Present', NULL, 1),
(58, 2, 'Student Two', '2025-05-28 12:45:00', 'P4: 12:45-1:45', 'MIV', 'Present', NULL, 1),
(59, 3, 'Student Three', '2025-05-28 13:45:00', 'P5: 1:45-2:45', 'MIV', 'Present', NULL, 1),
(60, 2, 'Student Two', '2025-05-28 13:45:00', 'P5: 1:45-2:45', 'MIV', 'Present', NULL, 1),
(61, 3, 'Student Three', '2025-05-28 15:00:00', 'P6: 3-4', 'Java', 'Present', NULL, 1),
(62, 2, 'Student Two', '2025-05-28 15:00:00', 'P6: 3-4', 'Java', 'Present', NULL, 1),
(63, 3, 'Student Three', '2025-05-28 16:00:00', 'P7: 4-5', 'Minor Degree', 'Present', NULL, 1),
(64, 2, 'Student Two', '2025-05-28 16:00:00', 'P7: 4-5', 'Minor Degree', 'Present', NULL, 1),
(66, 1, 'Student One', '2025-05-28 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(67, 1, 'Student One', '2025-05-28 10:00:00', 'P2: 10-11', 'DBMS', 'Present', NULL, 1),
(68, 1, 'Student One', '2025-05-28 11:00:00', 'P3: 11-12', 'COA', 'Present', NULL, 1),
(69, 1, 'Student One', '2025-05-28 12:45:00', 'P4: 12:45-1:45', 'Java lab', 'Present', NULL, 1),
(70, 1, 'Student One', '2025-05-28 13:45:00', 'P5: 1:45-2:45', 'Java lab', 'Late', NULL, 1),
(71, 1, 'Student One', '2025-05-28 15:00:00', 'P6: 3-4', 'MIV', 'Late', NULL, 1),
(72, 1, 'Student One', '2025-05-28 16:00:00', 'P7: 4-5', 'ECO', 'Absent', NULL, 1),
(73, 3, 'Student Three', '2025-05-01 09:00:00', 'P1: 9-10', 'ECO', 'Late', NULL, 1),
(74, 2, 'Student Two', '2025-05-01 09:00:00', 'P1: 9-10', 'ECO', 'Late', NULL, 1),
(75, 3, 'Student Three', '2025-05-01 10:00:00', 'P2: 10-11', 'Java', 'Late', NULL, 1),
(76, 2, 'Student Two', '2025-05-01 10:00:00', 'P2: 10-11', 'Java', 'Present', NULL, 1),
(77, 3, 'Student Three', '2025-05-01 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(78, 2, 'Student Two', '2025-05-01 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(79, 3, 'Student Three', '2025-05-01 12:45:00', 'P4: 12:45-1:45', 'COA', 'Late', NULL, 1),
(80, 2, 'Student Two', '2025-05-01 12:45:00', 'P4: 12:45-1:45', 'COA', 'Present', NULL, 1),
(81, 3, 'Student Three', '2025-05-01 13:45:00', 'P5: 1:45-2:45', 'ACD', 'Present', NULL, 1),
(82, 2, 'Student Two', '2025-05-01 13:45:00', 'P5: 1:45-2:45', 'ACD', 'Present', NULL, 1),
(83, 3, 'Student Three', '2025-05-01 15:00:00', 'P6: 3-4', 'Minor Degree Lab', 'Present', NULL, 1),
(84, 2, 'Student Two', '2025-05-01 15:00:00', 'P6: 3-4', 'Minor Degree Lab', 'Present', NULL, 1),
(85, 3, 'Student Three', '2025-05-01 16:00:00', 'P7: 4-5', 'Minor Degree Lab', 'Present', NULL, 1),
(86, 2, 'Student Two', '2025-05-01 16:00:00', 'P7: 4-5', 'Minor Degree Lab', 'Late', NULL, 1),
(88, 1, 'Student One', '2025-05-01 09:00:00', 'P1: 9-10', 'ECO', 'Present', NULL, 1),
(89, 1, 'Student One', '2025-05-01 10:00:00', 'P2: 10-11', 'Java', 'Late', NULL, 1),
(90, 1, 'Student One', '2025-05-01 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(91, 1, 'Student One', '2025-05-01 12:45:00', 'P4: 12:45-1:45', 'COA', 'Present', NULL, 1),
(92, 1, 'Student One', '2025-05-01 13:45:00', 'P5: 1:45-2:45', 'ACD', 'Present', NULL, 1),
(93, 1, 'Student One', '2025-05-01 15:00:00', 'P6: 3-4', 'Minor Degree Lab', 'Present', NULL, 1),
(94, 1, 'Student One', '2025-05-01 16:00:00', 'P7: 4-5', 'Minor Degree Lab', 'Present', NULL, 1),
(95, 3, 'Student Three', '2025-05-02 09:00:00', 'P1: 9-10', 'ACD', 'Present', NULL, 1),
(96, 2, 'Student Two', '2025-05-02 09:00:00', 'P1: 9-10', 'ACD', 'Present', NULL, 1),
(97, 3, 'Student Three', '2025-05-02 10:00:00', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(98, 2, 'Student Two', '2025-05-02 10:00:00', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(99, 3, 'Student Three', '2025-05-02 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(100, 2, 'Student Two', '2025-05-02 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(101, 3, 'Student Three', '2025-05-02 12:45:00', 'P4: 12:45-1:45', 'ECO', 'Present', NULL, 1),
(102, 2, 'Student Two', '2025-05-02 12:45:00', 'P4: 12:45-1:45', 'ECO', 'Present', NULL, 1),
(103, 3, 'Student Three', '2025-05-02 13:45:00', 'P5: 1:45-2:45', 'Java', 'Late', NULL, 1),
(104, 2, 'Student Two', '2025-05-02 13:45:00', 'P5: 1:45-2:45', 'Java', 'Present', NULL, 1),
(105, 3, 'Student Three', '2025-05-02 15:00:00', 'P6: 3-4', 'ACD lab', 'Present', NULL, 1),
(106, 2, 'Student Two', '2025-05-02 15:00:00', 'P6: 3-4', 'ACD lab', 'Present', NULL, 1),
(107, 3, 'Student Three', '2025-05-02 16:00:00', 'P7: 4-5', 'ACD lab', 'Present', NULL, 1),
(108, 2, 'Student Two', '2025-05-02 16:00:00', 'P7: 4-5', 'ACD lab', 'Present', NULL, 1),
(110, 1, 'Student One', '2025-05-02 09:00:00', 'P1: 9-10', 'ACD', 'Late', NULL, 1),
(111, 1, 'Student One', '2025-05-02 10:00:00', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(112, 1, 'Student One', '2025-05-02 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(113, 1, 'Student One', '2025-05-02 12:45:00', 'P4: 12:45-1:45', 'ECO', 'Late', NULL, 1),
(114, 1, 'Student One', '2025-05-02 13:45:00', 'P5: 1:45-2:45', 'Java', 'Absent', NULL, 1),
(115, 1, 'Student One', '2025-05-02 15:00:00', 'P6: 3-4', 'DBMS lab', 'Present', NULL, 1),
(116, 1, 'Student One', '2025-05-02 16:00:00', 'P7: 4-5', 'DBMS lab', 'Present', NULL, 1),
(117, 3, 'Student Three', '2025-05-05 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(118, 2, 'Student Two', '2025-05-05 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(119, 3, 'Student Three', '2025-05-05 10:00:00', 'P2: 10-11', 'DBMS', 'Present', NULL, 1),
(120, 2, 'Student Two', '2025-05-05 10:00:00', 'P2: 10-11', 'DBMS', 'Present', NULL, 1),
(121, 3, 'Student Three', '2025-05-05 11:00:00', 'P3: 11-12', 'COA', 'Present', NULL, 1),
(122, 2, 'Student Two', '2025-05-05 11:00:00', 'P3: 11-12', 'COA', 'Present', NULL, 1),
(123, 3, 'Student Three', '2025-05-05 12:45:00', 'P4: 12:45-1:45', 'ACD lab', 'Present', NULL, 1),
(124, 2, 'Student Two', '2025-05-05 12:45:00', 'P4: 12:45-1:45', 'ACD lab', 'Present', NULL, 1),
(125, 3, 'Student Three', '2025-05-05 13:45:00', 'P5: 1:45-2:45', 'ACD lab', 'Late', NULL, 1),
(126, 2, 'Student Two', '2025-05-05 13:45:00', 'P5: 1:45-2:45', 'ACD lab', 'Present', NULL, 1),
(127, 3, 'Student Three', '2025-05-05 15:00:00', 'P6: 3-4', 'MIV', 'Present', NULL, 1),
(128, 2, 'Student Two', '2025-05-05 15:00:00', 'P6: 3-4', 'MIV', 'Present', NULL, 1),
(129, 3, 'Student Three', '2025-05-05 16:00:00', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(130, 2, 'Student Two', '2025-05-05 16:00:00', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(132, 1, 'Student One', '2025-05-05 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(133, 1, 'Student One', '2025-05-05 10:00:00', 'P2: 10-11', 'DBMS', 'Present', NULL, 1),
(134, 1, 'Student One', '2025-05-05 11:00:00', 'P3: 11-12', 'COA', 'Absent', NULL, 1),
(135, 1, 'Student One', '2025-05-05 12:45:00', 'P4: 12:45-1:45', 'ACD lab', 'Present', NULL, 1),
(136, 1, 'Student One', '2025-05-05 13:45:00', 'P5: 1:45-2:45', 'ACD lab', 'Present', NULL, 1),
(137, 1, 'Student One', '2025-05-05 15:00:00', 'P6: 3-4', 'MIV', 'Present', NULL, 1),
(138, 1, 'Student One', '2025-05-05 16:00:00', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(139, 3, 'Student Three', '2025-05-06 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(140, 2, 'Student Two', '2025-05-06 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(141, 3, 'Student Three', '2025-05-06 10:00:00', 'P2: 10-11', 'COA', 'Present', NULL, 1),
(142, 2, 'Student Two', '2025-05-06 10:00:00', 'P2: 10-11', 'COA', 'Present', NULL, 1),
(143, 3, 'Student Three', '2025-05-06 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(144, 2, 'Student Two', '2025-05-06 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(145, 3, 'Student Three', '2025-05-06 12:45:00', 'P4: 12:45-1:45', 'DBMS lab', 'Present', NULL, 1),
(146, 2, 'Student Two', '2025-05-06 12:45:00', 'P4: 12:45-1:45', 'DBMS lab', 'Present', NULL, 1),
(147, 3, 'Student Three', '2025-05-06 13:45:00', 'P5: 1:45-2:45', 'DBMS lab', 'Present', NULL, 1),
(148, 2, 'Student Two', '2025-05-06 13:45:00', 'P5: 1:45-2:45', 'DBMS lab', 'Present', NULL, 1),
(149, 3, 'Student Three', '2025-05-06 15:00:00', 'P6: 3-4', 'COA', 'Present', NULL, 1),
(150, 2, 'Student Two', '2025-05-06 15:00:00', 'P6: 3-4', 'COA', 'Present', NULL, 1),
(151, 3, 'Student Three', '2025-05-06 16:00:00', 'P7: 4-5', 'Sports', 'Present', NULL, 1),
(152, 2, 'Student Two', '2025-05-06 16:00:00', 'P7: 4-5', 'Sports', 'Present', NULL, 1),
(154, 1, 'Student One', '2025-05-06 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(155, 1, 'Student One', '2025-05-06 10:00:00', 'P2: 10-11', 'COA', 'Absent', NULL, 1),
(156, 1, 'Student One', '2025-05-06 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(157, 1, 'Student One', '2025-05-06 12:45:00', 'P4: 12:45-1:45', 'Java lab', 'Present', NULL, 1),
(158, 1, 'Student One', '2025-05-06 13:45:00', 'P5: 1:45-2:45', 'Java lab', 'Present', NULL, 1),
(159, 1, 'Student One', '2025-05-06 15:00:00', 'P6: 3-4', 'COA', 'Present', NULL, 1),
(160, 1, 'Student One', '2025-05-06 16:00:00', 'P7: 4-5', 'Sports', 'Present', NULL, 1),
(161, 3, 'Student Three', '2025-05-07 09:00:00', 'P1: 9-10', 'Java', 'Late', NULL, 1),
(162, 2, 'Student Two', '2025-05-07 09:00:00', 'P1: 9-10', 'Java', 'Present', NULL, 1),
(163, 3, 'Student Three', '2025-05-07 10:00:00', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(164, 2, 'Student Two', '2025-05-07 10:00:00', 'P2: 10-11', 'MIV', 'Late', NULL, 1),
(165, 3, 'Student Three', '2025-05-07 11:00:00', 'P3: 11-12', 'ACD', 'Present', NULL, 1),
(166, 2, 'Student Two', '2025-05-07 11:00:00', 'P3: 11-12', 'ACD', 'Late', NULL, 1),
(167, 3, 'Student Three', '2025-05-07 12:45:00', 'P4: 12:45-1:45', 'MIV', 'Present', NULL, 1),
(168, 2, 'Student Two', '2025-05-07 12:45:00', 'P4: 12:45-1:45', 'MIV', 'Present', NULL, 1),
(169, 3, 'Student Three', '2025-05-07 13:45:00', 'P5: 1:45-2:45', 'MIV', 'Present', NULL, 1),
(170, 2, 'Student Two', '2025-05-07 13:45:00', 'P5: 1:45-2:45', 'MIV', 'Present', NULL, 1),
(171, 3, 'Student Three', '2025-05-07 15:00:00', 'P6: 3-4', 'Java', 'Present', NULL, 1),
(172, 2, 'Student Two', '2025-05-07 15:00:00', 'P6: 3-4', 'Java', 'Present', NULL, 1),
(173, 3, 'Student Three', '2025-05-07 16:00:00', 'P7: 4-5', 'Minor Degree', 'Present', NULL, 1),
(174, 2, 'Student Two', '2025-05-07 16:00:00', 'P7: 4-5', 'Minor Degree', 'Present', NULL, 1),
(176, 1, 'Student One', '2025-05-07 09:00:00', 'P1: 9-10', 'Minor Degree', 'Present', NULL, 1),
(177, 1, 'Student One', '2025-05-07 10:00:00', 'P2: 10-11', 'DBMS', 'Late', NULL, 1),
(178, 1, 'Student One', '2025-05-07 11:00:00', 'P3: 11-12', 'COA', 'Late', NULL, 1),
(179, 1, 'Student One', '2025-05-07 12:45:00', 'P4: 12:45-1:45', 'Java lab', 'Late', NULL, 1),
(180, 1, 'Student One', '2025-05-07 13:45:00', 'P5: 1:45-2:45', 'Java lab', 'Late', NULL, 1),
(181, 1, 'Student One', '2025-05-07 15:00:00', 'P6: 3-4', 'MIV', 'Present', NULL, 1),
(182, 1, 'Student One', '2025-05-07 16:00:00', 'P7: 4-5', 'ECO', 'Present', NULL, 1),
(183, 3, 'Student Three', '2025-05-08 09:00:00', 'P1: 9-10', 'ECO', 'Present', NULL, 1),
(184, 2, 'Student Two', '2025-05-08 09:00:00', 'P1: 9-10', 'ECO', 'Present', NULL, 1),
(185, 3, 'Student Three', '2025-05-08 10:00:00', 'P2: 10-11', 'Java', 'Present', NULL, 1),
(186, 2, 'Student Two', '2025-05-08 10:00:00', 'P2: 10-11', 'Java', 'Present', NULL, 1),
(187, 3, 'Student Three', '2025-05-08 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(188, 2, 'Student Two', '2025-05-08 11:00:00', 'P3: 11-12', 'DBMS', 'Present', NULL, 1),
(189, 3, 'Student Three', '2025-05-08 12:45:00', 'P4: 12:45-1:45', 'COA', 'Late', NULL, 1),
(190, 2, 'Student Two', '2025-05-08 12:45:00', 'P4: 12:45-1:45', 'COA', 'Present', NULL, 1),
(191, 3, 'Student Three', '2025-05-08 13:45:00', 'P5: 1:45-2:45', 'ACD', 'Late', NULL, 1),
(192, 2, 'Student Two', '2025-05-08 13:45:00', 'P5: 1:45-2:45', 'ACD', 'Present', NULL, 1),
(193, 3, 'Student Three', '2025-05-08 15:00:00', 'P6: 3-4', 'Minor Degree Lab', 'Late', NULL, 1),
(194, 2, 'Student Two', '2025-05-08 15:00:00', 'P6: 3-4', 'Minor Degree Lab', 'Present', NULL, 1),
(195, 3, 'Student Three', '2025-05-08 16:00:00', 'P7: 4-5', 'Minor Degree Lab', 'Present', NULL, 1),
(196, 2, 'Student Two', '2025-05-08 16:00:00', 'P7: 4-5', 'Minor Degree Lab', 'Present', NULL, 1),
(198, 3, 'Student Three', '2025-05-20 21:14:07', 'P2: 10-11', 'COA', 'Late', NULL, 1),
(199, 1, 'Student One', '2025-05-20 21:16:03', 'P2: 10-11', 'COA', 'Late', NULL, 1),
(200, 1, 'Student One', '2025-05-20 22:38:02', 'P3: 11-12', 'DBMS', 'Late', NULL, 1),
(201, 1, 'Student One', '2025-05-20 22:41:16', 'P4: 12:45-1:45', 'DBMS lab', 'Late', NULL, 1),
(202, 1, 'Student One', '2025-05-20 22:41:52', 'P5: 1:45-2:45', 'DBMS lab', 'Late', NULL, 1),
(203, 3, 'Student Three', '2025-05-20 22:43:05', 'P5: 1:45-2:45', 'Java lab', 'Late', NULL, 1),
(204, 2, 'Student Two', '2025-05-20 22:43:09', 'P5: 1:45-2:45', 'Java lab', 'Late', NULL, 1),
(205, 1, 'Student One', '2025-05-20 22:55:32', 'P1: 9-10', 'Minor Degree', 'Late', NULL, 1),
(206, 1, 'Student One', '2025-05-20 22:57:00', 'P6: 3-4', 'COA', 'Late', NULL, 1),
(207, 2, 'Student Two', '2025-05-20 23:18:16', 'P4: 12:45-1:45', 'Java lab', 'Absent', NULL, 1),
(208, 3, 'Student Three', '2025-05-20 23:18:16', 'P4: 12:45-1:45', 'Java lab', 'Absent', NULL, 1),
(214, 1, 'Student One', '2025-05-21 09:40:47', 'P1: 9-10', 'Java', 'Late', NULL, 1),
(215, 2, 'Student Two', '2025-05-21 09:40:51', 'P1: 9-10', 'Java', 'Late', NULL, 1),
(216, 3, 'Student Three', '2025-05-21 09:45:55', 'P1: 9-10', 'Java', 'Late', NULL, 1),
(223, 2, 'Student Two', '2025-05-21 10:07:06', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(224, 1, 'Student One', '2025-05-21 10:07:14', 'P2: 10-11', 'MIV', 'Present', NULL, 1),
(230, 3, 'Student Three', '2025-05-21 22:49:04', 'P2: 10-11', 'MIV', 'Absent', NULL, 1),
(231, 2, 'Student Two', '2025-05-21 22:49:04', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(232, 3, 'Student Three', '2025-05-21 22:49:04', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(233, 2, 'Student Two', '2025-05-21 22:49:04', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(234, 3, 'Student Three', '2025-05-21 22:49:04', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(235, 2, 'Student Two', '2025-05-21 22:49:04', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(236, 3, 'Student Three', '2025-05-21 22:49:04', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(237, 2, 'Student Two', '2025-05-21 22:49:04', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(238, 3, 'Student Three', '2025-05-21 22:49:04', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(239, 2, 'Student Two', '2025-05-21 22:49:04', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1),
(240, 3, 'Student Three', '2025-05-21 22:49:04', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1),
(241, 9, 'Student Four', '2025-05-21 22:49:04', 'P1: 9-10', 'Java', 'Absent', NULL, 1),
(242, 9, 'Student Four', '2025-05-21 22:49:04', 'P2: 10-11', 'MIV', 'Absent', NULL, 1),
(243, 9, 'Student Four', '2025-05-21 22:49:04', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(244, 1, 'Student One', '2025-05-21 22:49:04', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(245, 9, 'Student Four', '2025-05-21 22:49:04', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(246, 1, 'Student One', '2025-05-21 22:49:04', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(247, 9, 'Student Four', '2025-05-21 22:49:04', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(248, 1, 'Student One', '2025-05-21 22:49:04', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(249, 9, 'Student Four', '2025-05-21 22:49:04', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(250, 1, 'Student One', '2025-05-21 22:49:04', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(251, 9, 'Student Four', '2025-05-21 22:49:04', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1),
(252, 1, 'Student One', '2025-05-21 22:49:04', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1),
(253, 2, 'Student Two', '2025-05-22 17:00:02', 'P1: 9-10', 'ECO', 'Absent', NULL, 1),
(254, 3, 'Student Three', '2025-05-22 17:00:02', 'P1: 9-10', 'ECO', 'Absent', NULL, 1),
(255, 2, 'Student Two', '2025-05-22 17:00:02', 'P2: 10-11', 'Java', 'Absent', NULL, 1),
(256, 3, 'Student Three', '2025-05-22 17:00:02', 'P2: 10-11', 'Java', 'Absent', NULL, 1),
(257, 2, 'Student Two', '2025-05-22 17:00:03', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(258, 3, 'Student Three', '2025-05-22 17:00:03', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(259, 2, 'Student Two', '2025-05-22 17:00:03', 'P4: 12:45-1:45', 'COA', 'Absent', NULL, 1),
(260, 3, 'Student Three', '2025-05-22 17:00:03', 'P4: 12:45-1:45', 'COA', 'Absent', NULL, 1),
(261, 2, 'Student Two', '2025-05-22 17:00:03', 'P5: 1:45-2:45', 'ACD', 'Absent', NULL, 1),
(262, 3, 'Student Three', '2025-05-22 17:00:03', 'P5: 1:45-2:45', 'ACD', 'Absent', NULL, 1),
(263, 2, 'Student Two', '2025-05-22 17:00:03', 'P6: 3-4', 'Minor Degree Lab', 'Absent', NULL, 1),
(264, 3, 'Student Three', '2025-05-22 17:00:03', 'P6: 3-4', 'Minor Degree Lab', 'Absent', NULL, 1),
(265, 2, 'Student Two', '2025-05-22 17:00:03', 'P7: 4-5', 'Minor Degree Lab', 'Absent', NULL, 1),
(266, 3, 'Student Three', '2025-05-22 17:00:03', 'P7: 4-5', 'Minor Degree Lab', 'Absent', NULL, 1),
(267, 9, 'Student Four', '2025-05-22 17:00:03', 'P1: 9-10', 'ECO', 'Absent', NULL, 1),
(268, 1, 'Student One', '2025-05-22 17:00:03', 'P1: 9-10', 'ECO', 'Absent', NULL, 1),
(269, 9, 'Student Four', '2025-05-22 17:00:03', 'P2: 10-11', 'Java', 'Absent', NULL, 1),
(270, 1, 'Student One', '2025-05-22 17:00:03', 'P2: 10-11', 'Java', 'Absent', NULL, 1),
(271, 9, 'Student Four', '2025-05-22 17:00:03', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(272, 1, 'Student One', '2025-05-22 17:00:03', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(273, 9, 'Student Four', '2025-05-22 17:00:03', 'P4: 12:45-1:45', 'COA', 'Absent', NULL, 1),
(274, 1, 'Student One', '2025-05-22 17:00:03', 'P4: 12:45-1:45', 'COA', 'Absent', NULL, 1),
(275, 9, 'Student Four', '2025-05-22 17:00:03', 'P5: 1:45-2:45', 'ACD', 'Absent', NULL, 1),
(276, 1, 'Student One', '2025-05-22 17:00:03', 'P5: 1:45-2:45', 'ACD', 'Absent', NULL, 1),
(277, 9, 'Student Four', '2025-05-22 17:00:03', 'P6: 3-4', 'Minor Degree Lab', 'Absent', NULL, 1),
(278, 1, 'Student One', '2025-05-22 17:00:03', 'P6: 3-4', 'Minor Degree Lab', 'Absent', NULL, 1),
(279, 9, 'Student Four', '2025-05-22 17:00:03', 'P7: 4-5', 'Minor Degree Lab', 'Absent', NULL, 1),
(280, 1, 'Student One', '2025-05-22 17:00:03', 'P7: 4-5', 'Minor Degree Lab', 'Absent', NULL, 1),
(281, 2, 'Student Two', '2025-05-27 18:15:07', 'P1: 9-10', 'Minor Degree', 'Absent', NULL, 1),
(282, 3, 'Student Three', '2025-05-27 18:15:07', 'P1: 9-10', 'Minor Degree', 'Absent', NULL, 1),
(283, 2, 'Student Two', '2025-05-27 18:15:07', 'P2: 10-11', 'COA', 'Absent', NULL, 1),
(284, 3, 'Student Three', '2025-05-27 18:15:07', 'P2: 10-11', 'COA', 'Absent', NULL, 1),
(285, 2, 'Student Two', '2025-05-27 18:15:07', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(286, 3, 'Student Three', '2025-05-27 18:15:07', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(287, 2, 'Student Two', '2025-05-27 18:15:07', 'P4: 12:45-1:45', 'Java lab', 'Absent', NULL, 1),
(288, 3, 'Student Three', '2025-05-27 18:15:07', 'P4: 12:45-1:45', 'Java lab', 'Absent', NULL, 1),
(289, 2, 'Student Two', '2025-05-27 18:15:08', 'P5: 1:45-2:45', 'Java lab', 'Absent', NULL, 1),
(290, 3, 'Student Three', '2025-05-27 18:15:08', 'P5: 1:45-2:45', 'Java lab', 'Absent', NULL, 1),
(291, 2, 'Student Two', '2025-05-27 18:15:08', 'P6: 3-4', 'COA', 'Absent', NULL, 1),
(292, 3, 'Student Three', '2025-05-27 18:15:08', 'P6: 3-4', 'COA', 'Absent', NULL, 1),
(293, 2, 'Student Two', '2025-05-27 18:15:08', 'P7: 4-5', 'Sports', 'Absent', NULL, 1),
(294, 3, 'Student Three', '2025-05-27 18:15:08', 'P7: 4-5', 'Sports', 'Absent', NULL, 1),
(295, 9, 'Student Four', '2025-05-27 18:15:08', 'P1: 9-10', 'Minor Degree', 'Absent', NULL, 1),
(296, 1, 'Student One', '2025-05-27 18:15:08', 'P1: 9-10', 'Minor Degree', 'Absent', NULL, 1),
(297, 9, 'Student Four', '2025-05-27 18:15:08', 'P2: 10-11', 'COA', 'Absent', NULL, 1),
(298, 1, 'Student One', '2025-05-27 18:15:08', 'P2: 10-11', 'COA', 'Absent', NULL, 1),
(299, 9, 'Student Four', '2025-05-27 18:15:08', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(300, 1, 'Student One', '2025-05-27 18:15:08', 'P3: 11-12', 'DBMS', 'Absent', NULL, 1),
(301, 9, 'Student Four', '2025-05-27 18:15:08', 'P4: 12:45-1:45', 'DBMS lab', 'Absent', NULL, 1),
(302, 1, 'Student One', '2025-05-27 18:15:08', 'P4: 12:45-1:45', 'DBMS lab', 'Absent', NULL, 1),
(303, 9, 'Student Four', '2025-05-27 18:15:08', 'P5: 1:45-2:45', 'DBMS lab', 'Absent', NULL, 1),
(304, 1, 'Student One', '2025-05-27 18:15:08', 'P5: 1:45-2:45', 'DBMS lab', 'Absent', NULL, 1),
(305, 9, 'Student Four', '2025-05-27 18:15:08', 'P6: 3-4', 'COA', 'Absent', NULL, 1),
(306, 1, 'Student One', '2025-05-27 18:15:08', 'P6: 3-4', 'COA', 'Absent', NULL, 1),
(307, 9, 'Student Four', '2025-05-27 18:15:08', 'P7: 4-5', 'Sports', 'Absent', NULL, 1),
(308, 1, 'Student One', '2025-05-27 18:15:08', 'P7: 4-5', 'Sports', 'Absent', NULL, 1),
(309, 9, 'Student Four', '2025-05-28 17:52:23', 'P1: 9-10', 'Java', 'Absent', NULL, 1),
(310, 1, 'Student One', '2025-05-28 17:52:23', 'P1: 9-10', 'Java', 'Absent', NULL, 1),
(311, 9, 'Student Four', '2025-05-28 17:52:23', 'P2: 10-11', 'MIV', 'Absent', NULL, 1),
(312, 1, 'Student One', '2025-05-28 17:52:23', 'P2: 10-11', 'MIV', 'Absent', NULL, 1),
(313, 9, 'Student Four', '2025-05-28 17:52:23', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(314, 1, 'Student One', '2025-05-28 17:52:23', 'P3: 11-12', 'ACD', 'Absent', NULL, 1),
(315, 9, 'Student Four', '2025-05-28 17:52:23', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(316, 1, 'Student One', '2025-05-28 17:52:23', 'P4: 12:45-1:45', 'MIV', 'Absent', NULL, 1),
(317, 9, 'Student Four', '2025-05-28 17:52:23', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(318, 1, 'Student One', '2025-05-28 17:52:23', 'P5: 1:45-2:45', 'MIV', 'Absent', NULL, 1),
(319, 9, 'Student Four', '2025-05-28 17:52:23', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(320, 1, 'Student One', '2025-05-28 17:52:23', 'P6: 3-4', 'Java', 'Absent', NULL, 1),
(321, 9, 'Student Four', '2025-05-28 17:52:23', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1),
(322, 1, 'Student One', '2025-05-28 17:52:23', 'P7: 4-5', 'Minor Degree', 'Absent', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `e1_timetable`
--

CREATE TABLE `e1_timetable` (
  `id` int NOT NULL,
  `day` varchar(10) NOT NULL,
  `period_1` varchar(50) NOT NULL COMMENT '9:00-10:00',
  `period_2` varchar(50) NOT NULL COMMENT '10:00-11:00',
  `period_3` varchar(50) NOT NULL COMMENT '11:00-12:00',
  `period_4` varchar(50) NOT NULL COMMENT '12:45-2:45 (First Half)',
  `period_5` varchar(50) NOT NULL COMMENT '12:45-2:45 (Second Half)',
  `period_6` varchar(50) NOT NULL COMMENT '3:00-4:00',
  `period_7` varchar(50) NOT NULL COMMENT '4:00-5:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `e1_timetable`
--

INSERT INTO `e1_timetable` (`id`, `day`, `period_1`, `period_2`, `period_3`, `period_4`, `period_5`, `period_6`, `period_7`) VALUES
(1, 'Mon', 'Minor Degree', 'DBMS', 'COA', 'ACD lab', 'ACD lab', 'MIV', 'ECO'),
(2, 'Tue', 'Minor Degree', 'COA', 'DBMS', 'Java lab', 'Java lab', 'COA', 'Sports'),
(3, 'Wed', 'Java', 'MIV', 'ACD', 'MIV', 'MIV', 'Java', 'Minor Degree'),
(4, 'Thu', 'ECO', 'Java', 'DBMS', 'COA', 'ACD', 'Minor Degree Lab', 'Minor Degree Lab'),
(5, 'Fri', 'ACD', 'MIV', 'ACD', 'ECO', 'Java', 'DBMS lab', 'DBMS lab');

-- --------------------------------------------------------

--
-- Table structure for table `e2_timetable`
--

CREATE TABLE `e2_timetable` (
  `id` int NOT NULL,
  `day` varchar(10) NOT NULL,
  `period_1` varchar(50) NOT NULL COMMENT '9:00-10:00',
  `period_2` varchar(50) NOT NULL COMMENT '10:00-11:00',
  `period_3` varchar(50) NOT NULL COMMENT '11:00-12:00',
  `period_4` varchar(50) NOT NULL COMMENT '12:45-2:45 (First Half)',
  `period_5` varchar(50) NOT NULL COMMENT '12:45-2:45 (Second Half)',
  `period_6` varchar(50) NOT NULL COMMENT '3:00-4:00',
  `period_7` varchar(50) NOT NULL COMMENT '4:00-5:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `e2_timetable`
--

INSERT INTO `e2_timetable` (`id`, `day`, `period_1`, `period_2`, `period_3`, `period_4`, `period_5`, `period_6`, `period_7`) VALUES
(1, 'Mon', 'Minor Degree', 'DBMS', 'COA', 'Java lab', 'Java lab', 'MIV', 'ECO'),
(2, 'Tue', 'Minor Degree', 'COA', 'DBMS', 'DBMS lab', 'DBMS lab', 'COA', 'Sports'),
(3, 'Wed', 'Java', 'MIV', 'ACD', 'MIV', 'MIV', 'Java', 'Minor Degree'),
(4, 'Thu', 'ECO', 'Java', 'DBMS', 'COA', 'ACD', 'Minor Degree Lab', 'Minor Degree Lab'),
(5, 'Fri', 'ACD', 'MIV', 'ACD', 'ECO', 'Java', 'ACD lab', 'ACD lab');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `fingerprint_id1` int NOT NULL,
  `fingerprint_id2` int NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `batch` varchar(10) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL
);

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `roll_number`, `fingerprint_id1`, `fingerprint_id2`, `email`, `phone`, `batch`, `class`) VALUES
(1, 'Student One', '23EC001', 1, 2, 'student1@example.com', '9000000001', 'E2', 'ECOMP'),
(2, 'Student Two', '23EC002', 3, 4, 'student2@example.com', '9000000002', 'E1', 'ECOMP'),
(3, 'Student Three', '23EC003', 5, 6, 'student3@example.com', '9000000003', 'E1', 'ECOMP'),
(9, 'Student Four', '23EC004', 7, 8, 'student4@example.com', '9000000004', 'E2', 'ECOMP');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `period_status` (`period`,`status`);

--
-- Indexes for table `e1_timetable`
--
ALTER TABLE `e1_timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e2_timetable`
--
ALTER TABLE `e2_timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint_id1` (`fingerprint_id1`),
  ADD UNIQUE KEY `fingerprint_id2` (`fingerprint_id2`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;

--
-- AUTO_INCREMENT for table `e1_timetable`
--
ALTER TABLE `e1_timetable`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `e2_timetable`
--
ALTER TABLE `e2_timetable`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;