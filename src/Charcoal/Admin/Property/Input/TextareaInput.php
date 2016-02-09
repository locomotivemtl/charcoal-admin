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
     * @param integer $cols
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setCols($cols)
    {
        if (!is_integer($cols)) {
            throw new InvalidArgumentException(
                'Accept needs to be a string'
            );
        }
        $this->cols = $cols;
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
     * @param integer $rows
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setRows($rows)
    {
        if (!is_integer($rows)) {
            throw new InvalidArgumentException(
                'Accept needs to be a string'
            );
        }
        $this->rows = $rows;
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
