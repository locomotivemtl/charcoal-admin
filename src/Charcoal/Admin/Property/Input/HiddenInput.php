<?php

namespace Charcoal\Admin\Property\Input;

/**
 * Hidden Input Property
 */
class HiddenInput extends TextInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'hidden';
    }
}
