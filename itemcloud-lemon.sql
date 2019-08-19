-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2019 at 11:26 PM
-- Server version: 10.1.10-MariaDB
-- PHP Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itemcloud-lemon`
--

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `class_id` int(16) NOT NULL DEFAULT '1',
  `title` varchar(240) NOT NULL,
  `info` varchar(9600) NOT NULL,
  `file` varchar(2400) CHARACTER SET latin1 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `item_class`
--

CREATE TABLE `item_class` (
  `class_id` int(16) NOT NULL,
  `class_name` text NOT NULL,
  `level` int(100) NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_class`
--

INSERT INTO `item_class` (`class_id`, `class_name`, `level`) VALUES
(1, 'note', 3),
(2, 'link', 3),
(3, 'file', 3),
(4, 'photo', 3),
(5, 'audio', 1),
(6, 'video', 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_nodes`
--

CREATE TABLE `item_nodes` (
  `node_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `node_name` varchar(128) CHARACTER SET latin1 NOT NULL,
  `length` int(11) NOT NULL,
  `required` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_nodes`
--

INSERT INTO `item_nodes` (`node_id`, `class_id`, `node_name`, `length`, `required`) VALUES
(1, 1, 'title', 140, 1),
(2, 1, 'info', 4000, NULL),
(3, 2, 'title', 140, NULL),
(4, 2, 'info', 4000, NULL),
(5, 2, 'file', 6000, 1),
(6, 4, 'title', 140, NULL),
(7, 4, 'info', 4000, NULL),
(8, 4, 'file', 6000, 1),
(12, 3, 'title', 140, NULL),
(13, 3, 'info', 4000, NULL),
(14, 3, 'file', 6000, 1),
(15, 5, 'title', 140, NULL),
(16, 5, 'info', 4000, NULL),
(17, 5, 'file', 6000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_type`
--

CREATE TABLE `item_type` (
  `type_id` int(11) NOT NULL,
  `class_id` int(16) NOT NULL,
  `file_type` text NOT NULL,
  `ext` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_type`
--

INSERT INTO `item_type` (`type_id`, `class_id`, `file_type`, `ext`) VALUES
(201, 3, 'application/pdf', 'pdf'),
(202, 4, 'image/jpeg', 'jpg'),
(203, 5, 'audio/mpeg', 'mp3'),
(204, 6, 'video/mpeg', 'mp4'),
(205, 4, 'image/png', 'png'),
(206, 4, 'image/gif', 'gif'),
(207, 3, 'application/zip', 'zip');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `password` varchar(100) CHARACTER SET latin1 NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_items`
--

CREATE TABLE `user_items` (
  `entry` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `item_class`
--
ALTER TABLE `item_class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `item_nodes`
--
ALTER TABLE `item_nodes`
  ADD PRIMARY KEY (`node_id`);

--
-- Indexes for table `item_type`
--
ALTER TABLE `item_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_items`
--
ALTER TABLE `user_items`
  ADD PRIMARY KEY (`entry`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `item_class`
--
ALTER TABLE `item_class`
  MODIFY `class_id` int(16) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `item_nodes`
--
ALTER TABLE `item_nodes`
  MODIFY `node_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `item_type`
--
ALTER TABLE `item_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_items`
--
ALTER TABLE `user_items`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
