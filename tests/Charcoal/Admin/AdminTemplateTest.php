<?php

namespace Charcoal\Admin\Tests;

use \ReflectionClass;

use \Charcoal\Admin\AdminTemplate as AdminTemplate;

class AdminTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper function for retrieving protected / private methods.
     */
    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Assert that the method `auth_required` is turned on by default.
     */
    public function testAuthRequiredIsTrue()
    {
        $obj = new AdminTemplate();
        $foo = self::getMethod($obj, 'auth_required');
        $res = $foo->invoke($obj);
        $this->assertTrue($res);
    }

    /**
     * Assert that the `set_data` method:
     * - is chainable
     * - sets the values
     */
    public function testSetData()
    {
        $obj = new AdminTemplate();
        $ret = $obj->set_data([
            'ident'=>'foo',
            'label'=>'Bar',
            'title'=>'Baz',
            'subtitle'=>'Foobar',
            'show_header_menu'=>false,
            'show_footer_menu'=>false
        ]);

        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->ident());
        $this->assertEquals('Bar', $obj->label());
        $this->assertEquals('Baz', $obj->title());
        $this->assertNotTrue($obj->show_header_menu());
        $this->assertNotTrue($obj->show_footer_menu());
    }
}
