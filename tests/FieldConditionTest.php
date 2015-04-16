<?php
require_once "./API/FieldCondition.php";
class FieldConditionTest extends PHPUnit_Framework_TestCase
{
    public function testNoOperator()
    {
        echo 'her:' . getcwd();
        $fc = new FieldCondition('testField');
        // Assert
        $this->assertEquals($fc->operator, null);
    }
}
