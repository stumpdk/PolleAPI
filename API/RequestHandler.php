<?php
    /*
     * Main class for handling API requests. 
     * See createQueryByRequest for details.
     */

    class RequestHandler{
        private $type;
        private $limit;
        private $outputFormat;
        private $queryBuilder;
        private $preQueries;
        private $postQueries;
        private $outputGenerator;  
        private $usagePolicy;
        private $executionTime;
        
        /**
         * Constructor. Setting the type, output format, limit and query.
         * Starting the receive and output classes.
         */
        public function __construct(){
            $timeStart = microtime(true);
            $this->type = $this->getParameter('type', 'string', true);

            //Setting the output format. JSON by default
            $outputFormat = $this->getParameter('format', 'string');
            if($outputFormat == null){
                $this->outputFormat = 'json';
            }
            else{
                $this->outputFormat = $outputFormat;
            }
            
            //Setting the limit
            $this->limit = 0;
            $this->limit = $this->getParameter('limit', 'int');
            
            $this->createQueryByRequest();
            
            if(!APIConfig::$debug)
                $this->getAndOutputData();
            
            $this->executionTime = microtime(true) - $timeStart;
                       
            if(APIConfig::$enableStatistics)
                $this->addStatistics();
            
            if(APIConfig::$debug)
                $this->outputDebuggingInfo();
        }
       
        /**
         * Getting parameters in a safe way.
         * Unsetting $_REQUEST[$name]
         * Returning the value
         */
        private function getParameter($name, $type, $isRequired = false)
        {
            $parameter = null;
            switch($type){
                case 'int':
                    $parameter = (int)$_REQUEST[$name];
                    break;
                
                case 'float':
                    $parameter = (float)$_REQUEST[$name];
                    break;
                
                case 'date':
                case 'datetime':
                    $parameter = (float)str_replace('-', '', $_REQUEST[$name]);
                    break;
                
                case 'string':
                default:
                    $parameter = Database::getInstance()->makeStringSqlSafe($_REQUEST[$name]);
                    break;
            }           

            if(isset($_REQUEST[$name]))
            {
                unset($_REQUEST[$name]);
                return $parameter;
            }
            else{
                if($isRequired) die('Necessary value not given');
                return null;
            }
        }
        
        /**
         * Chosing the query and setting the fields, joins and conditions based on the type of data required
         * This is where the queries are created!
         */
        private function createQueryByRequest()
        {         
            $joins = null;
            $conditions = array();
            $orderBy = null;
            $selectType = 'AND';
 
            //Configuring the usage policies
            //By default the settings are set by the APIConfig class
            $this->usagePolicy = new UsagePolicy(strtolower($this->type));

            switch(strtolower($this->type))
            {
                case 'addresses':
                    /*
                     * Getting the nearest addresses.
                     * Not used by now
                     */
                    
                    /*
                    //$this->limit = 100;
                    $preQueries = array();
                    //Creating temporary table holding addresses and their coordinates
                    $this->preQueries[] = 'CREATE TEMPORARY TABLE `tmp_dist` (
                                    `adresse_id` INT NOT NULL PRIMARY KEY ,
                                    `distance` DECIMAL(18,12) DEFAULT NULL,
                                    `longitude` DECIMAL(18,12) DEFAULT NULL,
                                    `latitude` DECIMAL(18,12) DEFAULT NULL
                                     ) ENGINE=MEMORY';
                    
                  //  $this->preQueries[] = 'TRUNCATE TABLE tmp_dist';
                    
                    $latitude = $this->getParameter('latitude', 'float', false);
                    $longitude = $this->getParameter('longitude', 'float', false);
                    $dist = $this->getParameter('distance', 'int', false);
                             
                    //Selecting the nearest 100 addresses from the temporary table within the given distance
                    $sql_dist = "(6371 * (ACOS(SIN($latitude * PI() / 180) * SIN(k.`latitude` * PI() / 180) + COS($latitude * PI() / 180) * COS(k.`latitude` * PI() / 180) * COS((($longitude * PI()) / 180) - ((k.`longitude` * PI()) / 180)))) * 1000)";
          
                    $this->preQueries[] = "INSERT INTO tmp_dist (adresse_id, distance, longitude, latitude) SELECT a.adresse_id, $sql_dist AS dist, k.longitude, k.latitude FROM `PRB_koordinat` AS k, `PRB_adresse` AS a WHERE k.koordinat_id = a.koordinat_id HAVING dist <= $dist AND dist < 200 ORDER BY dist ASC";                   
                    
                    //Setting the fields to be loaded 
      //              $fields = array('fornavne', 'efternavn', 'foedested', 'foedselsdato', 'longitude', 'latitude');
                    
                    $conditions[] = new FieldCondition('PRB_person.person_id', 'id');
                    $conditions[] = new FieldCondition('PRB_person.registerblad_id', 'registerbladid');
                    $conditions[] = new FieldCondition('PRB_person.fornavne', 'firstnames');
                    $conditions[] = new FieldCondition('PRB_person.efternavn', 'lastname');
                    $conditions[] = new FieldCondition('PRB_foedested.foedested', 'birthplace');
                    $conditions[] = new FieldCondition('longitude', 'longitude');
                    $conditions[] = new FieldCondition('latitude', 'latitude');
                    $conditions[] = new FieldCondition('person_type', null, '1', '=', false);
                    
                    //Setting the joins
                    $joins = 'PRB_PERSON LEFT JOIN PRB_registerblad ON PRB_person.registerblad_id = PRB_registerblad.registerblad_id
                                LEFT JOIN PRB_adresse ON PRB_adresse.registerblad_id = PRB_registerblad.registerblad_id
                                LEFT JOIN PRB_foedested ON PRB_person.foedested_id = PRB_foedested.foedested_id
                                LEFT JOIN PRB_fulltext ON PRB_fulltext.registerblad_id = PRB_adresse.registerblad_id
                                JOIN tmp_dist ON PRB_adresse.adresse_id = tmp_dist.adresse_id';                   
                    
                    $filter = $this->getParameter('filter', 'string', false);
                    
                    if($filter) $conditions[] = new FieldCondition('PRB_fulltext.fulltext', null, $filter, '%LIKE%', false);
                    
                    $groupBy = 'GROUP BY tmp_dist.longitude, tmp_dist.latitude ';
                    
            */
                    break;
                
                case 'freetext':
                    /*
                    * API:
                    * polle.dk/api/?type=freetext&freetext=jens;tømrer
                    * Required:
                    * freetext (string, separated by ";")
                    * 
                    * Optional:
                    * None 
                    */  

                    $conditions[] = new FieldCondition('PRB_fulltext.fulltext', null, $this->getParameter('filter', 'string', true), '%LIKE%', false);
                    
                    $conditions[] = new FieldCondition('PRB_person.registerblad_id', 'id');
                    $conditions[] = new FieldCondition('fornavne', 'firstnames');
                    $conditions[] = new FieldCondition('efternavn', 'lastname');
                    $conditions[] = new FieldCondition('PRB_foedested.foedested', 'birthplace');
                    $conditions[] = new FieldCondition('DATE_FORMAT(PRB_person.foedselsdato, \'%d-%m-%Y\' )', 'birthdate');
                    $conditions[] = new FieldCondition('DATE_FORMAT(PRB_person.afdoed_dato, \'%d-%m-%Y\' )', 'deathdate');
                 
                    $joins = 'PRB_fulltext
                        LEFT JOIN PRB_person ON PRB_person.registerblad_id = PRB_fulltext.registerblad_id
                        LEFT JOIN PRB_foedested ON PRB_foedested.foedested_id = PRB_person.foedested_id';
                    
                    break;
                           
                case 'person':
                    /*
                    * API:
                    * polle.dk/api/?type=person&firstnames=jens&lastname=richten
                    * Required:
                    * None
                    * 
                    * Optional:
                    * firstnames (string)
                    * lastname (string)
                    * birthplace (string)
                    * dateofbirth (date)
                    * dateofdeath (date)
                    * freetext (string, separated by ";")
                    */                          
                     
                    $conditions[] = new FieldCondition('PRB_person.fornavne', 'firstnames', $this->getParameter('firstnames', 'string', false), '%LIKE%');
                    $conditions[] = new FieldCondition('PRB_person.efternavn', 'lastname', $this->getParameter('lastname', 'string', false), '%LIKE%');
                    $conditions[] = new FieldCondition('PRB_foedested.foedested', 'birthplace', $this->getParameter('birthplace', 'string', false), 'LIKE');
                    $conditions[] = new FieldCondition('PRB_person.afdoed_dato', 'dateofdeath', $this->getParameter('dateofdeath', 'string', false), '=');
                    $conditions[] = new FieldCondition('PRB_person.foedselsdato', 'dateofbirth', $this->getParameter('dateofbirth', 'string', false), '=');
                    $conditions[] = new FieldCondition('PRB_fulltext.fulltext', 'freetext', $this->getParameter('freetext', 'string', false), '%LIKE%', false);
           
                    $joins = 'PRB_person 
                        LEFT JOIN PRB_person_stilling ON PRB_person_stilling.person_id = PRB_person.person_id 
                        LEFT JOIN PRB_stilling ON PRB_person_stilling.stilling_id = PRB_stilling.stilling_id 
                        LEFT JOIN PRB_foedested ON PRB_person.foedested_id = PRB_foedested.foedested_id
                        LEFT JOIN PRB_adresse ON PRB_person.registerblad_id = PRB_adresse.registerblad_id
                        LEFT JOIN PRB_fulltext ON PRB_person.registerblad_id = PRB_fulltext.registerblad_id';
                    
                    break;
                    
                case 'heatmap': 
                    /*
                     * API:
                     * polle.dk/api/?type=heatmap&startdate=18910101&enddate=18920101&filter=tømrer;jens
                     * Required:
                     * None
                     * 
                     * Optional:
                     * startdate (date)
                     * enddate (date)
                     * filters (string, separated by ";")
                     */
       
                    $startdate = $this->getParameter('startdate', 'date', false);
                    $enddate = $this->getParameter('enddate', 'date', false);

                    $conditions[] = new FieldCondition('PRB_adresse.adresse_dato', null, $startdate, '>', false);
                    $conditions[] = new FieldCondition('PRB_adresse.adresse_dato', null, $enddate, '<', false);
                    $conditions[] = new FieldCondition('COUNT(adresse_id)', 'weight');
                    $conditions[] = new FieldCondition('ROUND(latitude,4)', 'latitude');
                    $conditions[] = new FieldCondition('ROUND(longitude,4)', 'longitude');

                    $conditions[] = new FieldCondition('PRB_fulltext.fulltext', null, $this->getParameter('filters', 'string', false), '%LIKE%', false);

                    $joins = 'PRB_koordinat
                        LEFT JOIN PRB_adresse ON PRB_adresse.koordinat_id = PRB_koordinat.koordinat_id
                        LEFT JOIN PRB_fulltext ON PRB_adresse.registerblad_id = PRB_fulltext.registerblad_id';

                    $groupBy = 'PRB_koordinat.koordinat_id';

                    break;
                case 'overallstatistics':
                    /*
                     * API:
                     * polle.dk/api/?type=overallstatistics&stattype=completeregistrations
                     * Required:
                     * stattype (string)
                     */
                    
                    $stattype = $this->getParameter('stattype', 'string');
                    
                    if($stattype == 'completed')
                        $conditions[] = new FieldCondition('complete', null, 1, '=', false);
                    
                    $conditions[] = new FieldCondition('count(registerblad_id)', 'sum');
                    
                    $joins = "PRB_registerblad";
                    
                    break;
                case 'registrationpointstotal':
                    /*
                     * API:
                     * polle.dk/api/?type=registrationpointstotal&minpoints=250&userid=217
                     * Required:
                     * None
                     * 
                     * Optional:
                     * minpoints (int)
                     * userid (int)
                     */
                    
                    $conditions[] = new FieldCondition('(SUM( `hovedpersoner` ) + SUM( `stillinger` ) * 0.5 + SUM( `aegtefaeller` ) + SUM( `boern` ) * 0.5 + SUM( `adresser` ) + SUM( `registerblade` ) * 0.5 + SUM(andre_point))', 'points');
                    $conditions[] = new FieldCondition('joomla_users.username', 'name');
                    $conditions[] = new FieldCondition('PRB_forening.forening');
                    $conditions[] = new FieldCondition('bruger_id', 'userid', $this->getParameter('userid', 'int'), '=', true);
                    $minPoints = $this->getParameter('minpoints', 'int');
                    
                    $joins = 'PRB_bruger LEFT OUTER JOIN PRB_forening ON PRB_bruger.forening_id = PRB_forening.forening_id
                              LEFT JOIN joomla_users ON PRB_bruger.bruger_id = joomla_users.id';
                    
                    $groupBy = 'userid';
                    
                    if(!is_null($minPoints))
                        $groupBy = 'userid HAVING points > ' . $minPoints;   
                    
                    $orderBy = 'points DESC';
                    
                    break;
                
                case 'registrationtypes':
                    /*
                     * API:
                     * polle.dk/api/?type=registrationtypes&registrationtype=1&start=01012013130000&end=01012013140000&userid=217
                     * Required:
                     * None
                     * 
                     * Optional:
                     * registrationtype (int)
                     * start (datetime)
                     * end (datetime)
                     * userid (int)
                     */        
                    
                    $conditions[] = new FieldCondition('type', null, $this->getParameter('registrationtype', 'int'), 'LIKE');
                    $conditions[] = new FieldCondition('bruger_id', 'userid', $this->getParameter('userid', 'int'), '=');
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('start', 'datetime'), '>');
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('end', 'datetime'), '<');
                    
                    $joins = 'PRB_registrering';
                    
                    break;
                
                case 'registrationscount':
                    /*
                     * API:
                     * polle.dk/api/?type=registrationscount&registrationtype=1&start=01012013130000&end=01012013140000&userid=217
                     * Required:
                     * None
                     * 
                     * Optional:
                     * registrationtype (int)
                     * group (string)
                     * start (datetime)
                     * end (datetime)
                     * userid (int)
                     */        
                    
                    $conditions[] = new FieldCondition('count(registrering_id)', 'antal');
                    $conditions[] = new FieldCondition('beskrivelse', 'description');
                    $conditions[] = new FieldCondition('point', 'point');
                    $conditions[] = new FieldCondition('type', null, $this->getParameter('registrationtype', 'string'), 'LIKE', true);
                    $conditions[] = new FieldCondition('bruger_id', 'userid', $this->getParameter('userid', 'int'), '=', true);
                    $conditions[] = new FieldCondition('username', 'username');
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('start', 'datetime'), '>', false);
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('end', 'datetime'), '<', false);
                    
                    $joins = 'PRB_registrering LEFT JOIN PRB_registreringstype_beskrivelser ON PRB_registrering.type = PRB_registreringstype_beskrivelser.registreringstype_id
                        LEFT JOIN joomla_users ON PRB_registrering.bruger_id = joomla_users.id';
                    //$groupBy = 'date(tidspunkt)';
                    
                    $group = strtolower($this->getParameter('group', 'string', false));

                    if(!is_null($group)){
                        if($group == 'date'){
                            $groupBy = 'date(tidspunkt)';
                        }
                        else if($group == 'hour'){
                            $groupBy = 'hour(tidspunkt)';
                        }          
                        else if($group == 'user'){
                            $groupBy = 'userid';
                        }    
                        else if($group == 'type'){
                            $groupBy = 'type';
                        }    
                        else if($group == 'user,type'){
                            $groupBy = 'userid,type';
                        }                          
                    }
      
                    break;                
                
                case 'usersearches':
                    /*
                     * API:
                     * polle.dk/api/?type=usersearches&userid=217
                     * Required:
                     * userid (int)
                     * 
                     * Optional:
                     * None
                     */             
                    
                    $conditions[] = new FieldCondition('search_id', 'searchid');
                    $conditions[] = new FieldCondition('description', 'description');
                    $conditions[] = new FieldCondition('search_app', 'searchapp');
                    $conditions[] = new FieldCondition('PRB_user_search.user_id', 'userid', $this->getParameter('userid', 'int', true), '=', false);
                    
                    //Obs: Mangler søgekritierier, som pt ikke hentes
                    
                    $joins = 'PRB_user_search LEFT JOIN KSASearch_searches ON KSASearch_searches.id = PRB_user_search.search_id';
                    
                    break;
                
                case 'userfavorites':
                    /*
                     * API:
                     * polle.dk/api/?type=userfavorites&userid=217
                     * Required:
                     * userid (int)
                     * 
                     * Optional:
                     * None
                     */      
                    
                    $conditions[] = new FieldCondition('bruger_favorit.bruger_id', null, $this->getParameter('userid', 'int', true), '=');                    
                    $conditions[] = new FieldCondition('station.station_id');
                    $conditions[] = new FieldCondition('station.nummer', 'station_nummer');
                    $conditions[] = new FieldCondition('station.navn', 'station_navn');
                    $conditions[] = new FieldCondition('filmrulle.filmrulle_id');
                    $conditions[] = new FieldCondition('filmrulle.nummer', 'filmrulle_nummer');
                    $conditions[] = new FieldCondition('registerblad.registerblad_id', 'id');
                    $conditions[] = new FieldCondition('registerblad_nummerering.nummer', 'registerblad_nummer');
                    $conditions[] = new FieldCondition('person.fornavne');
                    $conditions[] = new FieldCondition('person.efternavn');
                    $conditions[] = new FieldCondition('person.foedselsdag');
                    $conditions[] = new FieldCondition('person.foedselsmaaned');
                    $conditions[] = new FieldCondition('person.foedselsaar');
                    $conditions[] = new FieldCondition('foedested.foedested');
                    $conditions[] = new FieldCondition('person.gift', 'gift');
 
                    //$orderBy = 'person.efternavn, person.fornavne';
                    
                    $joins = 'PRB_registerblad as registerblad
                                LEFT JOIN PRB_person as person ON registerblad.registerblad_id = person.registerblad_id
                                LEFT JOIN PRB_station as station on registerblad.station_id = station.station_id
                                LEFT JOIN PRB_filmrulle as filmrulle ON registerblad.filmrulle_id = filmrulle.filmrulle_id
                                LEFT JOIN PRB_registerblad_nummerering as registerblad_nummerering ON registerblad.registerblad_id = registerblad_nummerering.registerblad_id
                                LEFT JOIN PRB_bruger_favorit as bruger_favorit ON registerblad.registerblad_id = bruger_favorit.registerblad_id
                                LEFT OUTER JOIN PRB_foedested as foedested ON person.foedested_id = foedested.foedested_id';
                    break;
                
                case 'activities':
                    /*
                     * API:
                     * polle.dk/api/?type=activities&type=2&start=01012013130000&end=01012013140000&userid=217
                     * Required:
                     * None
                     * 
                     * Optional:
                     * itemid (int)
                     * item (string)
                     * action (string)
                     * start (datetime)
                     * end (datetime)
                     * userid (int)
                     */                    
                    
                    //Only internal requests allowed
                    $this->usagePolicy = new UsagePolicy(strtolower($this->type), -1, true);
                    
                    $conditions[] = new FieldCondition('id');
                    $conditions[] = new FieldCondition('itemId', 'itemid', $this->getParameter('itemid', 'int'), '=');
                    $conditions[] = new FieldCondition('item', null, $this->getParameter('item', 'string'), 'LIKE');
                    $conditions[] = new FieldCondition('action', null, $this->getParameter('action', 'string'), 'LIKE');
                    $conditions[] = new FieldCondition('details');
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('start', 'datetime'), '>', false);
                    $conditions[] = new FieldCondition('tidspunkt', 'timestamp', $this->getParameter('end', 'datetime'), '<', false);
                    $conditions[] = new FieldCondition('timestamp');
                    $conditions[] = new FieldCondition('userId', 'userid', $this->getParameter('userid', 'int'), '=');
                    
                    $joins = 'PRB_log';
                    
                    break;
                
                case 'searchescount':
                    /*
                     * API:
                     * polle.dk/api/?type=searchescount&start=01012013130000&end=01012013140000&userid=217
                     * Required:
                     * None
                     * 
                     * Optional:
                     * start (datetime)
                     * end (datetime)
                     * userid (int)
                     */
                      
                    $conditions[] = new FieldCondition('count(id)', 'antal');
                    $conditions[] = new FieldCondition('timestamp', 'date');
                    $conditions[] = new FieldCondition('results', null, $this->getParameter('results', 'string'), '=');
                    $conditions[] = new FieldCondition('search_app', 'searchapp', $this->getParameter('searchapp', 'string'), 'LIKE');
                    $conditions[] = new FieldCondition('timestamp', null, $this->getParameter('start', 'datetime'), '>', false);
                    $conditions[] = new FieldCondition('timestamp', null, $this->getParameter('end', 'datetime'), '<', false);
                    $conditions[] = new FieldCondition('user_id', 'userid', $this->getParameter('userid', 'int'), '=');
                    
                    $joins = 'KSASearch_searches';
                    
                    break;
                
                case 'statistics':
                    /*
                     * API:
                     * polle.dk/api/?type=statistics&start=01012013130000&end=01012013140000&ip=127.0.0.1
                     * Required:
                     * None
                     * 
                     * Optional:
                     * start (datetime)
                     * end (datetime)
                     * ip (string)
                     * stattype (string)
                     */                   
                     
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.timestamp', null, $this->getParameter('start', 'datetime'), '>');
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.timestamp', null, $this->getParameter('end', 'datetime'), '<');
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.ip', null, $this->getParameter('ip', 'string'), 'LIKE');
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.type', null, $this->getParameter('stattype', 'string'), 'LIKE');
                    
                    $conditions[] = new FieldCondition('executionTime', 'executionTime');
                    
                    $joins = APIConfig::$statisticsTableName;
                    
                    break;     
                
                case 'statisticscount':
                    /*
                     * Getting the number of requests grouped by type or ip depending on input
                     * 
                     * API:
                     * polle.dk/api/?type=statistics&start=01012013130000&end=01012013140000&ip=127.0.0.1
                     * Required:
                     * group
                     * 
                     * Optional:
                     * start (datetime)
                     * end (datetime)
                     * ip (string)
                     */                   
                    
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.timestamp', null, $this->getParameter('start', 'datetime'), '>', false);
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.timestamp', null, $this->getParameter('end', 'datetime'), '<', false);
                    $conditions[] = new FieldCondition(APIConfig::$statisticsTableName . '.ip', null, $this->getParameter('ip', 'string'), 'LIKE', false);
                      
                    $conditions[] = new FieldCondition('COUNT(id)', 'antal');
                    
                    $joins = APIConfig::$statisticsTableName;
                    
                    $group = strtolower($this->getParameter('group', 'string'));
                    
                    if(is_null($group)) die();
                    
                    if($group == 'ip'){
                        $conditions[] = new FieldCondition('ip', 'ip');
                        $groupBy = APIConfig::$statisticsTableName . '.ip';
                    }
                    else if($group == 'type'){
                        $conditions[] = new FieldCondition('type', 'type');
                        $groupBy = APIConfig::$statisticsTableName . '.type';
                    }
                    
                    break;                       
                
                case 'mapdata':
                    /*
                     * Getting the map data for the generic map presenter
                     * 
                     * API:
                     * polle.dk/api/?type=mapdata&tags=jewsescape;hiddingplaces
                     * Required:
                     * tags
                     *
                     */  
                   
                    $conditions[] = new FieldCondition('id');
                    $conditions[] = new FieldCondition('name');
                    $conditions[] = new FieldCondition('content_da');
                    $conditions[] = new FieldCondition('content_en');
                    $conditions[] = new FieldCondition('geometry');
                    $conditions[] = new FieldCondition('tags', null, rawurldecode($this->getParameter('tags', 'string')), '%LIKE%', true);
                    
                    $joins = 'ksa_mapdata';
                    
                    $selectType = rawurldecode($this->getParameter('selecttype', 'string'));
                    
                    break;
                
                case 'createmapdata':
                    /*
                     * Putting the map data for the generic map presenter.
                     * Special case in which data is added!
                     * 
                     * API:
                     * polle.dk/api/?type=createmapdata&name=name&content=jsondata1&geometry=jsondata2&tags=tag1;tag2
                     * Required:
                     * name
                     * content
                     * geometry
                     * tags
                     *
                     * Optional:
                     * None
                     * 
                     * Database table:
                     * 
                     *  CREATE TABLE IF NOT EXISTS `ksa_mapdata` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `name` varchar(150) NOT NULL,
                        `content_da` text NOT NULL,
                        `content_en` text NOT NULL,
                        `geometry` text NOT NULL,
                        `tags` char(250) NOT NULL,
                        PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
                     * 
                     */  
                    $id = $this->getParameter('id', 'int');
                    $name = $this->getParameter('name', 'string');
                    $contentDa = $this->getParameter('content_da', 'string'); //JSON???
                    $contentEn = $this->getParameter('content_en', 'string'); //JSON???
                    $geometry = $this->getParameter('geometry', 'string'); //JSON???
                    $tags = strtolower( rawurldecode($this->getParameter('tags', 'string')) );
                    
                    //Validating
                    if(is_null($name) || is_null($contentDa) ||  is_null( $geometry) || is_null($tags)) die('All parameters needs to be filled out');
                    
                    //Inserting
                    if(is_null($id)){
                        $query = 'INSERT INTO ksa_mapdata (`name`, `content_da`, `content_en`, `geometry`, `tags`) VALUES (\'' . $name . '\', \'' . $contentDa . '\',\'' . $contentEn . '\',\'' . $geometry . '\', \'' . $tags . '\' )';
                        Database::getInstance()->runQueryQueue($query);
                        $id = Database::getInstance()->getInsertId();
                    }
                    //Updating
                    else{
                        $query = 'UPDATE ksa_mapdata SET  
                            `name` =  \'' . $name . '\',
                            `content_da` =  \'' . $contentDa . '\',
                            `content_en` =  \'' . $contentEn . '\',
                            `geometry` =  \'' . $geometry . '\',
                            `tags` =  \'' . $tags . '\' 
                            WHERE  `ksa_mapdata`.`id` =' . $id . ' limit 1';
                        Database::getInstance()->runQueryQueue($query);
                    }
                           
                    $conditions[] = new FieldCondition('id', null, $id, '=');
                    $conditions[] = new FieldCondition('name');
                    $conditions[] = new FieldCondition('content_da');
                    $conditions[] = new FieldCondition('content_en');
                    $conditions[] = new FieldCondition('geometry');
                    $conditions[] = new FieldCondition('tags');
                    
                    $joins = 'ksa_mapdata';
                    
                    break;                
                    
                case 'deletemapdata':
                    /*
                     * Deleting objects that match the input on parameters id, name and tags
                     * Special case in which data is deleted!
                     * 
                     * API:
                     * polle.dk/api/?type=createmapdata&name=name&content=jsondata1&geometry=jsondata2&tags=tag1;tag2
                     * Required:
                     * name
                     * content
                     * geometry
                     * tags
                     *
                     * Optional:
                     * None
                     * 
                     */
                    
                    $id = $this->getParameter('id', 'int', true);
                    
                    $query = 'DELETE FROM ksa_mapdata WHERE id = \'' . $id . '\' LIMIT 1';
                    Database::getInstance()->runQueryQueue($query);
                    die();
                    
                    break;
                default:
                    die('Service not supported');
                    break;
            }

            //If the request is not allowed according to the given usage policy, the request is stopped
            if(!$this->usagePolicy->requestAllowed()){
                //echo json_encode(array('error' => 'No more requests allowed by this type and IP'));
                die('Request not allowed');
            }
            
            //Constructing the query
            $this->queryBuilder = new QueryBuilder($conditions, $joins, $this->limit, $groupBy, $orderBy, $selectType);
        }
        
        /**
         * Running the queries and returning the data in the choosen format
         */
        private function getAndOutputData()
        {
            Database::getInstance()->runQueryQueue($this->preQueries);
            $this->outputGenerator = new OutputGenerator(Database::getInstance()->runQueryGetResult($this->queryBuilder->sqlQuery), $this->outputFormat);
            Database::getInstance()->runQueryQueue($this->postQueries);
        }
        
        /**
         * Saving statistics
         */
        private function addStatistics()
        {
            $Statistics = new Statistics();
            $Statistics->addRequestEntry($this->type, $this->queryBuilder->toJSON(), $this->executionTime, $this->outputGenerator->results);
        }
        
        /**
         * Output debug information
         */
        private function outputDebuggingInfo(){
            var_dump($this);
            echo 'Query explained: <br>';
            var_dump( Database::getInstance()->runQueryGetAssocList('EXPLAIN ' . $this->queryBuilder->sqlQuery) );
            die();
        }
    }
       
?>
