<?php

namespace Charcoal\Tests\Admin\Template;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Template\ElfinderTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ElfinderTemplateTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var ElfinderTemplate
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
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new ElfinderTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAdminAssertsUrl()
    {
        $ret = $this->obj->adminAssetsUrl();
        $this->assertEquals('/assets/admin/', $ret);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerElfinderConfig($container);
            $container['widget/factory'] = $this->createMock('\Charcoal\Factory\FactoryInterface');

            $this->container = $container;
        }

        return $this->container;
    }
}
