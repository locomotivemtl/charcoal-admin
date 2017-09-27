<?php

namespace Charcoal\Admin\Tests\Script\Object\Table;

use \PDO;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Source\DatabaseSource;

use \Charcoal\Admin\Script\Object\Table\CreateScript;

use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class CreateScriptTest extends PHPUnit_Framework_TestCase
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

    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerBaseUrl($container);
        $containerProvider->registerModelFactory($container);
        $containerProvider->registerClimate($container);
        return $container;
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        $this->obj = new CreateScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'climate_reader' => $this->container['climate/reader'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }

    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('obj-type', $args);
    }
}
