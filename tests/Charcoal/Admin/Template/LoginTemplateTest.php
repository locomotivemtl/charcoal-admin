<?php
namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\LoginTemplate;

class LoginTemplateTest extends \PHPUnit_Framework_TestCase
{
    public static function getMethod($obj, $name)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testConstructor()
    {
        $obj = new LoginTemplate();
        $this->assertInstanceOf('\Charcoal\Admin\Template\LoginTemplate', $obj);
    }

    public function testAuthRequiredIsFalse()
    {
        $obj = new LoginTemplate();
        $foo = self::getMethod($obj, 'auth_required');
        $res = $foo->invoke($obj);
        $this->assertNotTrue($res);
    }

    public function testShowHeaderMenuIsFalse()
    {
        $obj = new LoginTemplate();
        $ret = $obj->show_header_menu();
        $this->assertNotTrue($ret);
    }

    public function testShowFooterMenuIsFalse()
    {
        $obj = new LoginTemplate();
        $ret = $obj->show_footer_menu();
        $this->assertNotTrue($ret);
    }
}
