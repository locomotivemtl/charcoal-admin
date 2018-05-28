<?php

namespace Charcoal\Tests\Admin\Widget;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SidemenuWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 * Class SidemenuWidgetTest
 * @package Charcoal\Tests\Admin\Widget
 */
class SidemenuWidgetTest extends AbstractTestCase
{
    /**
     * @var SidemenuWidget
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerWidgetFactory($container);

        $container['request'] = Request::createFromEnvironment(Environment::mock());
        $container['sidemenu/group/factory'] = $container['widget/factory'];

        $this->obj = new SidemenuWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(SidemenuWidget::class, $this->obj);
    }
}
