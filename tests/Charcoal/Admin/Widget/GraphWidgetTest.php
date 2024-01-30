<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\Graph\AbstractGraphWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GraphWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Admin\Widget\Graph\AbstractGraphWidget', [[
            'logger'=>$logger
        ]]);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'height' => 222,
            'colors' => ['#ff0000', '#0000ff']
        ]);
        $this->assertSame($obj, $ret);
        $this->assertEquals('222px', $obj->height());
        $this->assertEquals(['#ff0000', '#0000ff'], $obj->colors());
    }

    /**
     * @return void
     */
    public function testSetHeight()
    {
        $obj =  $this->obj;
        $this->assertEquals('400px', $obj->height());

        $ret = $obj->setHeight(333);
        $this->assertSame($obj, $ret);
        $this->assertEquals('333px', $obj->height());

        //$this->expectException('\InvalidArgumentException');
        //$obj->setHeight(false);
    }

    /**
     * @return void
     */
    public function testSetColors()
    {
        $obj =  $this->obj;
        $this->assertEquals($obj->defaultColors(), $obj->colors());

        $ret = $obj->setColors(['#fff', '#000']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['#fff', '#000'], $obj->colors());
    }
}
