<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 *
 */
class TextareaInput extends AbstractPropertyInput
{
    /**
     * @var integer $cols
     */
    private $cols;

    /**
     * @var integer $rows
     */
    private $rows;

    /**
     * @param integer $cols The number of columns (html cols attribute).
     * @throws InvalidArgumentException  If the argument is not a number.
     * @return Text Chainable
     */
    public function setCols($cols)
    {
        if (!is_numeric($cols)) {
            throw new InvalidArgumentException(
                'Accept needs to be a string'
            );
        }
        $this->cols = (int)$cols;
        return $this;
    }

    /**
     * @return integer
     */
    public function cols()
    {
        return $this->cols;
    }

    /**
     * @param integer $rows The number of rows (html rows attribute).
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setRows($rows)
    {
        if (!is_numeric($rows)) {
            throw new InvalidArgumentException(
                'Accept needs to be a string'
            );
        }
        $this->rows = (int)$rows;
        return $this;
    }

    /**
     * @return integer
     */
    public function rows()
    {
        return $this->rows;
    }
}
