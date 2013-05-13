<?php
/*
 * The configuration class.
 * All members are statics, and is used throughout the API
 * for various settings.
 * 
 * SQL for creating the api statistics table (replace api_statistics with
 * any name and change the APIConfig::statisticsTableName correspondingly   ):

    CREATE TABLE IF NOT EXISTS `api_statistics` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` char(50) NOT NULL,
      `query` text NOT NULL,
      `ip` char(50) NOT NULL,
      `executionTime` float NOT NULL,
      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `timestamp` (`timestamp`),
      KEY `type` (`type`),
      KEY `ip` (`ip`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 *
 */

    //Includes the necessary files here so the config.php also functions as a bootstraper
    include_once 'RequestHandler.php';
    include_once 'QueryBuilder.php';
    include_once 'OutputGenerator.php';
    include_once 'FieldCondition.php';
    include_once 'Database.php';
    include_once 'UsagePolicy.php';
    include_once 'Statistics.php';

    class APIConfig{
        //Database informations
        public static $dbhost = 'localhost';
        public static $dbuser = 'databaseuser';
        public static $dbpass = 'databasepassword';
        public static $dbname = 'databasename';
        public static $dbCharSet = 'utf8';
        
        //Whether or not the statistics should be enabled
        public static $enableStatistics = true;
        
        //Name for the table holding the statistics
        public static $statisticsTableName = 'api_statistics';
        
        //Overall limit for queries. Overrides any user given input
        public static $generalQueryLimit = 1000;
        
        //Maximum number of requests of a given type in the counter time range
        public static $maxNumberOfRequests = 1000;
        
        //Default request policy. Is overridden if given by the constructor
        public static $internalRequestsOnly = false;
        
        //When the number of requests is counted, this time range is used as a limit
        public static $requestCounterTimeRange = 86400;
    }

?>
