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

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(null);
    }

    public function testSetUsername()
    {
        $obj = new User();
        $ret = $obj->set_username('Foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->username());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_username(false);
    }

    public function testSetEmail()
    {
        $obj = new User();
        $ret = $obj->set_email('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->email());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_email(false);
    }

    /*public function testSetEmailInvalidEmailThrowsException()
    {
        $obj = new User();

        $this->setExpectedException('\InvalidArgumentException');
        $ret = $obj->set_email('foo');
    }*/

    public function testKey()
    {
        $obj = new User();
        $ret = $obj->key();
        $this->assertEquals('username', $ret);
    }
}
