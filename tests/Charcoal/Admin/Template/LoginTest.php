<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Login as Login;

class LoginTest extends \PHPUnit_Framework_TestCase
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
        $obj = new Login();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Login', $obj);
    }

    public function testAuthRequiredIsFalse()
    {
        $obj = new Login();
        $foo = self::getMethod($obj, 'auth_required');
        $res = $foo->invoke($obj);
        $this->assertNotTrue($res);
    }

    public function testShowHeaderMenuIsFalse()
    {
        $obj = new Login();
        $ret = $obj->show_header_menu();
        $this->assertNotTrue($ret);
    }

    public function testShowFooterMenuIsFalse()
    {
        $obj = new Login();
        $ret = $obj->show_footer_menu();
        $this->assertNotTrue($ret);
    }
}
