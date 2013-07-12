<?php

    class FieldCondition
    {
        public $field;
        public $operator;
        public $value;
        public $alias;
        public $includeInResult;
        
        public function __construct($field, $alias = null, $value = null, $operator = null, $includeInResult = true)
        {
            $this->field = $field;
            $this->value = $value;
            $this->operator = $operator;
            $this->alias = $alias;
            $this->includeInResult = $includeInResult;
            
            if(!is_null($this->operator))
                $this->convertOperator();
        }
        
        /*
         * Converting the user friendly operator tags to the ones used while creating
         * the conditions in the query builder
         */
        private function convertOperator(){
            switch($this->operator){
                case 'LIKE':
                    $this->operator = 'LIKE \'|VALUE|\'';
                    break;
                case '%LIKE%':
                    $this->operator = 'LIKE \'%|VALUE|%\'';
                    break;
                case '%LIKE':
                    $this->operator = 'LIKE \'%|VALUE|\'';
                    break;
                case 'LIKE%':
                    $this->operator = 'LIKE \'|VALUE|%\'';
                    break;
                default:
                    $this->operator = $this->operator . ' \'|VALUE|\'';
                    break;
            }
        }
    }
?>
