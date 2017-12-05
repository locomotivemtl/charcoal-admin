<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use Pimple\Container;

use \Charcoal\Admin\Widget\FormSidebarWidget;

use Charcoal\Admin\Tests\ContainerProvider;

class FormSidebarWidgetTest extends PHPUnit_Framework_TestCase
{
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

    public function testConstructor()
    {
        $this->assertInstanceOf(FormSidebarWidget::class, $this->obj);
    }
}
