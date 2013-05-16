<?php
/**
 * Class to handle statistics.
 * Mainly used for creating new entries.
 * 
 * @author Bo
 */

class Statistics {
    
    public function addRequestEntry($type, $executionQuery, $executionTime, $results)
    {        
        $query = "INSERT INTO " . APIConfig::$statisticsTableName . " (`type`, `query`, `IP`,`executionTime`,`results`,`timestamp`) 
                    VALUES ('" . $type . "','" . Database::getInstance()->makeStringSqlSafe($executionQuery) .  "','" . $_SERVER['REMOTE_ADDR'] ."', '" . $executionTime . "','" . $results . "', NOW())";
        
        Database::getInstance()->runQueryQueue($query);
    }
}

?>
