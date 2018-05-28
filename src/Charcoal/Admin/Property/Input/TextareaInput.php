<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Multi-Line Text Input Property
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
     * @var integer $minLength
     */
    private $minLength = 0;

    /**
     * @var integer $maxLength
     */
    private $maxLength = 0;

    /**
     * @param integer $cols The number of columns (html cols attribute).
     * @throws InvalidArgumentException  If the argument is not a number.
     * @return Text Chainable
     */
    public function setCols($cols)
    {
        if (!is_numeric($cols)) {
            throw new InvalidArgumentException(
                'Columns must to be a number'
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
                'Rows must to be a number'
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

    /**
     * @param integer $minLength The min length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMinLength($minLength)
    {
        if (!is_numeric($minLength)) {
            throw new InvalidArgumentException(
                'Minimum length needs to be an integer'
            );
        }
        $this->minLength = (int)$minLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function minLength()
    {
        return $this->minLength;
    }

    /**
     * @param integer $maxLength The max length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMaxLength($maxLength)
    {
        if (!is_numeric($maxLength)) {
            throw new InvalidArgumentException(
                'Maximum length needs to be an integer'
            );
        }
        $this->maxLength = (int)$maxLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function maxLength()
    {
        return $this->maxLength;
    }
}
