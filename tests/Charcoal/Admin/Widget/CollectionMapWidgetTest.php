<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\CollectionMapWidget;

class CollectionMapWidgetTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->obj = new CollectionMapWidget([
            'logger' => $logger
        ]);
    }

    public function testSetLatProperty()
    {
        $ret = $this->obj->setLatProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->latProperty());
    }

    public function testSetLonProperty()
    {
        $ret = $this->obj->setLonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->lonProperty());
    }

    public function testSetPolygonProperty()
    {
        $ret = $this->obj->setPolygonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->polygonProperty());
    }

    public function testSetInfoboxTemplate()
    {
        $ret = $this->obj->setInfoboxTemplate('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->infoboxTemplate());
    }
}
