<?php

namespace Charcoal\Admin\Property\Input\Selectize;

use \RuntimeException;
use \InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\ObjectProperty;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Tags Input Property
 *
 * The HTML form control can be either an `<input type="text">` (for multiple values) or a `<select>` (single value).
 */
class TagsInput extends AbstractSelectableInput
{
    /**
     * Settings for {@link http://selectize.github.io/selectize.js/ Selectize.js}
     *
     * @var array
     */
    private $selectizeOptions;

    /**
     * Whether to show a button to copy items to clipboard.
     *
     * @var boolean
     */
    protected $allowClipboardCopy;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * Set an object model factory.
     *
     * @param  FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(
                sprintf('Model Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->modelFactory;
    }

    /**
     * The input name should always be the property's ident.
     *
     * @return string
     */
    public function inputName()
    {
        $name = $this->propertyIdent();

        if ($this->p()->l10n()) {
            $name .= '['.$this->lang().']';
        }

        return $name;
    }

    /**
     * Retrieve the selectable options.
     *
     * Note: This method is also featured in {@see \Charcoal\Admin\Property\Input\SelectInput}.
     *
     * @todo   [^1]: With PHP7 we can simply do `yield from $choices;`.
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
     * Note: This method is also featured in {@see \Charcoal\Admin\Property\Input\SelectInput}.
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
     * Show/hide the "Copy to Clipboard" button.
     *
     * @param  boolean $flag Show (TRUE) or hide (FALSE) the copy button.
     * @return UiItemInterface Chainable
     */
    public function setAllowClipboardCopy($flag)
    {
        $this->allowClipboardCopy = !!$flag;

        return $this;
    }

    /**
     * Determine if the property allows "Copy to Clipboard".
     *
     * @return boolean
     */
    public function allowClipboardCopy()
    {
        return $this->allowClipboardCopy;
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
        $this->selectizeOptions = array_merge($this->defaultSelectizeOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) selectize picker options.
     *
     * @param  array $settings The selectize picker options.
     * @return TagsInput Chainable
     */
    public function mergeSelectizeOptions(array $settings)
    {
        $this->selectizeOptions = array_merge($this->selectizeOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an selectize picker option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self Chainable
     */
    public function addSelectizeOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->selectizeOptions === null) {
            $this->selectizeOptions();
        }

        $this->selectizeOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the selectize picker's options.
     *
     * @return array
     */
    public function selectizeOptions()
    {
        if ($this->selectizeOptions === null) {
            $this->selectizeOptions = $this->defaultSelectizeOptions();
        }

        return $this->selectizeOptions;
    }

    /**
     * Retrieve the default selectize picker options.
     *
     * @return array
     */
    public function defaultSelectizeOptions()
    {
        $metadata = $this->metadata();
        $options  = [];

        if (isset($metadata['data']['selectize_options'])) {
            $options = $metadata['data']['selectize_options'];
        }

        if (isset($options['options'])) {
            $options['options'] = array_merge($options['options'], $this->selectizeVal());
        } else {
            $options['options'] = $this->selectizeVal();
        }

        return $options;
    }

    /**
     * Convert the given value into selectize picker choices.
     *
     * @param  mixed $val     The value to parse into selectize choices.
     * @param  array $options Optional structure options.
     * @return array
     */
    private function selectizeVal($val = null, array $options = [])
    {
        /** @todo Find a use for this */
        unset($options);

        $choices = [];

        if ($val === null) {
            $val = $this->propertyVal();
        }

        if ($val !== null) {
            $prop = $this->property();
            $val  = $prop->parseVal($val);

            if (!$prop->multiple()) {
                $val = [ $val ];
            }

            if ($prop instanceof ObjectProperty) {
                $objType = $prop->objType();
                foreach ($val as $v) {
                    $obj = $this->modelFactory()->create($objType);
                    $obj->load($v);
                    if ($obj->id()) {
                        $choices[] = [
                            'value' => $obj->id(),
                            'text'  => (string)$obj->name(),
                            'color' => method_exists($obj, 'color') ? $obj->color() : null
                        ];
                    }
                }
            } else {
                foreach ($val as $v) {
                    $choices[] = [
                        'value' => $v,
                        'text'  => $v,
                        'color' => null
                    ];
                }
            }
        }

        return $choices;
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
