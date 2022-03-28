SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `opencloud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `opencloud`;

CREATE TABLE `videos` (
  `file_loc` varchar(200) NOT NULL,
  `id` varchar(10) NOT NULL,
  `views` int(10) NOT NULL DEFAULT 0,
  `last_checked` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `last_checked` (`last_checked`),
  ADD KEY `views` (`views`);
