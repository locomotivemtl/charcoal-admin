<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\AdminWidget as AdminWidget;

class AdminWidgetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->obj = new AdminWidget([
            'logger'=>$logger
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'type'=>'foo',
            'ident'=>'bar',
            'label'=>'baz',
            'show_actions'=>false
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->type());
        $this->assertEquals('bar', $obj->ident());
        $this->assertEquals('baz', $obj->label());
        $this->assertNotTrue($obj->showActions());
    }

    public function testSetType()
    {
        $obj = $this->obj;
        $this->assertEquals(null, $obj->type());

        $ret = $obj->setType('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setType(null);
    }

    public function testSetLabel()
    {
        $obj = $this->obj;
        //$this->assertEquals(null, $obj->label());

        $obj = $this->obj;
        $obj->setIdent('foo.bar');
        $this->assertEquals('Foo Bar', $obj->label());

        $obj->setLabel('foo');
        $this->assertEquals('foo', $obj->label());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->set_label(null);
    }
}
