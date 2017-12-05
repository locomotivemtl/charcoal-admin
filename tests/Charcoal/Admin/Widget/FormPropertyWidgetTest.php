<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use Pimple\Container;

use \Charcoal\Admin\Widget\FormPropertyWidget;

use Charcoal\Admin\Tests\ContainerProvider;

class FormPropertyWidgetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $container['property/input/factory'] = $container['property/factory'];
        $container['property/display/factory'] = $container['property/factory'];

        $this->obj = new FormPropertyWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FormPropertyWidget::class, $this->obj);
    }

    public function testSetOutputType()
    {
        //$this->assertEquals(FormPropertyWidget::DEFAULT_OUTPUT, $this->obj->outputType());

        $ret = $this->obj->setOutputType('input');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('input', $this->obj->outputType());

        $this->obj->setOutputType('display');
        $this->assertEquals('display', $this->obj->outputType());

        $this->obj->setOutputType('');
        $this->assertEquals(FormPropertyWidget::DEFAULT_OUTPUT, $this->obj->outputType());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setOutputType(['foo']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setOutputType('foobar');
    }

    public function testPropertyType()
    {
        $this->assertNull($this->obj->propertyType());

        $ret = $this->obj->setPropertyType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->propertyType());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setPropertyType(['foo']);
    }
}
