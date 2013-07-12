<?php
/**
 * Class to handle statistics.
 * Mainly used for creating new entries.
 * 
 * @author Bo
 */

class Statistics {
    
    public function addRequestEntry($type, $executionQuery, $executionTime)
    {        
        $query = "INSERT INTO " . APIConfig::$statisticsTableName . " (`type`, `query`, `IP`,`executionTime`,`timestamp`) 
                    VALUES ('" . $type . "','" . mysql_real_escape_string($executionQuery) .  "','" . $_SERVER['REMOTE_ADDR'] ."', '" . $executionTime . "', NOW())";
        
        Database::getInstance()->runQueryQueue($query);
    }
}

?>
