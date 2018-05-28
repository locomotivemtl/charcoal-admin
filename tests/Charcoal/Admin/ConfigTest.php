<?php

namespace Charcoal\Tests\Admin;

// From 'charcoal-admin'
use Charcoal\Admin\Config;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ConfigTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = new Config();
        $ret = $obj->merge([
            'basePath'=>'foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());
    }

    /**
     * @return void
     */
    public function testSetBasePath()
    {
        $obj = new Config();
        $this->assertEquals('admin', $obj->basePath());

        $ret = $obj->setBasePath('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());

        $this->expectException('\InvalidArgumentException');
        $obj->setBasePath([]);
    }

    /**
     * @return void
     */
    public function testSetBasePathEmptyParamThrowsException()
    {
        $obj = new Config();

        $this->expectException('\InvalidArgumentException');
        $obj->setBasePath('');
    }
}
