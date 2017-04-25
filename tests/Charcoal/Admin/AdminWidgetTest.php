<?php

namespace Charcoal\Admin\Tests;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Pimple\Container;

use Charcoal\Admin\AdminWidget;

/**
 *
 */
class AdminWidgetTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $this->obj = new AdminWidget([
            'logger'    => new NullLogger(),
            'container' => $container
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'type'         => 'foo',
            'ident'        => 'bar',
            'label'        => 'baz',
            'show_actions' => false
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
        $obj->setType(1);
    }

    public function testSetLabel()
    {
        $obj = $this->obj;
        //$this->assertEquals(null, $obj->label());

        $obj = $this->obj;
        $obj->setIdent('foo.bar');
        $this->assertEquals(null, $obj->label());

        $obj->setLabel('Foo Bar');
        $this->assertEquals('Foo Bar', $obj->label());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->set_label(null);
    }
}
