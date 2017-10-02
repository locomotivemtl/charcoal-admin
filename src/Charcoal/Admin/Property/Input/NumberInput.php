<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Number Property Input Type
 */
class NumberInput extends AbstractPropertyInput
{
    /**
     * The minimum numeric value allowed.
     *
     * @var integer|float|null
     */
    private $min;

    /**
     * The maximum numeric value allowed.
     *
     * @var integer|float|null
     */
    private $max;

    /**
     * Limit the increments at which a numeric value can be set.
     *
     * Note: It can be the string "any" or a positive floating point number.
     *
     * @var string|integer|float|null
     */
    private $step;

    /**
     * @param  mixed $min The minimum.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMin($min)
    {
        if ($min === null || $min === '') {
            $this->min = null;
            return $this;
        }

        if (!is_numeric($min)) {
            throw new InvalidArgumentException(
                'Minimum value needs to be a number'
            );
        }

        $this->min = $min + 0;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasMin()
    {
        return !(empty($this->min) && !is_numeric($this->min));
    }

    /**
     * @return integer|float|null
     */
    public function min()
    {
        return $this->min;
    }

    /**
     * @param  mixed $max The maximum.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMax($max)
    {
        if ($max === null || $max === '') {
            $this->max = null;
            return $this;
        }

        if (!is_numeric($max)) {
            throw new InvalidArgumentException(
                'Maximum value needs to be a number'
            );
        }

        $this->max = $max + 0;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasMax()
    {
        return !(empty($this->max) && !is_numeric($this->max));
    }

    /**
     * @return integer|float|null
     */
    public function max()
    {
        return $this->max;
    }

    /**
     * @param  mixed $step The step attribute.
     * @throws InvalidArgumentException If the value is not a number.
     * @return Text Chainable
     */
    public function setStep($step)
    {
        if ($step === null || $step === '') {
            $this->step = null;
            return $this;
        }

        if ($step === 'any') {
            $this->step = $step;
            return $this;
        }

        if (!is_numeric($step)) {
            throw new InvalidArgumentException(
                'Step size needs to be a number or the string "any"'
            );
        }

        $this->step = $step + 0;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasStep()
    {
        return !(empty($this->step) && !is_numeric($this->step));
    }

    /**
     * @return string|integer|float|null
     */
    public function step()
    {
        return $this->step;
    }
}
