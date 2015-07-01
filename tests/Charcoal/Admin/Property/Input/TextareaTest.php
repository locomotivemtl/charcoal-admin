<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \Charcoal\Admin\Property\Input\Textarea as Textarea;

class TextareaTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Textarea();
        $this->assertInstanceOf('\Charcoal\Admin\Property\Input\Textarea', $obj);
    }

    public function testSetData()
    {
        $obj = new Textarea();
        $ret = $obj->set_data([
            'cols'=>42,
            'rows'=>84
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());
        $this->assertEquals(84, $obj->rows());
    }

    public function testSetCols()
    {
        $obj = new Textarea();
        $ret = $obj->set_cols(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_cols('foo');
    }

    public function testSetRows()
    {
        $obj = new Textarea();
        $ret = $obj->set_rows(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->rows());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_rows('foo');
    }
}
