<?php

namespace Charcoal\Admin\Property;

use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewInterface;
use Closure;
use DateTimeInterface;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

/**
 * Selectable input properties provide an array of choices to choose from.
 */
abstract class AbstractSelectableInput extends AbstractPropertyInput implements
    SelectableInputInterface
{
    /**
     * The empty (NULL) option.
     *
     * @var array
     */
    protected $emptyChoice;

    /**
     * The object-to-choice map.
     *
     * @var array
     */
    protected $choiceObjMap;

    /**
     * Parsed property value
     * Execute only once.
     *
     * @var mixed
     */
    protected $parsedVal = [];

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

        if (isset($choice['label'])) {
            $choice['label'] = (string)$this->translator()->translation($choice['label']);
        } else {
            $choice['label'] = (string)$this->translator()->translation($choice['value']);
        }

        $choice['checked'] = $this->isChoiceSelected($choice);
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
        if ($value instanceof ModelInterface) {
            $value = $value->id();
        }

        if ($value instanceof Closure) {
            $value = $value();
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d-H:i');
        }

        if (method_exists($value, '__toString')) {
            $value = strval($value);
        }

        return $value;
    }

    /**
     * Keep parsed val in memory per lang
     *
     * @return mixed Parsed property value.
     */
    public function parsedVal()
    {
        if (!isset($this->parsedVal[$this->lang()])) {
            $val = $this->propertyVal();

            if ($val === null) {
                return null;
            }
            $val = $this->p()->parseVal($val);

            // Could be Translation instance
            // Could be array
            if (isset($val[$this->lang()])) {
                $val = $val[$this->lang()];
            }

            // Doing this in the parseVal method of abstract property
            // was causing multiple && l10n properties not to save.
            if (!is_array($val) && $this->p()->multiple()) {
                $val = explode($this->p()->multipleSeparator(), $val);
            }

            $this->parsedVal[$this->lang()] = $val;
        }
        return $this->parsedVal[$this->lang()];
    }

    /**
     * Determine if the provided option is a selected value.
     *
     * @param  mixed $choice The choice to check.
     * @return boolean
     */
    public function isChoiceSelected($choice)
    {
        $val = $this->parsedVal();

        if ($val === null) {
            return false;
        }

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
     * Set the empty option's structure.
     *
     * @param  array|string $choice The property value.
     * @throws InvalidArgumentException If the choice structure is invalid.
     * @return PropertyInputInterface Chainable
     */
    public function setEmptyChoice($choice)
    {
        if (is_string($choice) || ($choice instanceof Translation)) {
            $choice = [
                'label' => $choice
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
     * @return array
     */
    protected function defaultEmptyChoice()
    {
        return [
            'value' => '',
            'label' => $this->translator()->translation('— None —')
        ];
    }

    /**
     * Render the given object.
     *
     * @param  ModelInterface|ViewableInterface $obj  The object or view to render as a label.
     * @param  string|null                      $prop Optional. The render pattern to render.
     * @throws InvalidArgumentException If the prop is not a string.
     * @return mixed|string
     */
    protected function renderChoiceObjMap($obj, $prop)
    {
        if (!is_string($prop)) {
            throw new InvalidArgumentException(
                'The render pattern must be a string.'
            );
        }

        if ($prop === '') {
            return '';
        }

        if (strpos($prop, '{{') === false) {
            if (isset($obj[$prop])) {
                return $this->parseChoiceVal($obj[$prop]);
            }
        }

        if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
            return $obj->renderTemplate($prop);
        } else {
            $callback = function ($matches) use ($obj) {
                $prop = trim($matches[1]);
                if (isset($obj[$prop])) {
                    return $this->parseChoiceVal($obj[$prop]);
                }
                return [];
            };

            return preg_replace_callback('~\{\{\s*(.*?)\s*\}\}~i', $callback, $prop);
        }
    }

    /**
     * Convert the given object into a choice structure.
     *
     * @param  array|\ArrayAccess|ModelInterface $obj The object to map to a choice.
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
                $choice[$key] = $this->renderChoiceObjMap($obj, $prop);
                break;
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
        $this->choiceObjMap = array_replace($this->defaultChoiceObjMap(), $map);

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
            $this->choiceObjMap = $this->defaultChoiceObjMap();
        }

        return $this->choiceObjMap;
    }

    /**
     * Retrieve the property input name without `[]` as fallback.
     * This allows to submit empty value from select input while preventing
     * submitting the fallback when the property is multiple.
     *
     * @return string
     */
    public function inputNameFallback()
    {
        $name = $this->inputName();
        if (substr($name, -2, 2) === '[]') {
            return substr($name, 0, -2);
        }

        return $name;
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

    /**
     * Return a json
     *
     * @return string
     */
    public function choiceObjMapAsJson()
    {
        return json_encode($this->choiceObjMap());
    }
}
