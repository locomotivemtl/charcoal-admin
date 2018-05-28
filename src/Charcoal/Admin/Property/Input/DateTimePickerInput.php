<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Date/Time Picker Input Property
 */
class DateTimePickerInput extends AbstractPropertyInput
{
    const DEFAULT_JS_FORMAT = 'YYYY-MM-DD HH:mm:ss';

    /**
     * Settings for {@link https://eonasdan.github.io/bootstrap-datetimepicker/ Bootstrap Datepicker}.
     *
     * @var array
     */
    private $pickerOptions;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'datetime-local';
    }

    /**
     * Set the color picker's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The color picker options.
     * @return ColorpickerInput Chainable
     */
    public function setPickerOptions(array $settings)
    {
        $this->pickerOptions = array_merge($this->defaultPickerOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) color picker options.
     *
     * @param  array $settings The color picker options.
     * @return ColorpickerInput Chainable
     */
    public function mergePickerOptions(array $settings)
    {
        $this->pickerOptions = array_merge($this->pickerOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an color picker option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return ColorpickerInput Chainable
     */
    public function addPickerOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->pickerOptions === null) {
            $this->pickerOptions();
        }

        $this->pickerOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the color picker's options.
     *
     * @return array
     */
    public function pickerOptions()
    {
        if ($this->pickerOptions === null) {
            $this->pickerOptions = $this->defaultPickerOptions();
        }

        return $this->pickerOptions;
    }

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
            // 'allowInputToggle' => true,
            'format'           => self::DEFAULT_JS_FORMAT,
            'defaultDate'      => $date ? $date->format(\DateTime::ISO8601) : null
        ];
    }

    /**
     * Retrieve the color picker's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function pickerOptionsAsJson()
    {
        return json_encode($this->pickerOptions());
    }
}
