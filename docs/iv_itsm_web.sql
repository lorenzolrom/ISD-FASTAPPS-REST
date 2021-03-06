-- WEB SERVER
CREATE TABLE `ITSM_WebServer` (
    `host` int(11) unsigned NOT NULL PRIMARY KEY,
    `webroot` TEXT NOT NULL,
    `logpath` TEXT NOT NULL,
    `confpath` TEXT NOT NULL,
    CONSTRAINT `ITSM_WebServer_ibfk_1` FOREIGN KEY (`host`) REFERENCES `ITSM_Host`(`id`) ON UPDATE CASCADE
);

-- REGISTRAR
CREATE TABLE `ITSM_Registrar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) DEFAULT NULL,
  `name` text NOT NULL,
  `url` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

-- VHOST
CREATE TABLE `ITSM_VHost` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` text NOT NULL,
  `subdomain` text NOT NULL,
  `name` varchar(64) NOT NULL,
  `host` int(11) unsigned DEFAULT NULL,
  `registrar` int(11) unsigned NOT NULL,
  `status` int(11) unsigned NOT NULL,
  `renewCost` float(11,2) NOT NULL,
  `webRoot` text,
  `logPath` text,
  `notes` text NOT NULL,
  `registerDate` date NOT NULL,
  `expireDate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`),
  KEY `registrar` (`registrar`),
  KEY `status` (`status`),
  CONSTRAINT `ITSM_VHost_ibfk_1` FOREIGN KEY (`host`) REFERENCES `ITSM_WebServer` (`host`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_VHost_ibfk_2` FOREIGN KEY (`registrar`) REFERENCES `ITSM_Registrar` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_VHost_ibfk_3` FOREIGN KEY (`status`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE
);

-- URL ALIAS
CREATE TABLE `NIS_URLAlias` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) NOT NULL,
  `destination` text NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`)
);

--
-- DEFAULT DATA
--

-- Attributes (wdns = VHost (Web Domain) Status)
INSERT INTO `Attribute` (`extension`, `type`, `code`, `name`) VALUES
  ('itsm', 'wdns', 'acti', 'Active'),
  ('itsm', 'wdns', 'redi', 'Redirected'),
  ('itsm', 'wdns', 'dorm', 'Dormant'),
  ('itsm', 'wdns', 'expi', 'Expired');

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('itsm_web'),
  ('itsm_web-aliases-rw'),
  ('itsm_web-registrars-r'),
  ('itsm_web-registrars-w'),
  ('itsm_web-vhosts-r'),
  ('itsm_web-vhosts-w'),
  ('itsm_weblogs'),
  ('itsm_web-servers-r'),
  ('itsm_web-servers-w');
