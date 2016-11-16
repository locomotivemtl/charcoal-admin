<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Select Options Input Property
 *
 * > The HTML _select_ (`<select>`) element represents a control that presents a menu of options.
 * — {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/select/}
 */
class SelectInput extends AbstractSelectableInput
{
    /**
     * Settings for {@link http://silviomoreto.github.io/bootstrap-select/ Bootstrap Select}.
     *
     * @var array
     */
    private $selectOptions;

    /**
     * Retrieve the selectable options.
     *
     * @todo [^1]: With PHP7 we can simply do `yield from $choices;`.
     * @return Generator|array
     */
    public function choices()
    {
        if ($this->p()->allowNull() && !$this->p()->multiple()) {
            $prepend = $this->emptyChoice();

            yield $prepend;
        }

        $choices = parent::choices();

        /* Pass along the Generator from the parent method [^1] */
        foreach ($choices as $choice) {
            yield $choice;
        }
    }

    /**
     * Retrieve a blank choice.
     *
     * @return array
     */
    protected function emptyChoice()
    {
        $label = $this->placeholder();

        return [
            'value'   => '',
            'label'   => $label,
            'title'   => $label,
            'subtext' => ''
        ];
    }

    /**
     * Set the select picker's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The select picker options.
     * @return Selectinput Chainable
     */
    public function setSelectOptions(array $settings)
    {
        $this->selectOptions = array_merge($this->defaultSelectOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) select picker options.
     *
     * @param  array $settings The select picker options.
     * @return Selectinput Chainable
     */
    public function mergeSelectOptions(array $settings)
    {
        $this->selectOptions = array_merge($this->selectOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an select picker option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return Selectinput Chainable
     */
    public function addSelectOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->selectOptions === null) {
            $this->selectOptions();
        }

        $this->selectOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the select picker's options.
     *
     * @return array
     */
    public function selectOptions()
    {
        if ($this->selectOptions === null) {
            $this->selectOptions = $this->defaultSelectOptions();
        }

        return $this->selectOptions;
    }

    /**
     * Retrieve the default select picker options.
     *
     * @return array
     */
    public function defaultSelectOptions()
    {
        return [];
    }

    /**
     * Retrieve the select picker's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function selectOptionsAsJson()
    {
        return json_encode($this->selectOptions());
    }
}
