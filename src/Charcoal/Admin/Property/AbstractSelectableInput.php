<?php

namespace Charcoal\Admin\Property;

/**
 * Selectable input properties provide an array of choices to choose from.
 */
abstract class AbstractSelectableInput extends AbstractPropertyInput implements
    SelectableInputInterface
{
    /**
     * The object-to-choice map.
     *
     * @var array
     */
    protected $choiceObjMap;

    /**
     * Retrieve the selectable options.
     *
     * @return Generator|array
     */
    public function choices()
    {
        $choices = $this->p()->choices();

        foreach ($choices as $ident => $choice) {
            $choice = $this->parseChoice($ident, $choice);

            if (isset($choice['active']) && $choice['active'] === false) {
                continue;
            }

            if (!is_array($choice)) {
                continue;
            }

            yield $ident => $choice;
        }
    }

    /**
     * Prepare a single selectable option for output.
     *
     * @param  string|integer $ident  The choice key.
     * @param  array|object   $choice The choice structure.
     * @return array|null
     */
    protected function parseChoice($ident, $choice)
    {
        if (!isset($choice['value'])) {
            $choice['value'] = $ident;
        }

        if (!isset($choice['label'])) {
            $choice['label'] = ucwords(strtolower(str_replace('_', ' ', $ident)));
        }

        if (!isset($choice['title'])) {
            $choice['title'] = $choice['label'];
        }

        $choice['checked']  = $this->isChoiceSelected($choice);
        $choice['selected'] = $choice['checked'];

        return $choice;
    }

    /**
     * Convert the value to a scalar.
     *
     * @param  mixed $value A value to parse.
     * @return mixed
     */
    protected function parseChoiceVal($value)
    {
        if ($value instanceof \Closure) {
            $value = $value();
        }

        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d-H:i');
        }

        if ($value instanceof \Charcoal\Translation\TranslationString) {
            $value = $value->fallback();
        }

        if (method_exists($value, '__toString')) {
            $value = strval($value);
        }

        return $value;
    }

    /**
     * Determine if the provided option is a selected value.
     *
     * @param  mixed $choice The choice to check.
     * @return boolean
     */
    public function isChoiceSelected($choice)
    {
        $val = $this->propertyVal();

        if ($val === null) {
            return false;
        }

        $val = $this->p()->parseVal($val);

        if (isset($choice['value'])) {
            $choice = $choice['value'];
        }

        if ($this->p()->multiple()) {
            return in_array($choice, $val);
        } else {
            return $choice == $val;
        }
    }

    /**
     * Convert the given object into a choice structure.
     *
     * @param  array|\ArrayAccess $obj The object to map to a choice.
     * @return array
     */
    public function mapObjToChoice($obj)
    {
        $map = $this->choiceObjMap();

        $choice = [];
        foreach ($map as $key => $props) {
            $choice[$key] = null;

            $props = explode(':', $props);
            foreach ($props as $prop) {
                if (isset($obj[$prop])) {
                    $choice[$key] = $this->parseChoiceVal($obj[$prop]);
                    break;
                }
            }
        }

        return $choice;
    }

    /**
     * Set the object-to-choice data map.
     *
     * @param  array $map Model-to-choice mapping.
     * @return TagsInput Chainable
     */
    public function setChoiceObjMap(array $map)
    {
        $this->choiceObjMap = $map;

        return $this;
    }

    /**
     * Retrieve the object-to-choice data map.
     *
     * @return array Returns a data map to abide.
     */
    public function choiceObjMap()
    {
        if ($this->choiceObjMap === null) {
            return $this->defaultChoiceObjMap();
        }

        return $this->choiceObjMap;
    }

    /**
     * Retrieve the default object-to-choice data map.
     *
     * @return array
     */
    public function defaultChoiceObjMap()
    {
        return [
            'value' => 'id',
            'label' => 'name:title:label'
        ];
    }
}