<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\AdminWidget as AdminWidget;

class AdminWidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new AdminWidget();
        $this->assertInstanceOf('\Charcoal\Admin\AdminWidget', $obj);
    }

    public function testSetData()
    {
        $obj = new AdminWidget();
        $ret = $obj->set_data([
            'type'=>'foo',
            'ident'=>'bar',
            'label'=>'baz',
            'show_actions'=>false
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->type());
        $this->assertEquals('bar', $obj->ident());
        $this->assertEquals('baz', $obj->label());
        $this->assertNotTrue($obj->show_actions());
    }

    public function testSetType()
    {
        $obj = new AdminWidget();
        $this->assertEquals(null, $obj->type());

        $ret = $obj->set_type('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_type(null);
    }

    public function testSetLabel()
    {
        $obj = new AdminWidget();
        //$this->assertEquals(null, $obj->label());

        $obj = new AdminWidget();
        $obj->set_ident('foo.bar');
        $this->assertEquals('Foo Bar', $obj->label());

        $obj->set_label('foo');
        $this->assertEquals('foo', $obj->label());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->set_label(null);
    }
}
