<?php
namespace Charcoal\Admin\Tests\Template\Object;

use \Charcoal\Admin\Template\Object\EditTemplate;

class EditTemplateTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = $GLOBALS['container'];
        $this->obj = $this->getMock(EditTemplate::class, null, [[
            'logger' => new \Psr\Log\NullLogger(),
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

    public function testAuthRequiredIsTrue()
    {

        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertTrue($res);
    }
}
