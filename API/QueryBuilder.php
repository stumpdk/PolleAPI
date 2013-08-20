<?php

    class QueryBuilder{
        //public $sqlResult;
        public $sqlQuery = 'SELECT %FIELDS% %JOINS% %CONDITIONS% %GROUPBY% %ORDERBY% LIMIT %LIMIT%';
        private $fieldConditions;
        private $conditionsAsString;
        private $generalLimit;
        private $queryLimit;
        private $groupBy;
        private $selectType;
        
        /**
         * 
         * Constructor.
         * Creating the query and running pre, post and main query, making the result available for public use.
         * 
         * @param type $fields
         * @param type $joins
         * @param type $limit
         * @param type $preQueries
         * @param type $postQueries
         * @param type $groupBy
         * @param type $selectType
         */
        public function __construct($fields, $joins, $limit = 0, $groupBy = null, $orderBy = null, $selectType = 'AND')
        {           
            $this->fieldConditions = $fields;
            $this->joins = $joins;
            $this->groupBy = $groupBy;
            $this->orderBy = $orderBy;
            $this->selectType = $selectType;
            
            $this->generalLimit = APIConfig::$generalQueryLimit;
            
            if($limit > $this->generalLimit || $limit == 0){
                $this->queryLimit = $this->generalLimit;
            }
            else{
                 $this->queryLimit = $limit;
            }
            
            $this->addFieldsToQuery();
            $this->addJoinsToQuery();
            $this->addConditionsToQuery();
            $this->addGroupByToQuery();
            $this->addOrderByToQuery();
            $this->addLimitToQuery();
        }
        
        /**
         * Adding the fields to the query
         * Only fields marked to be included in the result are added
         */
        private function addFieldsToQuery()
        {
            if($this->fieldConditions){
                $fields = '';
                foreach($this->fieldConditions as $curCondition){
                    if($curCondition->includeInResult){
                        $fields .= $curCondition->field;
                        if($curCondition->alias) $fields .= ' AS ' . $curCondition->alias;
                        $fields .= ', ';
                    }
                }
                
                $fields = substr($fields, 0, strlen($fields)-2);
            }
            else{
                $fields = ' * ';
            }
            
            $this->sqlQuery = str_replace('%FIELDS%', $fields, $this->sqlQuery);
        }

        /**
         * Adding the joins to the query
         */
        private function addJoinsToQuery()
        {
            if($this->joins){
                $this->joins = ' FROM ' . $this->joins;
                $this->sqlQuery = str_replace('%JOINS%', $this->joins, $this->sqlQuery);            
            }
            else{
                die('Joins should be given!');
            }
        }        
        
        /**
         * Adding the conditions to the query
         * String values are separated by ";"
         */
        private function addConditionsToQuery()
        {
            if($this->fieldConditions){
                $firstRun = true;
                foreach($this->fieldConditions as $curCondition){
                    //If both value and operator is set, the condition is created
                    if($curCondition->value && $curCondition->operator){
                        //Adding 'WHERE' in first run
                        if($firstRun) { $conditions = ' WHERE '; $firstRun = false; }
                        
                        
                        //The parameters is split by ";".
                        //If there is more than one condition, multiple conditions are created using the same operator and field
                        
                        //Splitting the value by ";"
                        $conditionValues = explode(';',$curCondition->value);
                        
                        if(sizeof($conditionValues) > 1){          
                            //If there is more than one condition, the conditions are added separatly
                            foreach($conditionValues as $value){
                                //$conditions .= $curCondition->field . ' LIKE \'%' . $value . '%\' AND ';
                                $newCondition = $curCondition->field . ' ' . str_replace('|VALUE|', $value, $curCondition->operator) . ' ' . $this->selectType . ' ';
                                $conditions .= $newCondition;
                                
                                $this->conditionsAsString[] = $newCondition;
                            }           
                        }
                        else{
                           // $conditions .= $curCondition->field . ' ' . $curCondition->operator . ' \'' . $curCondition->value . '\' AND '; 
                            $newCondition = $curCondition->field . ' ' . str_replace('|VALUE|', $curCondition->value, $curCondition->operator) . ' ' . $this->selectType . ' ';
                            $conditions .= $newCondition;
                            
                            $this->conditionsAsString[] = $newCondition;
                        }
                    }
                }

                //Removing the last ' AND '
                $conditions = substr($conditions, 0, strlen($conditions)-(strlen($this->selectType)+2));
            }
            else{
                $conditions = ' ';
            }
            
            $this->sqlQuery = str_replace('%CONDITIONS%', $conditions, $this->sqlQuery);            
        }
        
        /**
         * Adding GROUP BY to the query
         */
        private function addGroupByToQuery()
        {
            if(!is_null($this->groupBy)){
                $this->sqlQuery = str_replace('%GROUPBY%', ' GROUP BY ' . $this->groupBy , $this->sqlQuery);
            }
            else{
                $this->sqlQuery = str_replace('%GROUPBY%', '', $this->sqlQuery);
            }
        }
        
        /**
         * Adding ORDER BY to the query
         */
        private function addOrderByToQuery()
        {
            if(!is_null($this->orderBy)){
                $this->sqlQuery = str_replace('%ORDERBY%', ' ORDER BY ' . $this->orderBy , $this->sqlQuery);
            }
            else{
                $this->sqlQuery = str_replace('%ORDERBY%', '', $this->sqlQuery);
            }
        }        
        
        /**
         * Adding LIMIT to the query
         */
        private function addLimitToQuery()
        {
            $this->sqlQuery = str_replace('%LIMIT%', $this->queryLimit, $this->sqlQuery);
        } 
        
        public function toJSON(){
            return json_encode(
                    array(
                        'conditions'=>$this->conditionsAsString,
                        'limit'=>$this->queryLimit
                    )
            );
        }
    }

?>
