--SQL for creating the api statistics table (replace api_statistics with
--any name and change the APIConfig::statisticsTableName correspondingly)

CREATE TABLE IF NOT EXISTS `api_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` char(50) NOT NULL,
  `query` text NOT NULL,
  `ip` char(50) NOT NULL,
  `executionTime` float NOT NULL,
  `results` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `type` (`type`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;