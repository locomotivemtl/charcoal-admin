<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormSidebarWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class FormSidebarWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $container['property/input/factory'] = $container['property/factory'];
        $container['property/display/factory'] = $container['property/factory'];

        $this->obj = new FormSidebarWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormSidebarWidget::class, $this->obj);
    }
}
