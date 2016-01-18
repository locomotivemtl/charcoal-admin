<?php

namespace Charcoal\Admin\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

/**
*
*/
trait LayoutTrait
{
    /**
    * @var integer $position
    */
    private $position = 0;

    /**
    * @var array $structure
    */
    private $structure = [];

    /**
    * @param integer $position
    * @throws InvalidArgumentException
    * @return LayoutInterface Chainable
    */
    public function setPosition($position)
    {
        if (!is_int($position)) {
            throw new InvalidArgumentException(
                'Position must be an integer.'
            );
        }
        $this->position = $position;
        return $this;
    }

    /**
    * @return integer
    */
    public function position()
    {
        return $this->position;
    }

    /**
    * Prepare the layouts configuration in a simpler, ready, data structure.
    *
    * This function goes through the layout options to expand loops into extra layout data...
    *
    * @param array $layouts The original layout data, typically from configuration
    * @throws InvalidArgumentException
    * @return array Computed layouts, ready for looping
    */
    public function setStructure($layouts)
    {
        if (!is_array($layouts) || empty($layouts)) {
            throw new InvalidArgumentException(
                'Structure must be an array'
            );
        }

        $computedLayouts = [];
        foreach ($layouts as $l) {
            $loop = isset($l['loop']) ? $l['loop'] : 1;
            $columns = isset($l['columns']) ? $l['columns'] : [1];
            $origColumns = $columns;
            for ($i=0; $i<$loop; $i++) {
                $computedLayouts[] = $l;
                $i ++;
            }
        }

        $this->structure = $computedLayouts;

        // Chainable
        return $this;
    }

    /**
    * @return array
    */
    public function structure()
    {
        return $this->structure;
    }

    /**
    * Get the total number of rows
    *
    * @return integer
    */
    public function numRows()
    {
        $structure = $this->structure();
        return count($structure);
    }

    /**
    * Get the row index at a certain position
    *
    * @param integer $position (Optional)
    * @return integer|null
    */
    public function rowIndex($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $i = 0;
        $p = 0;
        foreach ($this->structure as $row_ident => $row) {
            $numCells = count($row['columns']);
            $p += $numCells;
            if ($p > $position) {
                return $i;
            }
            $i++;
        }
        return null;
    }

    /**
    * Get the row information
    *
    * If no `$position` is specified, then the current position will be used.
    *
    * @param integer $position (Optional pos)
    * @return array|null
    */
    public function rowData($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $rowIndex = $this->rowIndex($position);
        if (isset($this->structure[$rowIndex])) {
            return $this->structure[$rowIndex];
        } else {
            return null;
        }
    }

    /**
    * Get the number of columns (the colspan) of the row at a certain position
    * @return integer|null
    */
    public function rowNumColumns($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $row = $this->rowData($position);
        if ($row === null) {
            return null;
        } else {
            return array_sum($row['columns']);
        }
    }

    /**
    * Get the number of cells at current position
    *
    * This can be different than the number of columns, in case
    *
    * @return integer
    */
    public function rowNumCells($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        // Get the data ta position
        $row = $this->rowData($position);
        $numCells = isset($row['columns']) ? count($row['columns']) : null;
        return $numCells;
    }

        /**
    * Get the cell index (position) of the first cell of current row
    */
    public function rowFirstCellIndex($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $structure = $this->structure();
        if (empty($structure)) {
            return null;
        }
        $firstList = [];
        $i = 0;
        $p = 0;
        foreach ($structure as $row) {
            $firstList[$i] = $p;
            if ($p > $position) {
                // Previous p
                return $firstList[($i-1)];
            }

            $numCells = isset($row['columns']) ? count($row['columns']) : 0;

            $p += $numCells;

            $i++;
        }
        return $firstList[($i-1)];
    }

    /**
    * Get the cell index in the current row
    */
    public function cellRowIndex($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }
        $first = $this->rowFirstCellIndex($position);

        return ($position - $first);
    }

    /**
    * Get the total number of cells, in all rows
    *
    * @return integer
    */
    public function numCellsTotal()
    {
        $numCells = 0;
        foreach ($this->structure as $row) {
            $rowCols = isset($row['columns']) ? count($row['columns']) : 0;
            $numCells += $rowCols;
        }
        return $numCells;
    }

    /**
    * Get the span number (in # of columns) of the current cell
    *
    * @return integer|null
    */
    public function cellSpan($position = null)
    {
        $row = $this->rowData($position);
        $cellIndex = $this->cellRowIndex($position);

        // Cellspan (defaults to 1)
        return isset($row['columns'][$cellIndex]) ? (int)$row['columns'][$cellIndex] : null;
    }

    /**
    * Get the span number as a part of 12 (for bootrap-style grids)
    *
    * @return integer
    */
    public function cellSpanBy12($position = null)
    {
        $numColumns =  $this->rowNumColumns($position);
        if (!$numColumns) {
            return null;
        }
        return ($this->cellSpan($position)*(12/$numColumns));
    }

    /**
    * Get wether or not the current cell starts a row (is the first one on the row)
    *
    * @return boolean
    */
    public function cellStartsRow($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        return ($this->cellRowIndex($position) === 0);
    }

    /**
    * Get wether or not the current cell ends a row (is the last one on the row)
    *
    * @return boolean
    */
    public function cellEndsRow($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $cellNum = $this->cellRowIndex($position);
        $numCells = $this->rowNumCells($position);

        return ($cellNum >= ($numCells-1));
    }

    /**
    * @return string
    */
    public function start()
    {
        return '';
    }

    /**
    * @return string
    */
    public function end()
    {
        $this->position++;
        return '';
    }
}
