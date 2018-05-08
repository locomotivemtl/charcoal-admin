<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

// From Slim
use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Widget\SecondaryMenuWidget;

use Charcoal\Admin\Tests\ContainerProvider;

/**
 * Class SecondaryMenuWidgetTest
 * @package Charcoal\Admin\Tests\Widget
 */
class SecondaryMenuWidgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SecondaryMenuWidget
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerWidgetFactory($container);

        $container['request'] = Request::createFromEnvironment(Environment::mock());
        $container['secondary-menu/group/factory'] = $container['widget/factory'];

        $this->obj = new SecondaryMenuWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     *
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(SecondaryMenuWidget::class, $this->obj);
    }
}
