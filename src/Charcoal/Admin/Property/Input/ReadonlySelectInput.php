<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

/**
 * Readonly Select Options Input Property
 */
class ReadonlySelectInput extends SelectInput
{
    /**
     * Retrieve the selected value and display it.
     *
     * @return string
     */
    public function displayVal()
    {
        foreach ($this->choices() as $choice) {
            if ($this->isChoiceSelected($choice)) {
                return $choice['label'];
                break;
            }
        }
    }
}
