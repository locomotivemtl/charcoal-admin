<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\TableWidget;

class TableWidgetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->obj = new TableWidget([
            'logger' => $logger
        ]);
    }

    public function testSetSortable()
    {
        $ret = $this->obj->setSortable(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->sortable());
    }
}
