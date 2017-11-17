<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\Admin\Property\Input\TextInput;

use \Charcoal\Admin\Tests\ContainerProvider;

class TextInputTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
//        $container = $GLOBALS['container'];
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerTranslator($container);
        $containerProvider->registerLogger($container);
        $containerProvider->registerMetadataLoader($container);
        $container['view'] = $this->createMock('\Charcoal\View\ViewInterface');

        $this->obj = new TextInput([
            'logger' => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container' => $container
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'size'        => 42,
            'min_length'  => 10,
            'max_length'  => 100,
            'pattern'     => 'foo',
            'placeholder' => 'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());
        $this->assertEquals(10, $obj->minLength());
        $this->assertEquals(100, $obj->maxLength());
        $this->assertEquals('foo', (string)$obj->pattern());
        $this->assertEquals('bar', (string)$obj->placeholder());
    }

    public function testSetSize()
    {
        $obj = $this->obj;
        $ret = $obj->setSize(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSize(false);
    }

    public function testSetMinLength()
    {
        $obj = $this->obj;
        $ret = $obj->setMinLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength(false);
    }

    public function testSetMaxLength()
    {
        $obj = $this->obj;
        $ret = $obj->setMaxLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->maxLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMaxLength(false);
    }

    public function testSetPattern()
    {
        $obj = $this->obj;
        $ret = $obj->setPattern('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->pattern());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPattern(false);
    }

    public function testSetPlaceholder()
    {
        $obj = $this->obj;
        $ret = $obj->setPlaceholder('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', (string)$obj->placeholder());
    }
}
