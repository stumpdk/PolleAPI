<?php

class FieldConditionTest extends PHPUnit_Framework_TestCase
{
    public function testNoOperator()
    {
        echo getcwd();
        $fc = new FieldCondition('testField');
        // Assert
        $this->assertEquals($fc->operator, null);
    }
}
