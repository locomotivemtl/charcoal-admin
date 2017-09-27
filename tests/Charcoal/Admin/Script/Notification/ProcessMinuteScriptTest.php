<?php

namespace Charcoal\Admin\Tests\Script\Notification;

use DateTime;
use ReflectionClass;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\Admin\Script\Notification\ProcessMinuteScript;
use Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class ProcessMinuteScriptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private $obj;

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerBaseUrl($container);
        $containerProvider->registerModelFactory($container);
        $containerProvider->registerClimate($container);

        $container['email/factory'] = function(Container $container) {
            return $container['model/factory'];
        };

        return $container;
    }

    private function callMethod($obj, $name, array $args = [])
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        $this->obj = new ProcessMinuteScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }


    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();
        $this->assertArrayHasKey('now', $args);
    }

    public function testFrequency()
    {
        $this->assertEquals('minute', $this->callMethod($this->obj, 'frequency'));
    }
}
