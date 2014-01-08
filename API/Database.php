<?php

/**
 * Database class
 * Creates connections and executes queries.
 * The class is created as a Singleton,
 * which means that the same class is used every time
 * it is called.
 * Usage example:
 * Database::getInstance()->runQueryGetResult($query);
 *
 * @author Bo
 */
class Database {
    
    private static $instance;
    private $mysqli;
    
    private function __construct()
    {
        $this->mysqli = new mysqli(APIConfig::$dbhost, APIConfig::$dbuser, APIConfig::$dbpass, APIConfig::$dbname);
        if ($this->mysqli->connect_error) {
            die('Connect Error (' . $this->mysqli->connect_errno . ') '
                    . $this->mysqli->connect_error);
        }
        $this->mysqli->set_charset(APIConfig::$dbCharSet);
    }
    
    /**
     * Getting the instance of the singleton class,
     * creates the instance if it is not set 
     * 
     * @return Database class instance
     */
    public static function getInstance(){
        if ( is_null( self::$instance ) ){
          self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Executing a query.
     * 
     * @param string query
     */
    public function runQueryGetAssocList($query)
    {
        $result = $this->mysqli->query($query) or die('Error: ' . $query . ' ' . $this->mysqli->error);
        return $result->fetch_all(MYSQLI_ASSOC);              
    }   
    
    /**
     * Executing a query.
     * 
     * @param string query
     */
    public function runQueryGetAssoc($query)
    {
        $result = $this->mysqli->query($query) or die('Error: ' . $query . ' ' . $this->mysqli->error);
        return $result->fetch_assoc();              
    }     
    
    /**
     * Executing a query, returns the result, not concrete data
     * 
     * @param string query 
     */
    public function runQueryGetResult($query){
        $result = $this->mysqli->query($query) or die('Error: ' . $query . ' ' . $this->mysqli->error);
        return $result;
    }
    
    /**
     * Runs query or queries in situations where the result is not used
     * 
     * @param mixed $queries
     * 
     * @return mysql result
     */
    public function runQueryQueue($queries)
    {
        if(sizeof($queries)>1){
            foreach($queries as $curQuery){
                if(strlen($curQuery)>0)
                    $this->mysqli->query($curQuery) or die('Error: ' . $queries . ' ' . $this->mysqli->error);
            }        
        }
        else{
            if(strlen($queries)>0)
                $this->mysqli->query($queries) or die('Error: ' . $query . ' ' . $this->mysqli->error);
        }
    }
    
    public function makeStringSqlSafe($string){
        return $this->mysqli->real_escape_string($string);
    }
    
    public function getInsertId(){
        return $this->mysqli->insert_id;
    }
}

?>
