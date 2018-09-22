SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `al_cart` (
  `id` int(10) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'item',
  `item` varchar(255) NOT NULL,
  `player` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `server` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `al_item_success` (
  `id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL DEFAULT '0',
  `login` varchar(32) NOT NULL DEFAULT '',
  `date` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `al_transactions` (
  `id` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `sum` decimal(10,2) NOT NULL DEFAULT '1.00',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `login` varchar(32) NOT NULL DEFAULT '',
  `response` text NOT NULL,
  `date_create` int(10) NOT NULL DEFAULT '0',
  `date_update` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `al_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopcart_player_idx` (`player`);

ALTER TABLE `al_item_success`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `al_transactions`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `al_cart`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `al_item_success`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `al_transactions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
