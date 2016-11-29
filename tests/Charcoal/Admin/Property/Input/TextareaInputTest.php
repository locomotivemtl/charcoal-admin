<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\Admin\Property\Input\TextareaInput;

use \Charcoal\Admin\Tests\ContainerProvider;

class TextareaInputTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerLogger($container);
        $containerProvider->registerMetadataLoader($container);

        $this->obj = new TextareaInput([
            'logger' => $container['logger'],
            'metadata_loader' => $container['metadata/loader']
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'cols'=>42,
            'rows'=>84
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());
        $this->assertEquals(84, $obj->rows());
    }

    public function testSetCols()
    {
        $obj = $this->obj;
        $ret = $obj->setCols(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setCols('foo');
    }

    public function testSetRows()
    {
        $obj = $this->obj;
        $ret = $obj->setRows(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->rows());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setRows('foo');
    }
}
