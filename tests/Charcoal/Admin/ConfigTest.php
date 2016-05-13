<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testSetData()
    {
        $obj = new Config();
        $ret = $obj->merge([
            'basePath'=>'foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());
    }

    public function testSetBasePath()
    {
        $obj = new Config();
        $this->assertEquals('admin', $obj->basePath());

        $ret = $obj->setBasePath('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setBasePath([]);
    }

    public function testSetBasePathEmptyParamThrowsException()
    {
        $obj = new Config();

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setBasePath('');
    }
}
