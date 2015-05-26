<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Form as Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Form();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Form', $obj);
    }

    public function testConstructorSetsData()
    {
        $obj = new Form(['method'=>'get']);
        $this->assertEquals('get', $obj->method());
    }

    public function testSetData()
    {
        $data = [
            'action'=>'foo',
            'method'=>'get'
        ];
        $obj = new Form();
        $obj->set_data($data);
        $this->assertEquals('get', $obj->method());
        $this->assertEquals('foo', $obj->action());
    }

    public function testSetDatanvalidArgumentSetsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new Form();
        $obj->set_data(null);
    }

    public function testSetDataIsChainable()
    {
        $data = [
            'action'=>'foo',
            'method'=>'get'
        ];
        $obj = new Form();
        $ret = $obj->set_data($data);
        $this->assertSame($obj, $ret);
    }
}
