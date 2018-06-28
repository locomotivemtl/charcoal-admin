<?php

namespace Charcoal\Admin\Property\Display;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Choice Display Property
 */
class ChoiceDisplay extends AbstractPropertyDisplay
{
    /**
     * The empty (NULL) option.
     *
     * @var array|null
     */
    protected $emptyChoice;

    /**
     * Set the empty option's structure.
     *
     * @see    \Charcoal\Admin\Property\AbstractSelectableInput::setEmptyChoice()
     * @param  array|string $choice The property value.
     * @throws InvalidArgumentException If the choice structure is invalid.
     * @return PropertyInputInterface Chainable
     */
    public function setEmptyChoice($choice)
    {
        if (is_string($choice) || ($choice instanceof Translation)) {
            $choice = [
                'label' => $choice,
            ];
        }

        if (is_array($choice)) {
            $choice = array_replace_recursive(
                $this->defaultEmptyChoice(),
                $choice
            );
        } else {
            throw new InvalidArgumentException(sprintf(
                'Empty choice must be an array, received %s',
                (is_object($choice) ? get_class($choice) : gettype($choice))
            ));
        }

        if (!$choice['label'] instanceof Translation) {
            $choice['label'] = $this->translator()->translation($choice['label']);
        }

        $this->emptyChoice = $choice;

        return $this;
    }

    /**
     * Retrieve the empty option structure.
     *
     * @see    \Charcoal\Admin\Property\AbstractSelectableInput::emptyChoice()
     * @return array|null
     */
    public function emptyChoice()
    {
        if ($this->emptyChoice === null) {
            return $this->defaultEmptyChoice();
        }

        return $this->emptyChoice;
    }

    /**
     * Retrieve the default empty option structure.
     *
     * @see    \Charcoal\Admin\Property\AbstractSelectableInput::defaultEmptyChoice()
     * @return array
     */
    protected function defaultEmptyChoice()
    {
        return [
            'value' => '',
            'label' => '',
        ];
    }

    /**
     * Retrieve display value.
     *
     * @return string
     */
    public function displayVal()
    {
        $prop = $this->p();
        $val  = $this->propertyVal();

        if ($val !== null) {
            return $prop->displayVal($val);
        }

        if ($prop->allowNull()) {
            $nil = $this->emptyChoice();
            return (string)$nil['label'];
        }

        return '';
    }
}
