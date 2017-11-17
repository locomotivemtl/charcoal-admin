<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \ReflectionClass;

// From PHPUnit
use \PHPUnit_Framework_TestCase;

// From Pimple
use \Pimple\Container;

// From 'charcoal-admin'
use \Charcoal\Admin\Template\Object\CollectionTemplate;
use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class CollectionTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var CollectionTemplate
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
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $this->createMock(CollectionTemplate::class, null, [[
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader']
        ]]);
        $this->obj->setDependencies($container);

        $this->obj->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testAuthRequiredIsTrue()
    {
        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertTrue($res);
    }

    public function testInit()
    {
        //$ret = $this->obj->init();
        $this->assertTrue(true);
    }

    public function testTitle()
    {
        $this->obj->setObjType('charcoal/admin/user');
        $ret = $this->obj->title();
        $ret2 = $this->obj->title();

        $this->assertSame($ret, $ret2);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerWidgetFactory($container);
            $containerProvider->registerDashboardBuilder($container);
            $containerProvider->registerCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
