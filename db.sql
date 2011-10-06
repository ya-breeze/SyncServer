/*!40101 SET NAMES utf8 */;
/*!40101 SET character_set_client = utf8 */;

DROP TABLE IF EXISTS `Items`;
CREATE TABLE `Items` (
  `id` varbinary(16) NOT NULL,
  `body` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `Logs`;
CREATE TABLE `Logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item` varbinary(16) DEFAULT NULL,
  `status` enum('updated','deleted') NOT NULL,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `localUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ItemUpdated` (`localUpdated`),
  UNIQUE KEY `LogsItem` (`item`)
) ENGINE=InnoDB;
