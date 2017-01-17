<?php

namespace Charcoal\Admin\Tests;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\Admin\AdminTemplate;

use Charcoal\Admin\Tests\ContainerProvider;

class AdminTemplateTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerAdminConfig($container);
        $containerProvider->registerBaseUrl($container);
        $containerProvider->registerModelFactory($container);
        $containerProvider->registerLogger($container);
        $containerProvider->registerMetadataLoader($container);
        $containerProvider->registerAuthenticator($container);
        $containerProvider->registerAuthorizer($container);

        $this->obj = $this->getMock(AdminTemplate::class, null, [[
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
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testSetIdent()
    {
        $this->assertEquals('', $this->obj->ident());
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());
    }

    public function testSetLabel()
    {
        $this->assertEquals(null, $this->obj->label());
        $ret = $this->obj->setLabel('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', (string)$this->obj->label());
    }

    public function testAuthRequiredIsTrue()
    {

        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertTrue($res);
    }
}
