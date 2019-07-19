<?php

namespace Charcoal\Admin\Property\Input\Selectize;

use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\ObjectProperty;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Tags Input Selectize
 *
 * The HTML form control can be either an `<input type="text">` (for multiple values)
 * or a `<select>` (single value).
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
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * The input name should always be the property's ident.
     *
     * @return string
     */
    public function inputName()
    {
        if ($this->inputName) {
            $name = $this->inputName;
        } else {
            $name = $this->propertyIdent();
        }

        if ($this->p()['l10n']) {
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
     * @return \Generator
     */
    public function choices()
    {
        if ($this->p()['allowNull'] && !$this->p()['multiple']) {
            $prepend = $this->parseChoice('', $this->emptyChoice());

            yield $prepend;
        }

        $choices = parent::choices();

        /* Pass along the Generator from the parent method [^1] */
        foreach ($choices as $choice) {
            yield $choice;
        }
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
        $this->selectizeOptions = array_merge(
            $this->defaultSelectizeOptions(),
            $this->parseSelectizeOptions($settings)
        );

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
        $this->selectizeOptions = array_merge(
            $this->selectizeOptions,
            $this->parseSelectizeOptions($settings)
        );

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

        if ($key === 'options') {
            $val = $this->parseSelectizeAvailableChoices($val);
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
        $options = [];

        if (isset($metadata['data']['selectize_options'])) {
            $options = $metadata['data']['selectize_options'];
            $options = $this->parseSelectizeOptions($options);
        }

        if ($this->property() instanceof ObjectProperty) {
            if (isset($options['options'])) {
                $options['options'] = array_merge($options['options'], $this->selectizeVal());
            } else {
                $options['options'] = $this->selectizeVal();
            }
        }

        return $options;
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

    /**
     * Retrieve the default object-to-choice data map.
     *
     * @return array
     */
    public function defaultChoiceObjMap()
    {
        return [
            'value' => 'id',
            'text'  => 'name:title:label:id',
            'color' => 'color'
        ];
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        $prop = $this->property();

        $data = [
            // Selectize Control
            'title'                    => (string)$prop['label'],
            'copy_items'               => $this->allowClipboardCopy(),

            'selectize_selector'       => '#'.$this->inputId(),
            'selectize_options'        => $this->selectizeOptions(),

            // Base Property
            'required'                 => $this->required(),
            'l10n'                     => $this->property()['l10n'],
            'multiple'                 => $this->multiple(),
            'multiple_separator'       => $this->property()->multipleSeparator(),
            'multiple_options'         => $this->property()['multipleOptions'],
        ];

        if ($prop instanceof ObjectProperty) {
            if ($prop->objType()) {
                $data['pattern']  = $prop['pattern'];
                $data['obj_type'] = $prop['objType'];
            }
        }

        return $data;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(sprintf(
                'Model Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->modelFactory;
    }

    /**
     * Parse the selectize picker's options.
     *
     * @param  array $settings The selectize picker options.
     * @return array Returns the parsed options.
     */
    protected function parseSelectizeOptions(array $settings)
    {
        return $settings;
    }

    /**
     * Set an object model factory.
     *
     * @param  FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    private function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
     * @return self
     */
    private function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;

        return $this;
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
            $val = $prop->parseVal($val);

            if (!$prop->multiple()) {
                $val = (array)$val;
            }

            if ($prop instanceof ObjectProperty) {
                $model = $this->modelFactory()->get($prop['objType']);
                if (!$model->source()->tableExists()) {
                    return $choices;
                }
                $loader = $this->collectionLoader();
                $loader->reset()->setModel($model);
                $loader->addFilter([
                    'property' => $model->key(),
                    'operator' => 'IN',
                    'value'      => $val
                ]);
                $collection = $loader->load();
                $choices = [];
                foreach ($collection as $obj) {
                    $choices[] = $this->mapObjToChoice($obj);
                }
            } else {
                foreach ($val as $v) {
                    $choices[] = [
                        'value' => $v,
                        'text'  => $v
                    ];
                }
            }
        }

        return $choices;
    }
}
