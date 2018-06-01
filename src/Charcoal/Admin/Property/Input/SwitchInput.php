<?php

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Switch Input Property
 *
 * For displaying checkboxes and radio buttons as toggle switches.
 */
class SwitchInput extends AbstractPropertyInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'checkbox';
    }

    /**
     * @return boolean
     */
    public function checked()
    {
        return !!$this->inputVal();
    }

    /**
     * @return integer
     */
    public function value()
    {
        return $this->inputVal() ? 1 : 0;
    }
}
