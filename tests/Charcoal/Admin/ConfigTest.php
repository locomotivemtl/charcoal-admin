<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Config as Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testSetData()
    {
        $obj = new Config();
        $ret = $obj->set_data([
            'base_path'=>'foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->base_path());
    }

    public function testSetBasePath()
    {
        $obj = new Config();
        $this->assertEquals('admin', $obj->base_path());

        $ret = $obj->set_base_path('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->base_path());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_base_path([]);
    }

    public function testSetBasePathEmptyParamThrowsException()
    {
        $obj = new Config();

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_base_path('');
    }

}
