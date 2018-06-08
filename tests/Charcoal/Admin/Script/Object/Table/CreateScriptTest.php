<?php

namespace Charcoal\Tests\Admin\Script\Object\Table;

use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'cache/void-adapter'
use Cache\Adapter\Void\VoidCachePool;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-admin'
use Charcoal\Admin\Script\Object\Table\CreateScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class CreateScriptTest extends AbstractTestCase
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
        return $container;
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('obj-type', $args);
    }
}
