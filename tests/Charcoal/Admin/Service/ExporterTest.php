<?php

namespace Charcoal\Tests\Admin;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory;

// From 'charcoal-admin'
use Charcoal\Admin\Service\Exporter;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ExporterTest extends AbstractTestCase
{
    /**
     * @var Exporter
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new Exporter([
           'logger'          => $container['logger'],
           'factory'         => $container['model/factory'],
           'propertyFactory' => $container['property/factory'],
           'translator'      => $container['translator'],
           'obj_type'        => 'charcoal/admin/user',
           'export_ident'    => 'y',
        ]);
    }

    /**
     * @return void
     */
    public function testExport()
    {
        $this->assertTrue(true);
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
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerViewServiceProvider($container);
            $containerProvider->registerModelServiceProvider($container);
            $containerProvider->registerTranslatorServiceProvider($container);

            $container['view'] = $this->createMock('\Charcoal\View\ViewInterface');

            $this->container = $container;
        }

        return $this->container;
    }
}
