<?php

namespace Charcoal\Admin\Tests\Property\Input;

use \Charcoal\Admin\Property\Input\TextareaInput;

class TextareaInputTest extends \PHPUnit_Framework_TestCase
{

    public function testSetData()
    {
        $obj = new TextareaInput();
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
        $obj = new TextareaInput();
        $ret = $obj->set_cols(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_cols('foo');
    }

    public function testSetRows()
    {
        $obj = new TextareaInput();
        $ret = $obj->set_rows(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->rows());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_rows('foo');
    }
}
