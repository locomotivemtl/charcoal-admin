<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Number Property Input Type
 */
class NumberInput extends AbstractPropertyInput
{
     /**
      * @var integer $min
      */
    private $min = 0;
    /**
     * @var integer $max
     */
    private $max = 0;

    /**
     * @var float $step
     */
    private $step = 0;

    /**
     * @param integer $min
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setMin($min)
    {
        if (!is_integer($min)) {
            throw new InvalidArgumentException(
                'Min length needs to be an integer'
            );
        }
        $this->min = $min;
        return $this;
    }

    /**
     * @return integer
     */
    public function min()
    {
        return $this->min;
    }

    /**
     * @param integer $max
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setMax($max)
    {
        if (!is_integer($max)) {
            throw new InvalidArgumentException(
                'Max length needs to be an integer'
            );
        }
        $this->max = $max;
        return $this;
    }

    /**
     * @return integer
     */
    public function max()
    {
        return $this->max;
    }

    /**
     * @param integer $step
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setStep($step)
    {
        if (!is_float($step)) {
            throw new InvalidArgumentException(
                'Step size needs to be a float'
            );
        }
        $this->step = $step;
        return $this;
    }

    /**
     * @return integer
     */
    public function step()
    {
        return $this->step;
    }
}
