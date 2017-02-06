<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Pimple\Container;

use Charcoal\Admin\Widget\TableWidget;

use Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class TableWidgetTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerWidgetFactory($container);
        $containerProvider->registerPropertyFactory($container);
        $containerProvider->registerPropertyDisplayFactory($container);

        $container['view'] = $this->getMock('\Charcoal\View\ViewInterface');

        $logger = new NullLogger();
        $this->obj = new TableWidget([
            'logger' => $logger,
            'container' => $container
        ]);
    }

    public function testSetSortable()
    {
        $ret = $this->obj->setSortable(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->sortable());

        $this->obj['sortable'] = false;
        $this->assertFalse($this->obj->sortable());

        $this->obj->set('sortable', true);
        $this->assertTrue($this->obj['sortable']);
    }

    public function testShowTableHeader()
    {
        $this->assertTrue($this->obj->showTableHeader());
        $ret = $this->obj->setShowTableHeader(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTableHeader());

        $this->obj['show_table_header'] = true;
        $this->assertTrue($this->obj->showTableHeader());

        $this->obj->set('show_table_header', false);
        $this->assertFalse($this->obj['show_table_header']);
    }

    public function testShowTableHead()
    {
        $this->assertTrue($this->obj->showTableHead());
        $ret = $this->obj->setShowTableHead(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTableHead());

        $this->obj['show_table_head'] = true;
        $this->assertTrue($this->obj->showTableHead());

        $this->obj->set('show_table_head', false);
        $this->assertFalse($this->obj['show_table_head']);
    }

    public function testShowTableFoot()
    {
        $this->assertFalse($this->obj->showTableFoot());
        $ret = $this->obj->setShowTableFoot(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTableFoot());

        $this->obj['show_table_foot'] = false;
        $this->assertFalse($this->obj->showTableFoot());

        $this->obj->set('show_table_foot', true);
        $this->assertTrue($this->obj['show_table_foot']);
    }
}
