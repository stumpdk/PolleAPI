<?php
echo 'her:' . getcwd();
require_once "../API/FieldCondition.php";
class FieldConditionTest extends PHPUnit_Framework_TestCase
{
    public function testNoOperator()
    {
        $fc = new FieldCondition($field, $alias = null, $value = null, $operator = null, $includeInResult = true);
        $fc = new FieldCondition('testField');
        // Assert
        $this->assertEquals($fc->operator, null);
    }
}
