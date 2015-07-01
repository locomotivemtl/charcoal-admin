<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Template as Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
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
        $obj = new Template();
        $this->assertInstanceOf('\Charcoal\Admin\Template', $obj);
    }

    public function testAuthRequiredIsTrue()
    {
        $obj = new Template();
        $foo = self::getMethod($obj, 'auth_required');
        $res = $foo->invoke($obj);
        $this->assertTrue($res);
    }

    public function testSetData()
    {
        $obj = new Template();
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
