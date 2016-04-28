<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\PaginationWidget;

class PaginationWidgetTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->obj = new PaginationWidget([
            'logger' => $logger
        ]);
    }

    public function testPageLogic()
    {
        $this->obj->setData([
            'page'          => 3,
            'num_per_page'  => 20,
            'num_total'     => 55
        ]);

        $this->assertEquals(3, $this->obj->numPages());
        $this->assertTrue($this->obj->showPagination());
        $this->assertTrue($this->obj->previousEnabled());
        $this->assertFalse($this->obj->nextEnabled());

        $this->assertEquals(2, $this->obj->pagePrev());
        $this->assertEquals(3, $this->obj->pageNext());

        // Switch to first page
        $this->obj->setPage(1);
        $this->assertFalse($this->obj->previousEnabled());
        $this->assertTrue($this->obj->nextEnabled());
        $this->assertEquals(1, $this->obj->pagePrev());
        $this->assertEquals(2, $this->obj->pageNext());

        // Change num total
        $this->obj->setNumTotal(15);
        $this->assertEquals(1, $this->obj->numPages());
        $this->assertFalse($this->obj->showPagination());
        $this->assertFalse($this->obj->previousEnabled());
        $this->assertFalse($this->obj->nextEnabled());
        $this->assertEquals(1, $this->obj->pagePrev());
        $this->assertEquals(1, $this->obj->pageNext());
    }

    public function testSetPage()
    {
        $this->assertEquals(1, $this->obj->page());
        $ret = $this->obj->setPage(2);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(2, $this->obj->page());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setPage('foobar');
    }

    public function testSetPageNegativeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setPage(-1);
    }

    public function testNumPerPage()
    {
        $this->assertEquals(0, $this->obj->numPerPage());
        $ret = $this->obj->setNumPerPage(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->numPerPage());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setNumPerPage('foobar');
    }

    public function testSetNumTotal()
    {
        $ret = $this->obj->setNumTotal(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->numTotal());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setNumTotal('foobar');
    }

}
