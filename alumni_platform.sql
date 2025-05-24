-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 10:23 AM
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
-- Database: `alumni_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `batch` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `poster` mediumblob DEFAULT NULL,
  `created_by` varchar(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_attendance`
--

CREATE TABLE `alumni_attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `alumni_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `people_count` int(11) NOT NULL,
  `stay_days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `success_stories`
--

CREATE TABLE `success_stories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` varchar(11) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `success_stories`
--

INSERT INTO `success_stories` (`id`, `title`, `content`, `author_id`, `department`, `created_at`, `status`) VALUES
(1, 'Job placement ', 'I have placed in Sankey Solutions which is a service based it company in thane ', '21UGCS20753', 'CSE', '2025-05-10 04:12:52', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `training_and_placement`
--

CREATE TABLE `training_and_placement` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `posted_by` varchar(11) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_and_placement`
--

INSERT INTO `training_and_placement` (`id`, `title`, `description`, `requirements`, `posted_by`, `department`, `created_at`, `approved`) VALUES
(1, 'Software Developer', 'asjfckasbjkdb', 'html,css,php', '21UGCS20753', 'CSE', '2025-03-22 00:15:45', 0),
(2, 'Salesforce developer', 'Salesforce developer ', 'freshher, logic building, sql,aptitude,', '21UGCS20753', 'CSE', '2025-05-09 13:54:39', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `prn_no` varchar(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','coordinator','admin') DEFAULT 'user',
  `graduation_year` int(11) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `work_info` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `job_profile` varchar(100) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `ug_degree` varchar(100) DEFAULT NULL,
  `ug_institute` varchar(255) DEFAULT NULL,
  `ug_graduation_year` int(11) DEFAULT NULL,
  `pg_degree` varchar(100) DEFAULT NULL,
  `pg_institute` varchar(255) DEFAULT NULL,
  `pg_graduation_year` int(11) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `status` enum('pending','verified') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`prn_no`, `name`, `email`, `password`, `role`, `graduation_year`, `degree`, `work_info`, `bio`, `job_profile`, `department`, `created_at`, `profile_picture`, `linkedin`, `company`, `position`, `first_name`, `middle_name`, `last_name`, `dob`, `gender`, `address`, `phone`, `github`, `ug_degree`, `ug_institute`, `ug_graduation_year`, `pg_degree`, `pg_institute`, `pg_graduation_year`, `skills`, `emergency_contact`, `experience`, `status`) VALUES
('21UDCV20753', 'Pratik Viswas Patil', 'mshikalgar246@gmail.com', NULL, 'user', NULL, NULL, NULL, NULL, 'Government', 'Civil', '2025-05-10 15:28:36', 'uploads/681f70a4d5c97.jpg', '', 'Tester', 'Test', NULL, NULL, NULL, '2025-05-14', 'Male', 'kldclksdmckl', '9146454748', '', 'BTech', 'TKIET Warananagar', 2025, '', '', 0, 'test', 'null', 1, 'pending'),
('21UGCS20646', 'Bhushan Ramkrishn Patil', 'pbhushan0603@gmail.com', '$2y$10$350chWUe8V2efh47MIxgBuoLxdg1nQIfEFR0bMPb3vnpNTcSCgyTK', 'user', NULL, NULL, NULL, NULL, 'Entrepreneur', 'CSE', '2025-03-22 08:03:58', 'uploads/67de6eee14501.png', '', 'TCS', 'DVELOPER', NULL, NULL, NULL, '2003-03-06', 'Male', 'At Post Kale Tal. Karad, Dist. Satara', '9763970196', '', 'BTech', 'TKIET Warananagar', 2025, '', '', 0, 'Java', '1234567890', 1, 'verified'),
('21UGCS20753', 'Munir Samir Shikalgar', 'munirshikalgar123@gmail.com', '$2y$10$GWaGojscf6FBx7P79LGTkuf6Fst.PIYpIKR6mszAMurmRF9A6ezM6', 'admin', NULL, NULL, NULL, NULL, 'Entrepreneur', 'CSE', '2025-03-19 04:41:24', 'Uploads/681b9f57523da.jpg', '', 'tcs', 'python', NULL, NULL, NULL, '2003-06-29', 'Male', 'At Post. Wangi Tal. KAdegaon, Dist Sangli', '9172119930', '', 'btech', 'tkiet', 2025, '', '', 0, 'PYTHON', '91', 1, 'verified'),
('22UDCI21603', 'Civil Coordinator', 'civil_coordinator@example.com', '$2y$10$q9/LG9zo/6HTDKKsJzZGAO/4tWy3DRYZ50CpuAH1x5McPRKgCYpqa', 'coordinator', NULL, NULL, NULL, NULL, NULL, 'Civil', '2025-05-10 15:46:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1980-01-01', 'Female', 'Civil Address', '1234567893', NULL, 'BTech', 'TKIET Warananagar', 2000, NULL, NULL, NULL, 'Coordination, Civil Management', NULL, NULL, 'verified'),
('22UDCS21289', 'Sakshi Baouso Gaikwad', 'gaikwad.sakshib13@gmail.com', NULL, 'user', NULL, NULL, NULL, NULL, 'IT', 'CSE', '2025-05-10 04:18:58', 'uploads/681ed3b220582.png', '', 'Moschip Technologies, Pune', 'Devops Enginneer', NULL, NULL, NULL, '2003-06-13', 'Female', 'A/p Shirala Tal. Shirala Dist. Sangli', '9209095186', '', 'BTech', 'TKIET Warananagar', 2025, '', '', 0, 'C, cpp', '9970615200', 3, 'pending'),
('22UDCS21562', 'Sarthak Rahul Dhavale', 'sarthak.dhavale2003@gmail.com', '$2y$10$YiJB1oqho1ylsEgE7QGpEujOFXDHtngBtXhK4ZhX8XUG5glTpqoiy', 'user', NULL, NULL, NULL, NULL, 'Entrepreneur', 'CSE', '2025-03-22 08:36:13', 'uploads/67de767cf41d9.jpg', '', 'IUDFZ', '', NULL, NULL, NULL, '2003-07-18', 'Male', 'Rahimatpur', '9404296772', '', 'BTech', 'TKIET Warananagar', 2025, '', '', 0, '', '354768899', 0, 'verified'),
('22UDCS21563', 'Asif Rafik Dhalait', 'dhalaitasif786@gmail.com', '$2y$10$q9/LG9zo/6HTDKKsJzZGAO/4tWy3DRYZ50CpuAH1x5McPRKgCYpqa', 'user', 2025, NULL, NULL, NULL, 'Government', 'CSE', '2025-03-21 14:14:43', 'uploads/67dd74533c2ed.jpg', '', 'ThinqLoud', 'Salesforce Developer', NULL, NULL, NULL, '2002-12-16', 'Male', 'At post. Rahimatpur Tal.Koregaon, Dist. Satara', '9970615200', '', 'BTech', 'TKIET Warananagar', 2025, '', '', 0, 'Salesforce', '9970615200', 1, 'verified'),
('22UDCS21564', 'Test Coordinator', 'cse_coordinator@example.com', '$2y$10$q9/LG9zo/6HTDKKsJzZGAO/4tWy3DRYZ50CpuAH1x5McPRKgCYpqa', 'coordinator', NULL, NULL, NULL, NULL, NULL, 'CSE', '2025-05-10 15:01:01', 'uploads/67dd74533c2ed.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '1980-01-01', 'Male', 'Test Address', '1234567890', NULL, 'BTech', 'TKIET Warananagar', 2000, NULL, NULL, NULL, 'Coordination, Management', NULL, NULL, 'verified'),
('22UDEN21601', 'ENTC Coordinator', 'entc_coordinator@example.com', '$2y$10$q9/LG9zo/6HTDKKsJzZGAO/4tWy3DRYZ50CpuAH1x5McPRKgCYpqa', 'coordinator', NULL, NULL, NULL, NULL, NULL, 'ENTC', '2025-05-10 15:46:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1980-01-01', 'Female', 'ENTC Address', '1234567891', NULL, 'BTech', 'TKIET Warananagar', 2000, NULL, NULL, NULL, 'Coordination, ENTC Management', NULL, NULL, 'verified'),
('22UDME21602', 'Mechanical Coordinator', 'mechanical_coordinator@example.com', '$2y$10$q9/LG9zo/6HTDKKsJzZGAO/4tWy3DRYZ50CpuAH1x5McPRKgCYpqa', 'coordinator', NULL, NULL, NULL, NULL, NULL, 'Mechanical', '2025-05-10 15:46:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1980-01-01', 'Male', 'Mechanical Address', '1234567892', NULL, 'BTech', 'TKIET Warananagar', 2000, NULL, NULL, NULL, 'Coordination, Mechanical Management', NULL, NULL, 'verified');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activities_created_by` (`created_by`);

--
-- Indexes for table `alumni_attendance`
--
ALTER TABLE `alumni_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `success_stories`
--
ALTER TABLE `success_stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `training_and_placement`
--
ALTER TABLE `training_and_placement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`prn_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `alumni_attendance`
--
ALTER TABLE `alumni_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `success_stories`
--
ALTER TABLE `success_stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `training_and_placement`
--
ALTER TABLE `training_and_placement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`prn_no`) ON DELETE CASCADE;

--
-- Constraints for table `success_stories`
--
ALTER TABLE `success_stories`
  ADD CONSTRAINT `success_stories_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`prn_no`) ON DELETE CASCADE;

--
-- Constraints for table `training_and_placement`
--
ALTER TABLE `training_and_placement`
  ADD CONSTRAINT `training_and_placement_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`prn_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
