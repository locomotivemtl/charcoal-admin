<?php
namespace Charcoal\Admin\Tests\Template\Object;

use \ReflectionClass;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use \Charcoal\Admin\Template\Object\EditTemplate;

class EditTemplateTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = $GLOBALS['container'];
        $this->obj = new EditTemplate([
            'logger' => new NullLogger(),
            'metadata_loader' => $container['metadata/loader']
        ]);
        $this->obj->setDependencies($container);
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
