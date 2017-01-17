<?php
namespace Charcoal\Admin\Tests\Template\Object;

use ReflectionClass;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\Admin\Template\Object\EditTemplate;

use Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class EditTemplateTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerBaseUrl($container);
        $containerProvider->registerAdminConfig($container);
        $containerProvider->registerLogger($container);
        $containerProvider->registerModelFactory($container);
        $containerProvider->registerMetadataLoader($container);
        $containerProvider->registerAuthenticator($container);
        $containerProvider->registerAuthorizer($container);
        $containerProvider->registerWidgetFactory($container);
        $containerProvider->registerDashboardBuilder($container);

        $this->obj = new EditTemplate([
            'logger' => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],

            // This will trigger `setDependencies`
            'container' => $container
        ]);
        //$this->obj->setDependencies($container);
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

    public function testTitle()
    {
        $this->obj->setObjType('charcoal/admin/user');
        $ret = $this->obj->title();
        $ret2 = $this->obj->title();

        $this->assertSame($ret, $ret2);
    }
}
