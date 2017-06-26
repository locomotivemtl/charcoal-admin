<?php

namespace Charcoal\Admin\Property\Input\Selectize;

use Charcoal\Admin\Property\Input\SelectInput;

/**
 * Tags Input Property
 *
 * The HTML form control can be either an `<input type="text">` (for multiple values)
 * or a `<select>` (single value).
 */
class SearchInput extends SelectInput
{
    /**
     * @var array
     */
    private $selectizeOptions = [];

    /**
     * Plugin options
     * @return array Selectize plugin options (js).
     */
    public function selectizeOptions()
    {
        return $this->selectizeOptions;
    }


    /**
     * Set the selectize picker's options.
     *
     * This method overwrites existing helpers.
     *
     * @param  array $settings The selectize picker options.
     * @return TagsInput Chainable
     */
    public function setSelectizeOptions(array $settings)
    {
        $this->selectizeOptions = $settings;

        return $this;
    }


    /**
     * Retrieve the selectize picker's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function selectizeOptionsAsJson()
    {
        return json_encode($this->selectizeOptions());
    }
}
