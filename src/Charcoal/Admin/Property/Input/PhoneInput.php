<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\Input\TextInput;

/**
 * Telephone Input
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
