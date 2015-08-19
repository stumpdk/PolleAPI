<?php
require_once "./API/APIConfig.php";
require_once "./API/QueryBuilder.php";
require_once "./API/FieldCondition.php";
class QueryBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testAcceptsNullValue()
    {
        $fc = array();
        $fc[] = new FieldCondition('testField', '0');
        $join = 'test_table';
        
        $qb = new QueryBuilder($fc, $join);
        
        // Assert
        $this->assertEquals($qb->sqlQuery, "SELECT testField FROM test_table WHERE testField = 0", "should add 0 as value");
    }
}
