<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormPropertyWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class FormPropertyWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
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

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormPropertyWidget::class, $this->obj);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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
