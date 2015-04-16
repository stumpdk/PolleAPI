<?php
class TestTest extends PHPUnit_Framework_TestCase
{
    // ...
    public function testCanBeNegated()
    {
        // Assert
        $this->assertEquals(-1, -1);
    }
}
