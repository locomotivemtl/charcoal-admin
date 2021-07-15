<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\CollectionMapWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class CollectionMapWidgetTest extends AbstractTestCase
{
    /**
     * @var CollectionMapWidget
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new CollectionMapWidget([
            'logger' => $logger
        ]);
    }

    /**
     * @return void
     */
    public function testSetLatProperty()
    {
        $ret = $this->obj->setLatProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->latProperty());
    }

    /**
     * @return void
     */
    public function testSetLonProperty()
    {
        $ret = $this->obj->setLonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->lonProperty());
    }

    /**
     * @return void
     */
    public function testSetPolygonProperty()
    {
        $ret = $this->obj->setPolygonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->polygonProperty());
    }

    /**
     * @return void
     */
    public function testSetInfoboxTemplate()
    {
        $ret = $this->obj->setInfoboxTemplate('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->infoboxTemplate());
    }
}
