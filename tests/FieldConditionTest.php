<?php
require_once "./API/FieldCondition.php";
class FieldConditionTest extends PHPUnit_Framework_TestCase
{
    public function testNoOperator()
    {
        $fc = new FieldCondition('testField');
        // Assert
        $this->assertEquals($fc->operator, null, "should not convert operator when not given");
    }
    
    public function testWithOperator()
    {
        $fc = new FieldCondition('testField', null, 'value', 'LIKE');
        
        $this->assertEquals($fc->operator, "LIKE '|VALUE|'", "should convert operator when given");
    }
}
