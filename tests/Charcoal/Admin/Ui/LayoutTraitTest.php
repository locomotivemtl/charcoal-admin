<?php

namespace Charcoal\Tests\Admin\Ui;


class LayoutTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\Admin\Ui\LayoutTrait');
    }

    public function testSetPosition()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->position());
        $ret = $obj->set_position(4);
        $this->assertSame($ret, $obj);
        $this->assertEquals(4, $obj->position());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_position('foo');
    }
}
