<?php

namespace Charcoal\Admin\Tests;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class AdminWidgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var AdminWidget
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new AdminWidget([
            'logger'    => $container['logger'],
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

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerWidgetDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
