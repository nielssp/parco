<?php
namespace Parco;

class PositionTest extends TestCase
{

    public function testPosition()
    {
        $mock = $this->getMockForTrait('Parco\Position');
        
        $this->assertEquals(array(1, 1), $mock->getPosition());
        
        $mock->setPosition(array(5, 17));
        $this->assertEquals(array(5, 17), $mock->getPosition());
        
        $this->assertEquals(5, $mock->posLine());
        $this->assertEquals(17, $mock->posColumn());
    }
}
