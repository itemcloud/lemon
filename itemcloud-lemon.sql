-- Itemcloud 1.1-1.2.x (lemon) Database
-- Server version: 10.1.44-MariaDB-0ubuntu0.18.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itemcloud-lemon`
--
CREATE DATABASE IF NOT EXISTS `itemcloud-lemon` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `itemcloud-lemon`;

-- --------------------------------------------------------

--
-- Table structure for table `addon`
--

CREATE TABLE `addon` (
  `addon_id` int(11) NOT NULL,
  `addon_name` varchar(140) NOT NULL,
  `name` varchar(280) NOT NULL,
  `version` varchar(64) NOT NULL,
  `active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `addon`
--

INSERT INTO `addon` (`addon_id`, `addon_name`, `name`, `version`, `active`) VALUES
(1002, 'lemon-reply', 'Lemon Item Reply (Comment Feeds)', '1.0', 1),
(1003, 'lemon-gallery', 'Lemon Gallery', '1.0', 1),
(1004, 'lemon-favorite', 'Lemon Favorites', '1.0', 1),
(1005, 'lemon-audiofeed', 'Lemon Audio Feed', '1.0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `addon_class`
--

CREATE TABLE `addon_class` (
  `entry` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `addon_class`
--

INSERT INTO `addon_class` (`entry`, `addon_id`, `class_id`) VALUES
(1, 1002, 1),
(2, 1002, 4),
(3, 1002, 2),
(4, 1003, 4),
(5, 1005, 5);

-- --------------------------------------------------------

--
-- Table structure for table `addon_feed`
--

CREATE TABLE `addon_feed` (
  `entry` int(11) NOT NULL,
  `addon_name` varchar(140) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `addon_title` varchar(140) NOT NULL,
  `collection_name` varchar(280) NOT NULL,
  `item_name` varchar(140) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `feed_limit` int(11) NOT NULL,
  `item_limit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `feed`
--

CREATE TABLE `feed` (
  `feed_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `feed_img` varchar(256) NOT NULL DEFAULT 'default.png',
  `display_id` int(11) NOT NULL DEFAULT '1',
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `feed_display`
--

CREATE TABLE `feed_display` (
  `display_id` int(11) NOT NULL,
  `display_type` varchar(128) NOT NULL,
  `name` varchar(256) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `feed_display`
--

INSERT INTO `feed_display` (`display_id`, `display_type`, `name`, `level`) VALUES
(1, 'page', 'Blog', 3),
(2, 'box', 'Grid', 3),
(3, 'list', 'Wiki', 1),
(4, 'card', 'Preview', 1),
(5, 'banner', 'Slides', 1),
(6, 'topics', 'Topics', 1);

-- --------------------------------------------------------

--
-- Table structure for table `feed_items`
--

CREATE TABLE `feed_items` (
  `entry` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `class_id` int(16) NOT NULL DEFAULT '1',
  `title` varchar(240) NOT NULL,
  `description` varchar(9600) NOT NULL,
  `link` varchar(2400) CHARACTER SET latin1 NOT NULL,
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
(5, 'audio', 2),
(6, 'video', 2);

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
(2, 1, 'description', 4000, NULL),
(3, 2, 'title', 140, NULL),
(4, 2, 'description', 4000, NULL),
(5, 2, 'link', 6000, 1),
(6, 4, 'title', 140, NULL),
(7, 4, 'description', 4000, NULL),
(8, 4, 'link', 6000, 1),
(12, 3, 'title', 140, NULL),
(13, 3, 'description', 4000, NULL),
(14, 3, 'link', 6000, 1),
(15, 5, 'title', 140, NULL),
(16, 5, 'description', 4000, NULL),
(17, 5, 'link', 6000, 1);

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
  `date` datetime NOT NULL,
  `level` int(11) NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_feeds`
--

CREATE TABLE `user_feeds` (
  `entry` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `last_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_items`
--

CREATE TABLE `user_items` (
  `entry` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` int(11) NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `user_id` int(11) NOT NULL,
  `user_img` varchar(256) DEFAULT NULL,
  `user_name` varchar(64) NOT NULL,
  `user_handle` varchar(32) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `email_alerts` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon`
--
ALTER TABLE `addon`
  ADD PRIMARY KEY (`addon_id`);

--
-- Indexes for table `addon_class`
--
ALTER TABLE `addon_class`
  ADD PRIMARY KEY (`entry`);

--
-- Indexes for table `addon_feed`
--
ALTER TABLE `addon_feed`
  ADD PRIMARY KEY (`entry`);

--
-- Indexes for table `feed`
--
ALTER TABLE `feed`
  ADD PRIMARY KEY (`feed_id`);

--
-- Indexes for table `feed_display`
--
ALTER TABLE `feed_display`
  ADD PRIMARY KEY (`display_id`);

--
-- Indexes for table `feed_items`
--
ALTER TABLE `feed_items`
  ADD PRIMARY KEY (`entry`);

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
-- Indexes for table `user_feeds`
--
ALTER TABLE `user_feeds`
  ADD PRIMARY KEY (`entry`);

--
-- Indexes for table `user_items`
--
ALTER TABLE `user_items`
  ADD PRIMARY KEY (`entry`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon`
--
ALTER TABLE `addon`
  MODIFY `addon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1006;
--
-- AUTO_INCREMENT for table `addon_class`
--
ALTER TABLE `addon_class`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `addon_feed`
--
ALTER TABLE `addon_feed`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
--
-- AUTO_INCREMENT for table `feed`
--
ALTER TABLE `feed`
  MODIFY `feed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;
--
-- AUTO_INCREMENT for table `feed_display`
--
ALTER TABLE `feed_display`
  MODIFY `display_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `feed_items`
--
ALTER TABLE `feed_items`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=404;
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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `user_feeds`
--
ALTER TABLE `user_feeds`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_items`
--
ALTER TABLE `user_items`
  MODIFY `entry` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
