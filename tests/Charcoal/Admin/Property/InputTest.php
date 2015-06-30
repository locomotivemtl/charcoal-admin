<?php

namespace Charcoal\Admin\Tests\Property;

use \Charcoal\Admin\Property\Input as Input;

class InputTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Input();
        $this->assertInstanceOf('\Charcoal\Admin\Property\Input', $obj);
    }

    public function testSetData()
    {
        $obj = new Input();
        $ret = $obj->set_data([
            'ident'=>'foo',
            'required'=>true,
            'disabled'=>true,
            'read_only'=>true
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());
        $this->assertTrue($obj->required());
        $this->assertTrue($obj->disabled());
        $this->assertTrue($obj->read_only());

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(false);
    }
}
