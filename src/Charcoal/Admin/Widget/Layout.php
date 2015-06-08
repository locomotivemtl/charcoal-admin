<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;
use \Iterator as Iterator;

use \Charcoal\Admin\Widget as Widget;

class Layout extends Widget
{
    /**
    * @var integer $_position
    */
    private $_position = 0;

    /**
    * @var array $_structure
    */
    protected $_structure = [];


    public function __construct($data = null)
    {
        // Initialize (empty) structure
        $this->_structure = [];

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return Layout Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        if (isset($data['structure']) && $data['structure'] !== null) {
            $this->set_structure($data['structure']);
        }

        return $this;
    }

    /**
    * @return integer
    */
    public function position()
    {
        return $this->_position;
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
    public function set_structure($layouts)
    {
        if (!is_array($layouts) || empty($layouts)) {
            throw new InvalidArgumentException('Structure must be an array');
        }

        $computed_layouts = [];
        foreach ($layouts as $l) {
            $loop = isset($l['loop']) ? $l['loop'] : 1;
            $columns = isset($l['columns']) ? $l['columns'] : [1];
            $orig_columns = $columns;
            for ($i=0; $i<$loop; $i++) {
                $computed_layouts[] = $l;
                $i ++;
            }
        }

        $this->_structure = $computed_layouts;

        // Chainable
        return $this;
    }

    /**
    * @return array
    */
    public function structure()
    {
        return $this->_structure;
    }

    /**
    * Get the total number of rows
    *
    * @return integer
    */
    public function num_rows()
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
    public function row_index($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $i = 0;
        $p = 0;
        foreach ($this->_structure as $row_ident => $row) {
            $num_cells = count($row['columns']);
            $p += $num_cells;
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
    public function row_data($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $row_index = $this->row_index($position);
        if (isset($this->_structure[$row_index])) {
            return $this->_structure[$row_index];
        } else {
            return null;
        }
    }

    /**
    * Get the number of columns (the colspan) of the row at a certain position
    * @return integer|null
    */
    public function row_num_columns($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $row = $this->row_data($position);
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
    public function row_num_cells($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        // Get the data ta position
        $row = $this->row_data($position);
        $num_cells = isset($row['columns']) ? count($row['columns']) : null;
        return $num_cells;
    }

        /**
    * Get the cell index (position) of the first cell of current row
    */
    public function row_first_cell_index($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $structure = $this->structure();
        if (empty($structure)) {
            return null;
        }
        $first_list = [];
        $i = 0;
        $p = 0;
        foreach ($structure as $row) {
            $first_list[$i] = $p;
            if ($p > $position) {
                // Previous p
                return $first_list[($i-1)];
            }

            $num_cells = isset($row['columns']) ? count($row['columns']) : 0;

            $p += $num_cells;

            $i++;
        }
        return $first_list[($i-1)];
    }

    /**
    * Get the cell index in the current row
    */
    public function cell_row_index($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }
        $first = $this->row_first_cell_index($position);

        return ($position - $first);
    }

    /**
    * Get the total number of cells, in all rows
    *
    * @return integer
    */
    public function num_cells_total()
    {
        $num_cells = 0;
        foreach ($this->_structure as $row) {
            $row_cols = isset($row['columns']) ? count($row['columns']) : 0;
            $num_cells += $row_cols;
        }
        return $num_cells;
    }

    /**
    * Get the span number (in # of columns) of the current cell
    *
    * @return integer|null
    */
    public function cell_span($position = null)
    {
        $row = $this->row_data($position);
        $cell_index = $this->cell_row_index($position);

        // Cellspan (defaults to 1)
        return isset($row['columns'][$cell_index]) ? (int)$row['columns'][$cell_index] : null;
    }

    /**
    * Get the span number as a part of 12 (for bootrap-style grids)
    *
    * @return integer
    */
    public function cell_span_by12($position = null)
    {
        $num_columns =  $this->row_num_columns($position);
        if (!$num_columns) {
            return null;
        }
        return ($this->cell_span($position)*(12/$num_columns));
    }

    /**
    * Get wether or not the current cell starts a row (is the first one on the row)
    *
    * @return boolean
    */
    public function cell_starts_row($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        return ($this->cell_row_index($position) === 0);
    }

    /**
    * Get wether or not the current cell ends a row (is the last one on the row)
    *
    * @return boolean
    */
    public function cell_ends_row($position = null)
    {
        if ($position === null) {
            $position = $this->position();
        }

        $cell_num = $this->cell_row_index($position);
        $num_cells = $this->row_num_cells($position);

        return ($cell_num >= ($num_cells-1));
    }

    public function start()
    {
        return '';
    }

    public function end()
    {
        $this->_position++;
        return '';
    }
}
