<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\User as User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new User();
        $this->assertInstanceOf('\Charcoal\Admin\User', $obj);
    }

    public function testSetData()
    {
        $obj = new User();
        $ret = $obj->set_data([
            'username'=>'Foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->username());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_data(null);
    }
}
