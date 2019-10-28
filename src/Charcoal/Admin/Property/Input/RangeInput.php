<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

/**
 * Range Property Input Type
 */
class RangeInput extends NumberInput
{
    /**
     * Whether to display the range value.
     *
     * @var boolean
     */
    private $showRangeValue = false;

    /**
     * Where to display the range value ("prefix", "suffix").
     *
     * @var string|null
     */
    private $rangeValueLocation;

    /**
     * Show/Hide the property's value.
     *
     * @param  boolean $show Show or hide the range value.
     * @return self
     */
    public function setShowRangeValue($show)
    {
        $this->showRangeValue = (bool)$show;
        $this->setRangeValueLocation($show);

        return $this;
    }

    /**
     * Determine if the property's value should be displayed.
     *
     * @return boolean
     */
    public function showRangeValue()
    {
        return $this->showRangeValue;
    }

    /**
     * Set where the property value should be displayed.
     *
     * @param  mixed $location The location to display the range value.
     *     Either "prefix", "suffix", or a custom CSS query selector.
     *     If the custom location is not a fullly-qualified ID or class name
     *     CSS selector, the query selector lookup will be done with the input's
     *     ID prefix (e.g., "my_range_output" → `#input_5db6fc900736b_my_range_output`).
     * @throws InvalidArgumentException If the show flag is invalid.
     * @return self
     */
    public function setRangeValueLocation($location)
    {
        switch ($location) {
            case false:
            case null:
                $this->rangeValueLocation = null;
                return $this;

            case true:
                $this->rangeValueLocation = 'suffix';
                return $this;
        }

        // Support custom locations
        if (is_string($location)) {
            $this->rangeValueLocation = $location;
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid range value location: %s ',
            (is_object($location) ? get_class($location) : gettype($location))
        ));
    }

    /**
     * Retrieve where the property value should be displayed.
     *
     * @return boolean
     */
    public function rangeValueLocation()
    {
        return $this->rangeValueLocation;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [
            // Base Control
            'input_name'           => $this->inputName(),

            // Range Control
            'show_range_value'     => $this->showRangeValue(),
            'range_value_location' => $this->rangeValueLocation(),
        ];
    }
}
