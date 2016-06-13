<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Checkbox Input Property
 *
 * The HTML _check box_ (`<input type="checkbox">`) input element represents
 * a control to select an array of different values.
 * — {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/checkbox}
 */
class CheckboxInput extends AbstractSelectableInput
{
    /**
     * Always accept multiple values.
     *
     * @return boolean
     */
    public function multiple()
    {
        return true;
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
