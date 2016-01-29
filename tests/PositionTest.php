<?php
namespace Parco;

class PositionTest extends TestCase
{

    public function testPosition()
    {
        $mock = $this->getMockForTrait('Parco\Position');
        
        $this->assertEquals(array(0, 0), $mock->getPosition());
        
        $mock->setPosition(array(5, 17));
        $this->assertEquals(array(5, 17), $mock->getPosition());
        
        $this->assertEquals(5, $mock->line());
        $this->assertEquals(17, $mock->column());
    }
    
    public function testInputPosition()
    {
        $mock = $this->getMockForTrait('Parco\Position');

        $lines = array('foo', 'foobar');
        $this->assertEquals(0, $mock->getInputLine($lines));
        $this->assertEquals(0, $mock->getInputColumn($lines));
        $mock->setPosition(array(-1, -1));
        $this->assertEquals(2, $mock->getInputLine($lines));
        $this->assertEquals(7, $mock->getInputColumn($lines));
        $mock->setPosition(array(1, 3));
        $this->assertEquals(1, $mock->getInputLine($lines));
        $this->assertEquals(3, $mock->getInputColumn($lines));
    }
    
    public function testComparePositions()
    {
        $mock = $this->getMockForTrait('Parco\Position');
        
        $this->assertEquals(0, $mock->comparePositions(array(1, 1), array(1, 1)));
        $this->assertEquals(0, $mock->comparePositions(array(-1, -1), array(-1, -1)));

        $this->assertGreaterThan(0, $mock->comparePositions(array(1, 2), array(1, 1)));
        $this->assertGreaterThan(0, $mock->comparePositions(array(2, 1), array(1, 1)));
        $this->assertGreaterThan(0, $mock->comparePositions(array(2, 1), array(1, 2)));
        $this->assertGreaterThan(0, $mock->comparePositions(array(-1, -1), array(1, 2)));

        $this->assertLessThan(0, $mock->comparePositions(array(1, 2), array(2, 1)));
        $this->assertLessThan(0, $mock->comparePositions(array(2, 1), array(2, 2)));
        $this->assertLessThan(0, $mock->comparePositions(array(2, 2), array(3, 1)));
        $this->assertLessThan(0, $mock->comparePositions(array(2, 2), array(-1, -1)));
    }
}
