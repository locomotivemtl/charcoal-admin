<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
*
*/
class DatetimepickerInput extends AbstractPropertyInput
{
    /**
    * Format `DateTime` to string.
    *
    * @todo   Adapt for l10n
    * @return string
    */
    public function date_format()
    {
        return $this->p()->format();
    }

    /**
    * Format `DateTime` to string.
    *
    * @todo   Adapt for l10n
    * @return string
    */
    public function display_val()
    {
        return $this->p()->val()->format($this->date_format());
    }
}
