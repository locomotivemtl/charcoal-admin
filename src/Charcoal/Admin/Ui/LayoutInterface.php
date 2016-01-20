<?php

namespace Charcoal\Admin\Ui;

/**
 *
 */
interface LayoutInterface
{
    /**
     * @param integer $position
     * @return LayoutInterface Chainable
     */
    public function setPosition($position);

    /**
     * @return integer
     */
    public function position();

    /**
     * Prepare the layouts configuration in a simpler, ready, data structure.
     *
     * This function goes through the layout options to expand loops into extra layout data...
     *
     * @param array $layouts The original layout data, typically from configuration
     * @return array Computed layouts, ready for looping
     */
    public function setStructure($layouts);

    /**
     * @return array
     */
    public function structure();

     /**
      * Get the total number of rows
      *
      * @return integer
      */
    public function numRows();

    /**
     * Get the row index at a certain position
     *
     * @param integer $position (Optional)
     * @return integer|null
     */
    public function rowIndex($position = null);

    /**
     * Get the row information
     *
     * If no `$position` is specified, then the current position will be used.
     *
     * @param integer $position (Optional pos)
     * @return array|null
     */
    public function rowData($position = null);

    /**
     * Get the number of columns (the colspan) of the row at a certain position
     * @return integer|null
     */
    public function rowNumColumns($position = null);

    /**
     * Get the number of cells at current position
     *
     * This can be different than the number of columns, in case
     *
     * @param integer|null Optional. Custom cell position. If unspecified use current position.
     */
    public function rowNumCells($position = null);

    /**
     * Get the cell index (position) of the first cell of current row.
     *
     * @param integer|null Optional. Custom cell position. If unspecified use current position.
     */
    public function rowFirstCellIndex($position = null);

    /**
     * Get the cell index in the current row.
     *
     * @param integer|null Optional. Custom cell position. If unspecified use current position.
     */
    public function cellRowIndex($position = null);

    /**
     * Get the total number of cells, in all rows.
     *
     * @return integer
     */
    public function numCellsTotal();

    /**
     * Get the span number (in # of columns) of the current cell.
     *
     * @param integer|null Optional. Custom cell position. If unspecified use current position.
     * @return integer|null
     */
    public function cellSpan($position = null);

    /**
     * Get the span number as a part of 12 (for bootrap-style grids)
     *
     * @return integer
     */
    public function cellSpanBy12($position = null);

    /**
     * Get wether or not the current cell starts a row (is the first one on the row).
     *
     * @return boolean
     */
    public function cellStartsRow($position = null);

    /**
     * Get wether or not the current cell ends a row (is the last one on the row).
     *
     * @return boolean
     */
    public function cellEndsRow($position = null);

    /**
     * @return string
     */
    public function start();

    /**
     * @return string
     */
    public function end();
}
