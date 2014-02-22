CREATE DATABASE IF NOT EXISTS `swallet` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `swallet`;

DROP TABLE IF EXISTS `brutes`;
CREATE TABLE IF NOT EXISTS `brutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `ip` varchar(64) NOT NULL,
  `logindata` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerID` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `node` varchar(64) NOT NULL,
  `message` varchar(512) NOT NULL,
  `extrainfo` varchar(512) NOT NULL,
  `status` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `rpc_server`;
CREATE TABLE IF NOT EXISTS `rpc_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(128) NOT NULL,
  `port` int(11) NOT NULL,
  `useSSL` int(1) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `created` datetime NOT NULL,
  `title` varchar(256) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `ca_path` varchar(256) NOT NULL,
  `backup_path` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
