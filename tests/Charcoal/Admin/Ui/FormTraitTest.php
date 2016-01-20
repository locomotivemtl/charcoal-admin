<?php

namespace Charcoal\Tests\Admin\Ui;

class FormTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\Admin\Ui\FormTrait');
    }

    /**
     * Assert that the `set_action()` method:
     * - is chainable
     * - sets the action
     * - throws an exception if the parameter is not a string
     * and that the `action()` method
     * - defaults to ""
     */
    public function testSetAction()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->action());
        $ret = $obj->setAction('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->action());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setAction(true);
    }

    /**
     * Assert that the `set_method()` method:
     * - is chainable
     * - sets the method
     * - throws an exception if the parameter is not a string
     * and that the `method()` method
     * - defaults to "post"
     */
    // public function testSetMethod()
    // {
    // 	$obj = $this->obj;
    // 	$this->assertEquals('post', $obj->method());
    // 	$ret = $obj->set_method('get');
    // 	$this->assertSame($ret, $obj);
    // 	$this->assertEquals('get', $obj->method());

    // 	$this->setExpectedException('\InvalidArgumentException');
    // 	$obj->set_method('foo');
    // }

    /**
     * Assert that the `setNextUrl()` method:
     * - is chainable
     * - sets the action
     * - throws an exception if the parameter is not a string
     * and that the `nextUrl()` method
     * - defaults to ""
     */
    public function testSetNextUrl()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->nextUrl());
        $ret = $obj->setNextUrl('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->nextUrl());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setNextUrl(true);
    }
}
