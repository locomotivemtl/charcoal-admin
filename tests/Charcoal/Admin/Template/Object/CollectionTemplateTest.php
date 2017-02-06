<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \ReflectionClass;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use \Pimple\Container;

use \Charcoal\Admin\Template\Object\CollectionTemplate;

use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class CollectionTemplateTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerTemplateDependencies($container);
        $containerProvider->registerMetadataLoader($container);
        $containerProvider->registerWidgetFactory($container);
        $containerProvider->registerDashboardBuilder($container);
        $containerProvider->registerCollectionLoader($container);

        $this->obj = $this->getMock(CollectionTemplate::class, null, [[
            'logger' => $container['logger'],
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
}
