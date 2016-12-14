<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Switch Input Property
 *
 * For displaying checkboxes and radio buttons as toggle switches.
 */
class SwitchInput extends AbstractPropertyInput
{
    /**
     * Settings for {@link http://www.bootstrap-switch.org Bootstrap Switch}.
     *
     * @var array
     */
    private $switchOptions;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'checkbox';
    }

    /**
     * @return boolean
     */
    public function checked()
    {
        return !!$this->inputVal();
    }

    /**
     * @return integer
     */
    public function value()
    {
        return $this->inputVal() ? 1 : 0;
    }

    /**
     * Set the switch's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The switch options.
     * @return Switchinput Chainable
     */
    public function setSwitchOptions(array $settings)
    {
        $this->switchOptions = array_merge($this->defaultSwitchOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) switch options.
     *
     * @param  array $settings The switch options.
     * @return Switchinput Chainable
     */
    public function mergeSwitchOptions(array $settings)
    {
        $this->switchOptions = array_merge($this->switchOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an switch option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return Switchinput Chainable
     */
    public function addSwitchOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->switchOptions === null) {
            $this->switchOptions();
        }

        $this->switchOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the switch's options.
     *
     * @return array
     */
    public function switchOptions()
    {
        if ($this->switchOptions === null) {
            $this->switchOptions = $this->defaultSwitchOptions();
        }

        return $this->switchOptions;
    }

    /**
     * Retrieve the default switch options.
     *
     * @return array
     */
    public function defaultSwitchOptions()
    {
        return [];
    }

    /**
     * Retrieve the switch's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function switchOptionsAsJson()
    {
        return json_encode($this->switchOptions());
    }
}