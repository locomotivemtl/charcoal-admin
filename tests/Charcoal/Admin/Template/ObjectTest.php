<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Object as Object;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Object();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Object', $obj);
    }

    public function testSetData()
    {
        $obj = new Object();
        $ret = $obj->set_data([
            'obj_type'=>'foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->obj_type());
    }

    public function testSetObjType()
    {
        $obj = new Object();
        $this->assertEquals(null, $obj->obj_type());

        $ret = $obj->set_obj_type('bar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->obj_type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_obj_type(null);
    }
}
