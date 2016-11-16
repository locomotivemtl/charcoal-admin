<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Radio Button Input Property
 *
 * The HTML _radio button_ (`<input type="radio">`) input element represents
 * a control to select a _single value_ from a list of different values.
 * — {@link https://www.w3.org/wiki/HTML/Elements/input/radio}
 */
class RadioInput extends AbstractSelectableInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'radio';
    }

    /**
     * Never accept multiple values.
     *
     * @return boolean
     */
    public function multiple()
    {
        return false;
    }

    /**
     * Prepare a single selectable option for output.
     *
     * @param  string|integer $ident  The choice key.
     * @param  array|object   $choice The choice structure.
     * @return array|null
     */
    protected function parseChoice($ident, $choice)
    {
        $choice = parent::parseChoice($ident, $choice);

        $choice['inputId'] = $this->generateInputId();

        return $choice;
    }
}
