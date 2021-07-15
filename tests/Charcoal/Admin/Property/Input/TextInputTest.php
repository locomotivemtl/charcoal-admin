<?php

namespace Charcoal\Tests\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class TextInputTest extends AbstractTestCase
{
    /**
     * @var TextInput
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerInputDependencies($container);
        $container['view'] = $this->createMock('\Charcoal\View\ViewInterface');

        $this->obj = new TextInput([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container'       => $container,
        ]);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testSetSize()
    {
        $obj = $this->obj;
        $ret = $obj->setSize(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());

        $this->expectException('\InvalidArgumentException');
        $obj->setSize(false);
    }

    /**
     * @return void
     */
    public function testSetMinLength()
    {
        $obj = $this->obj;
        $ret = $obj->setMinLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->minLength());

        $this->expectException('\InvalidArgumentException');
        $obj->setMinLength(false);
    }

    /**
     * @return void
     */
    public function testSetMaxLength()
    {
        $obj = $this->obj;
        $ret = $obj->setMaxLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->maxLength());

        $this->expectException('\InvalidArgumentException');
        $obj->setMaxLength(false);
    }

    /**
     * @return void
     */
    public function testSetPattern()
    {
        $obj = $this->obj;
        $ret = $obj->setPattern('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->pattern());

        $this->expectException('\InvalidArgumentException');
        $obj->setPattern(false);
    }

    /**
     * @return void
     */
    public function testSetPlaceholder()
    {
        $obj = $this->obj;
        $ret = $obj->setPlaceholder('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', (string)$obj->placeholder());
    }
}
