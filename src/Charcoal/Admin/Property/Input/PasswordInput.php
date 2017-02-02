<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Password Property
 */
class PasswordInput extends TextInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'password';
    }

}
