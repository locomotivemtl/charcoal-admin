<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use Pimple\Container;

use \Charcoal\Admin\Widget\TextWidget;

use Charcoal\Admin\Tests\ContainerProvider;

class TextWidgetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $this->obj = new TextWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    public function testSetShowTitle()
    {
        $this->assertFalse($this->obj->showTitle());
        $ret = $this->obj->setShowTitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTitle());

        $this->obj->setShowTitle(true);
        $this->obj->setTitle('foo');
        $this->assertTrue($this->obj->showTitle());
    }

    public function testSetShowSubtitle()
    {
        $this->assertFalse($this->obj->showSubtitle());
        $ret = $this->obj->setShowSubtitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showSubtitle());

        $this->obj->setShowSubtitle(true);
        $this->obj->setSubtitle('foo');
        $this->assertTrue($this->obj->showSubtitle());
    }

    public function testSetShowDescription()
    {
        $this->assertFalse($this->obj->showDescription());
        $ret = $this->obj->setShowDescription(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showDescription());

        $this->obj->setShowDescription(true);
        $this->obj->setDescription('foo');
        $this->assertTrue($this->obj->showDescription());
    }

    public function testSetShowNotes()
    {
        $this->assertFalse($this->obj->showNotes());
        $ret = $this->obj->setShowNotes(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showNotes());

        $this->obj->setShowNotes(true);
        $this->obj->setNotes('foo');
        $this->assertTrue($this->obj->showNotes());
    }

    public function testSetTitle()
    {
        $ret = $this->obj->setTitle('Fôö title');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö title', (string)$this->obj->title());
    }

    public function testSetSubtitle()
    {
        $ret = $this->obj->setSubtitle('Fôö subtitle');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö subtitle', (string)$this->obj->subtitle());
    }

    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Fôö description');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö description', (string)$this->obj->description());
    }

    public function testSetNotes()
    {
        $ret = $this->obj->setNotes('Fôö notes');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö notes', (string)$this->obj->notes());
    }
}
