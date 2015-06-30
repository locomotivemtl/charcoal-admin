<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \Charcoal\Admin\Property\Input\Text as Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Text();
        $this->assertInstanceOf('\Charcoal\Admin\Property\Input\Text', $obj);
    }

    public function testSetData()
    {
        $obj = new Text();
        $ret = $obj->set_data([
            'size'=>42,
            'min_length'=>10,
            'max_length'=>100,
            'pattern'=>'foo',
            'placeholder'=>'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());
        $this->assertEquals(10, $obj->min_length());
        $this->assertEquals(100, $obj->max_length());
        $this->assertEquals('foo', $obj->pattern());
        $this->assertEquals('bar', $obj->placeholder());

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(false);
    }

    public function testSetSize()
    {
        $obj = new Text();
        $ret = $obj->set_size(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_size(false);

    }

    public function testSetMinLength()
    {
        $obj = new Text();
        $ret = $obj->set_min_length(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->min_length());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_min_length(false);

    }

    public function testSetMaxLength()
    {
        $obj = new Text();
        $ret = $obj->set_max_length(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->max_length());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_max_length(false);

    }

    public function testSetPattern()
    {
        $obj = new Text();
        $ret = $obj->set_pattern('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->pattern());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_pattern(false);

    }

    public function testSetPlaceholder()
    {
        $obj = new Text();
        $ret = $obj->set_placeholder('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->placeholder());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_placeholder(false);

    }
}
