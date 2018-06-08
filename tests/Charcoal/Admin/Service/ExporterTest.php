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
     * @return void
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerPropertyFactory($container);
        $containerProvider->registerModelFactory($container);
        $this->obj = new Exporter([
           'logger'        => $container['logger'],
           'factory'       => $container['model/factory'],
           'translator'    => $container['translator'],
           'obj_type'      => 'charcoal/admin/user',
           'export_ident'  => 'y',
           'propertyFactory'=> $container['property/factory']
        ]);
    }

    /**
     * @return void
     */
    public function testExport()
    {
        $this->assertTrue(true);
    }
}
