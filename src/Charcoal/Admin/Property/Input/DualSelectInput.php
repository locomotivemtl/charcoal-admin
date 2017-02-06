<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

// From 'charcoal-admin'
use \Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * List Builder Input Property
 *
 * Represents a control of selectable values that can be moved between two list boxes,
 * one representing selected values and the other representing unselected ones.
 *
 * Learn more about {@link https://en.wikipedia.org/wiki/List_builder List builder} control.
 */
class DualSelectInput extends AbstractSelectableInput
{
    /**
     * Whether the lists can be filtered.
     *
     * @var mixed
     */
    protected $searchable;

    /**
     * Whether options in the right-side can be moved amongst each other.
     *
     * @var boolean
     */
    protected $reorderable;

    /**
     * Settings for {@link http://crlcu.github.io/multiselect/ Multiselect}.
     *
     * @var array
     */
    private $dualSelectOptions;

    /**
     * Retrieve the unselected options.
     *
     * @return Generator|array
     */
    public function unselectedChoices()
    {
        $choices = parent::choices();

        /* Filter the all options down to those *not* selected */
        foreach ($choices as $choice) {
            if (!$choice['selected']) {
                yield $choice;
            }
        }
    }

    /**
     * Retrieve the selected options.
     *
     * @return Generator|array
     */
    public function selectedChoices()
    {
        $val = $this->propertyVal();

        if ($val !== null) {
            $val = $this->p()->parseVal($val);

            if (!$this->p()->multiple()) {
                $val = [$val];
            }

            $choices = iterator_to_array(parent::choices());

            /* Filter the all options down to those selected */
            foreach ($val as $v) {
                if (isset($choices[$v])) {
                    yield $choices[$v];
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function searchable()
    {
        if ($this->searchable === null) {
            if (isset($this->dualSelectOptions['searchable'])) {
                $searchable = $this->dualSelectOptions['searchable'];

                $label = $this->translator()->translation([
                    'en' => 'Search…',
                    'fr' => 'Recherche…'
                ]);

                $defaultOptions = [
                    'left'  => [
                        'placeholder' => $label
                    ],
                    'right' => [
                        'placeholder' => $label
                    ]
                ];

                if (is_bool($searchable) && $searchable) {
                    $searchable = $defaultOptions;
                } elseif (is_array($searchable)) {
                    $lists = ['left', 'right'];

                    foreach ($lists as $ident) {
                        if (isset($searchable[$ident]['placeholder'])) {
                            $placeholder = $searchable[$ident]['placeholder'];
                        } elseif (isset($searchable['placeholder'])) {
                            $placeholder = $searchable['placeholder'];
                        }

                        if (isset($placeholder)) {
                            $searchable[$ident]['placeholder'] = $this->translator()->translation($placeholder);
                        } else {
                            $searchable[$ident]['placeholder'] = $label;
                        }
                    }
                } else {
                    $searchable = false;
                }
            } else {
                $searchable = false;
            }

            $this->searchable = $searchable;
        }

        return $this->searchable;
    }

    /**
     * Determine if the right-side can be manually sorted.
     *
     * @return boolean
     */
    public function reorderable()
    {
        if ($this->reorderable === null) {
            if (isset($this->dualSelectOptions['reorderable'])) {
                $this->reorderable = boolval($this->dualSelectOptions['reorderable']);
            } else {
                $this->reorderable = false;
            }
        }

        return $this->reorderable;
    }

    /**
     * Set the dual-select's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The dual-select options.
     * @return Selectinput Chainable
     */
    public function setDualSelectOptions(array $settings)
    {
        $this->dualSelectOptions = array_merge($this->defaultDualSelectOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) dual-select options.
     *
     * @param  array $settings The dual-select options.
     * @return Selectinput Chainable
     */
    public function mergeDualSelectOptions(array $settings)
    {
        $this->dualSelectOptions = array_merge($this->dualSelectOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an dual-select option.
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
        if ($this->dualSelectOptions === null) {
            $this->dualSelectOptions();
        }

        $this->dualSelectOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the dual-select's options.
     *
     * @return array
     */
    public function dualSelectOptions()
    {
        if ($this->dualSelectOptions === null) {
            $this->dualSelectOptions = $this->defaultDualSelectOptions();
        }

        return $this->dualSelectOptions;
    }

    /**
     * Retrieve the default dual-select options.
     *
     * @return array
     */
    public function defaultDualSelectOptions()
    {
        return [];
    }

    /**
     * Retrieve the dual-select's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function dualSelectOptionsAsJson()
    {
        return json_encode($this->dualSelectOptions());
    }
}
