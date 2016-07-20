<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\ObjectFormWidget;

class ObjectFormWidgetTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->obj = new ObjectFormWidget([
            'logger' => $logger
        ]);
    }

    public function testSetFormIdent()
    {
        $ret = $this->obj->setFormIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->formIdent());

        if (class_exists('\Throwable', false)) {
            $this->setExpectedException('\Throwable');
        } else {
            $this->setExpectedException('\Exception');
        }

        $this->obj->setFormIdent(false);
    }
}
