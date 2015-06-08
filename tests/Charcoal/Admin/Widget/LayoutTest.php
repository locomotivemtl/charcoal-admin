<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Layout as Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Layout();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Layout', $obj);
        $this->assertEquals(0, $obj->position());
    }

    public function testSetData()
    {
        $struct = [[
            'columns'=>[1]
        ]];
        $computed = [
            'columns'=>[1]
        ];

        $obj = new Layout();
        $ret = $obj->set_data([
            'structure'=>$struct
        ]);
        $this->assertSame($ret, $obj);
        //$this->assertEquals($computed, $obj->structure());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_data(false);
    }

    public function testSetStructure()
    {
        $obj = new Layout();
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

        $obj = new Layout();
        $ret = $obj->set_structure($struct);

        $this->assertSame($ret, $obj);
        //$this->assertEquals($struct, $obj->structure());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_structure([]);
    }

    public function testNumRows()
    {
        $obj = new Layout();
        $this->assertEquals(0, $obj->num_rows());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(3, $obj->num_rows());
    }

    public function testRowIndex()
    {
        $obj = new Layout();
        $this->assertNull($obj->row_index());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(0, $obj->row_index(0));
        $this->assertEquals(1, $obj->row_index(1));
        $this->assertEquals(1, $obj->row_index(2));
        $this->assertEquals(2, $obj->row_index(3));
        $this->assertEquals(2, $obj->row_index(4));
        $this->assertEquals(0, $obj->row_index(5));
    }

    public function testRowData()
    {
        $obj = new Layout();
        $this->assertNull($obj->row_data());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,1]]
        ]);
        $this->assertEquals(['columns'=>[1]], $obj->row_data(0));
        $this->assertEquals(['columns'=>[1,2]], $obj->row_data(1));
        $this->assertEquals(['columns'=>[1,2]], $obj->row_data(2));
        $this->assertEquals(['columns'=>[2,1]], $obj->row_data(3));
        $this->assertEquals(['columns'=>[2,1]], $obj->row_data(4));
        $this->assertNull($obj->row_data(5));
    }

    public function testRowNumColumns()
    {
        $obj = new Layout();
        $this->assertNull($obj->row_num_columns());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->row_num_columns(0));
        $this->assertEquals(3, $obj->row_num_columns(1));
        $this->assertEquals(3, $obj->row_num_columns(2));
        $this->assertEquals(4, $obj->row_num_columns(3));
        $this->assertEquals(4, $obj->row_num_columns(4));
        $this->assertNull($obj->row_num_columns(5));
    }

    public function testRowNumCells()
    {
        $obj = new Layout();
        $this->assertNull($obj->row_num_cells());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->row_num_cells(0));
        $this->assertEquals(2, $obj->row_num_cells(1));
        $this->assertEquals(2, $obj->row_num_cells(2));
        $this->assertEquals(2, $obj->row_num_cells(3));
        $this->assertEquals(2, $obj->row_num_cells(4));
        $this->assertNull($obj->row_num_cells(5));
    }

    public function testRowFirstCellIndex()
    {
        $obj = new Layout();
        $this->assertNull($obj->row_first_cell_index());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(0, $obj->row_first_cell_index(0));
        $this->assertEquals(1, $obj->row_first_cell_index(1));
        $this->assertEquals(1, $obj->row_first_cell_index(2));
        $this->assertEquals(3, $obj->row_first_cell_index(3));
        $this->assertEquals(3, $obj->row_first_cell_index(4));
        //$this->assertNull($obj->row_first_cell_index(5));
    }

    public function testCellRowIndex()
    {
        $obj = new Layout();
        //$this->assertNull($obj->cell_row_index());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(0, $obj->cell_row_index(0));
        $this->assertEquals(0, $obj->cell_row_index(1));
        $this->assertEquals(1, $obj->cell_row_index(2));
        $this->assertEquals(0, $obj->cell_row_index(3));
        $this->assertEquals(1, $obj->cell_row_index(4));
        //$this->assertNull($obj->cell_row_index(5));
    }

    public function testNumCellsTotal()
    {
        $obj = new Layout();
        $this->assertEquals(0, $obj->num_cells_total());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(5, $obj->num_cells_total());
    }

    public function testNumCellSpan()
    {
        $obj = new Layout();
        $this->assertNull($obj->cell_span());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[2,2]]
        ]);
        $this->assertEquals(1, $obj->cell_span(0));
        $this->assertEquals(1, $obj->cell_span(1));
        $this->assertEquals(2, $obj->cell_span(2));
        $this->assertEquals(2, $obj->cell_span(3));
        $this->assertEquals(2, $obj->cell_span(4));
        $this->assertNull($obj->cell_span(5));
    }

    public function testNumCellSpanBy12()
    {
        $obj = new Layout();
        $this->assertNull($obj->cell_span_by12());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(12, $obj->cell_span_by12(0));
        $this->assertEquals(4, $obj->cell_span_by12(1));
        $this->assertEquals(8, $obj->cell_span_by12(2));
        $this->assertEquals(9, $obj->cell_span_by12(3));
        $this->assertEquals(3, $obj->cell_span_by12(4));
        $this->assertNull($obj->cell_span_by12(5));
    }

    public function testCellStartsRow()
    {
        $obj = new Layout();
        //$this->assertNull($obj->cell_starts_row());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(true, $obj->cell_starts_row(0));
        $this->assertEquals(true, $obj->cell_starts_row(1));
        $this->assertEquals(false, $obj->cell_starts_row(2));
        $this->assertEquals(true, $obj->cell_starts_row(3));
        $this->assertEquals(false, $obj->cell_starts_row(4));
        //$this->assertNull($obj->cell_starts_row(5));
    }

    public function testCellEndsRow()
    {
        $obj = new Layout();
        //$this->assertNull($obj->cell_starts_row());
        $obj->set_structure([
            ['columns'=>[1]],
            ['columns'=>[1,2]],
            ['columns'=>[3,1]]
        ]);
        $this->assertEquals(true, $obj->cell_ends_row(0));
        $this->assertEquals(false, $obj->cell_ends_row(1));
        $this->assertEquals(true, $obj->cell_ends_row(2));
        $this->assertEquals(false, $obj->cell_ends_row(3));
        $this->assertEquals(true, $obj->cell_ends_row(4));
        //$this->assertNull($obj->cell_ends_row(5));
    }
}
