<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \Charcoal\Admin\Property\Input\TextInput;

class TextInputTest extends \PHPUnit_Framework_TestCase
{

    public function testSetData()
    {
        $obj = new TextInput();
        $ret = $obj->setData([
            'size'=>42,
            'min_length'=>10,
            'max_length'=>100,
            'pattern'=>'foo',
            'placeholder'=>'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());
        $this->assertEquals(10, $obj->minLength());
        $this->assertEquals(100, $obj->maxLength());
        $this->assertEquals('foo', $obj->pattern());
        $this->assertEquals('bar', $obj->placeholder());
    }

    public function testSetSize()
    {
        $obj = new TextInput();
        $ret = $obj->setSize(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSize(false);

    }

    public function testSetMinLength()
    {
        $obj = new TextInput();
        $ret = $obj->setMinLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength(false);

    }

    public function testSetMaxLength()
    {
        $obj = new TextInput();
        $ret = $obj->setMaxLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->maxLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMaxLength(false);

    }

    public function testSetPattern()
    {
        $obj = new TextInput();
        $ret = $obj->setPattern('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->pattern());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPattern(false);

    }

    public function testSetPlaceholder()
    {
        $obj = new TextInput();
        $ret = $obj->setPlaceholder('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->placeholder());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPlaceholder(false);

    }
}
