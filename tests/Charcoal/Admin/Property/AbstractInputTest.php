<?php

namespace Charcoal\Admin\Tests\Property;

class AbstractInputTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Admin\Property\AbstractPropertyInput');
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'ident'=>'foo',
            'required'=>true,
            'disabled'=>true,
            'read_only'=>true
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());
        $this->assertTrue($obj->required());
        $this->assertTrue($obj->disabled());
        $this->assertTrue($obj->readOnly());
    }
}
