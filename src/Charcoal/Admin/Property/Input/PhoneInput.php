<?php

namespace Charcoal\Admin\Property\Input;

use Charcoal\Admin\Property\Input\TextInput;

/**
 * Telephone Number Input
 *
 * This method should be renamed to `TelInput` (@todo).
 */
class PhoneInput extends TextInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'tel';
    }
}
