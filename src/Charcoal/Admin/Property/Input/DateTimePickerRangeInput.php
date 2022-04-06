<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

use Charcoal\Admin\Property\Input\DateTimePickerInput;

/**
 * Date/Time Picker Input Property
 */
class DateTimePickerRangeInput extends DateTimePickerInput
{
    const DEFAULT_JS_FORMAT = 'YYYY-MM-DD';

    /**
     * Retrieve the default color picker options.
     *
     * @return array
     */
    public function defaultPickerOptions()
    {
        $date = null;

        if ($this->inputVal() !== '') {
            $date = new \DateTime($this->inputVal());
        }

        return [
            'format'      => self::DEFAULT_JS_FORMAT,
            'defaultDate' => $date ? $date->format(\DateTime::ISO8601) : null
        ];
    }
}
