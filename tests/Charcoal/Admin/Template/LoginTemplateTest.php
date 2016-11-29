<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\LoginTemplate;

/**
 *
 */
class LoginTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginTemplate
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new LoginTemplate([
            'logger' => new \Psr\Log\NullLogger()
        ]);
    }

    public static function getMethod($obj, $name)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testAuthRequiredIsFalse()
    {

        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertNotTrue($res);
    }

    public function testShowHeaderMenuIsFalse()
    {
        $this->assertNotTrue($this->obj->showHeaderMenu());
    }

    public function testShowFooterMenuIsFalse()
    {
        $this->assertNotTrue($this->obj->showHeaderMenu());
    }
}
