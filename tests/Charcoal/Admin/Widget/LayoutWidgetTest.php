<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\LayoutWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class LayoutWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $this->obj = new LayoutWidget([
            'logger' => $logger
        ]);
    }

    /**
     * @return void
     */
    public function testDefaultPosition()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Admin\Widget\LayoutWidget', $obj);
        $this->assertEquals(0, $obj->position());
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $struct = [[
            'columns'=>[1]
        ]];
        $computed = [
            'columns'=>[1]
        ];

        $obj = $this->obj;
        $ret = $obj->setData([
            'structure'=>$struct
        ]);
        $this->assertSame($ret, $obj);
        //$this->assertEquals($computed, $obj->structure());
    }

    /**
     * @return void
     */
    public function testSetStructure()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->structure());

        $struct = [
            [
                'columns'=>[1,2],
            ],
            [
                'columns'=>[1]
            ],
            [
                'columns'=>[1,1,1,1],
                'loop'=>3
            ]
        ];

        $obj = $this->obj;
        $ret = $obj->setStructure($struct);

        $this->assertSame($ret, $obj);
        //$this->assertEquals($struct, $obj->structure());
    }

    /**
     * @return void
     */
    public function testNumRows()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numRows());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(3, $obj->numRows());
    }

    /**
     * @return void
     */
    public function testRowIndex()
    {
        $obj = $this->obj;
        $this->assertNull($obj->rowIndex());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(0, $obj->rowIndex(0));
        $this->assertEquals(1, $obj->rowIndex(1));
        $this->assertEquals(1, $obj->rowIndex(2));
        $this->assertEquals(2, $obj->rowIndex(3));
        $this->assertEquals(2, $obj->rowIndex(4));
        $this->assertEquals(0, $obj->rowIndex(5));
    }

    /**
     * @return void
     */
    public function testRowData()
    {
        $obj = $this->obj;
        $this->assertNull($obj->rowData());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(['columns'=>[1]], $obj->rowData(0));
        $this->assertEquals(['columns'=>[1,2]], $obj->rowData(1));
        $this->assertEquals(['columns'=>[1,2]], $obj->rowData(2));
        $this->assertEquals(['columns'=>[2,1]], $obj->rowData(3));
        $this->assertEquals(['columns'=>[2,1]], $obj->rowData(4));
        $this->assertNull($obj->rowData(5));
    }

    /**
     * @return void
     */
    public function testRowNumColumns()
    {
        $obj = $this->obj;
        $this->assertNull($obj->rowNumColumns());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->rowNumColumns(0));
        $this->assertEquals(3, $obj->rowNumColumns(1));
        $this->assertEquals(3, $obj->rowNumColumns(2));
        $this->assertEquals(4, $obj->rowNumColumns(3));
        $this->assertEquals(4, $obj->rowNumColumns(4));
        $this->assertNull($obj->rowNumColumns(5));
    }

    /**
     * @return void
     */
    public function testRowNumCells()
    {
        $obj = $this->obj;
        $this->assertNull($obj->rowNumCells());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->rowNumCells(0));
        $this->assertEquals(2, $obj->rowNumCells(1));
        $this->assertEquals(2, $obj->rowNumCells(2));
        $this->assertEquals(2, $obj->rowNumCells(3));
        $this->assertEquals(2, $obj->rowNumCells(4));
        $this->assertNull($obj->rowNumCells(5));
    }

    /**
     * @return void
     */
    public function testRowFirstCellIndex()
    {
        $obj = $this->obj;
        $this->assertNull($obj->rowFirstCellIndex());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(0, $obj->rowFirstCellIndex(0));
        $this->assertEquals(1, $obj->rowFirstCellIndex(1));
        $this->assertEquals(1, $obj->rowFirstCellIndex(2));
        $this->assertEquals(3, $obj->rowFirstCellIndex(3));
        $this->assertEquals(3, $obj->rowFirstCellIndex(4));
        //$this->assertNull($obj->rowFirstCellIndex(5));
    }

    /**
     * @return void
     */
    public function testCellRowIndex()
    {
        $obj = $this->obj;
        //$this->assertNull($obj->cellRowIndex());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(0, $obj->cellRowIndex(0));
        $this->assertEquals(0, $obj->cellRowIndex(1));
        $this->assertEquals(1, $obj->cellRowIndex(2));
        $this->assertEquals(0, $obj->cellRowIndex(3));
        $this->assertEquals(1, $obj->cellRowIndex(4));
        //$this->assertNull($obj->cellRowIndex(5));
    }

    /**
     * @return void
     */
    public function testNumCellsTotal()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numCellsTotal());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(5, $obj->numCellsTotal());
    }

    /**
     * @return void
     */
    public function testNumCellSpan()
    {
        $obj = $this->obj;
        $this->assertNull($obj->cellSpan());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->cellSpan(0));
        $this->assertEquals(1, $obj->cellSpan(1));
        $this->assertEquals(2, $obj->cellSpan(2));
        $this->assertEquals(2, $obj->cellSpan(3));
        $this->assertEquals(2, $obj->cellSpan(4));
        $this->assertNull($obj->cellSpan(5));
    }

    /**
     * @return void
     */
    public function testNumCellSpanBy12()
    {
        $obj = $this->obj;
        $this->assertNull($obj->cellSpanBy12());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(12, $obj->cellSpanBy12(0));
        $this->assertEquals(4, $obj->cellSpanBy12(1));
        $this->assertEquals(8, $obj->cellSpanBy12(2));
        $this->assertEquals(9, $obj->cellSpanBy12(3));
        $this->assertEquals(3, $obj->cellSpanBy12(4));
        $this->assertNull($obj->cellSpanBy12(5));
    }

    /**
     * @return void
     */
    public function testCellStartsRow()
    {
        $obj = $this->obj;
        //$this->assertNull($obj->cellStartsRow());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(true, $obj->cellStartsRow(0));
        $this->assertEquals(true, $obj->cellStartsRow(1));
        $this->assertEquals(false, $obj->cellStartsRow(2));
        $this->assertEquals(true, $obj->cellStartsRow(3));
        $this->assertEquals(false, $obj->cellStartsRow(4));
        //$this->assertNull($obj->cellStartsRow(5));
    }

    /**
     * @return void
     */
    public function testCellEndsRow()
    {
        $obj = $this->obj;
        //$this->assertNull($obj->cellStartsRow());
        $obj->setStructure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(true, $obj->cellEndsRow(0));
        $this->assertEquals(false, $obj->cellEndsRow(1));
        $this->assertEquals(true, $obj->cellEndsRow(2));
        $this->assertEquals(false, $obj->cellEndsRow(3));
        $this->assertEquals(true, $obj->cellEndsRow(4));
        //$this->assertNull($obj->cellEndsRow(5));
    }

    /**
     * @return void
     */
    public function testStart()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->start());
    }

    /**
     * @return void
     */
    public function testEnd()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->position());
        $ret = $obj->end();
        $this->assertEquals(1, $obj->position());
        $this->assertEquals('', $ret);
    }
}
