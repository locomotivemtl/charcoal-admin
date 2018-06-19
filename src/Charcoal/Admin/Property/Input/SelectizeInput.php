<?php

namespace Charcoal\Admin\Property\Input;

use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Collection;
use Charcoal\Loader\CollectionLoader;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalCollection;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\ObjectProperty;
use Charcoal\Property\SelectablePropertyInterface;
use Charcoal\Property\AbstractProperty;

// From 'charcoal-admin'
use Charcoal\Admin\Service\SelectizeRenderer;
use Charcoal\Admin\Property\HierarchicalObjectProperty;

/**
 * Tags Input Property
 *
 * The HTML form control can be either an `<input type="text">` (for multiple values)
 * or a `<select>` (single value).
 */
class SelectizeInput extends SelectInput
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
     * Should the object be loaded in deferred mode.
     *
     * @var boolean
     */
    private $deferred;

    /**
     * Whether to show a button to allow update items.
     *
     * @var boolean
     */
    protected $allowUpdate;

    /**
     * Whether to show a button to allow item create.
     *
     * @var boolean
     */
    protected $allowCreate;

    /**
     * The form idents to use while creating objects through Selectize.
     *
     * Can either be a single value,
     * or an array of values for these idents
     *  - 'create'
     *  - 'update'
     *
     * @var mixed
     */
    private $formIdent;

    /**
     * Check used to parse multi Choice map against the obj properties.
     *
     * @var boolean
     */
    protected $isChoiceObjMapFinalized = false;

    /**
     * @var array
     */
    private $selectizeTemplates;

    /**
     * @var SelectizeRenderer
     */
    private $selectizeRenderer;

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function modelFactory()
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
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     * @return CollectionLoader
     */
    protected function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new RuntimeException(sprintf(
                'Collection Loader is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->collectionLoader;
    }

    /**
     * Retrieve the selectable options.
     *
     * Note: This method is also featured in {@see \Charcoal\Admin\Property\Input\SelectInput}.
     *
     * @todo   [^1]: With PHP7 we can simply do `yield from $choices;`.
     * @return \Generator|array
     */
    public function choices()
    {
        if ($this->p()->allowNull() && !$this->p()->multiple()) {
            $prepend = $this->parseChoice('', $this->emptyChoice());

            yield $prepend;
        }

        $choices = $this->selectizeVal($this->p()->choices());

        /* Pass along the Generator from the parent method [^1] */
        /* Filter the all options down to those *not* selected */
        foreach ($choices as $ident => $choice) {
            $choice = $this->parseChoice($ident, $choice);
            if (($choice['selected'] && $this->deferred()) || !$this->deferred()) {
                yield $choice;
            }
        }
    }

    /**
     * Create an input group to nest extra inputs alongside selectize
     * @return boolean
     */
    public function inputGroup()
    {
        return !!($this->allowClipboardCopy() || $this->allowUpdate() || $this->allowCreate());
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
     * @param boolean $allowUpdate Show (TRUE) or hide (FALSE) the update button.
     * @return self
     */
    public function setAllowUpdate($allowUpdate)
    {
        $this->allowUpdate = !!$allowUpdate;

        return $this;
    }

    /**
     * Determine if the property allows "Update items".
     *
     * @return boolean
     */
    public function allowUpdate()
    {
        return $this->allowUpdate;
    }

    /**
     * @param boolean $allowCreate Show (TRUE) or hide (FALSE) the create button.
     * @return self
     */
    public function setAllowCreate($allowCreate)
    {
        $this->allowCreate = !!$allowCreate;

        return $this;
    }

    /**
     * Determine if the property allows "Update items".
     *
     * @return boolean
     */
    public function allowCreate()
    {
        return $this->allowCreate;
    }

    /**
     * @return boolean
     */
    public function deferred()
    {
        return $this->deferred;
    }

    /**
     * @param boolean $deferred Should the object be loaded in deferred mode.
     * @return self
     */
    public function setDeferred($deferred)
    {
        $this->deferred = $deferred;

        return $this;
    }

    /**
     * @return mixed
     */
    public function formIdent()
    {
        return $this->formIdent;
    }

    /**
     * @param mixed $formIdent The form ident(s) for object creation and modification.
     * @return self
     */
    public function setFormIdent($formIdent)
    {
        $this->formIdent = $formIdent;

        return $this;
    }

    /**
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function formIdentAsJson()
    {
        return json_encode($this->formIdent());
    }

    /**
     * Set the selectize picker's options.
     *
     * This method overwrites existing helpers.
     *
     * @param  array $settings The selectize picker options.
     * @return self Chainable
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
     * @return self Chainable
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

        if ($this->property() instanceof SelectablePropertyInterface) {
            $choices = iterator_to_array($this->choices());

            if (isset($options['options'])) {
                $options['options'] = array_merge($options['options'], $choices);
            } else {
                $options['options'] = $choices;
            }

            $items = $this->propertyVal();

            if (!$items) {
                $items = [];
            }

            // workaround for object setter casting the proerty as object.
            if ($items instanceof ModelInterface) {
                $items = $this->mapObjToChoice($items)['value'];
            }

            if (is_scalar($items)) {
                $items = [$items];
            }

            if (!isset($options['items'])) {
                $options['items'] = [];
            }
            $options['items'] = array_merge($options['items'], $items);
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
     *
     *
     * @return boolean
     */
    public function isObject()
    {
        return !!($this->p()->type() === 'object');
    }

    /**
     * @return array
     */
    public function selectizeTemplates()
    {
        return $this->selectizeTemplates;
    }

    /**
     * @param array|object|mixed $selectizeTemplates Selectize Templates array.
     * @throws \InvalidArgumentException If the supplied argument is not of type object.
     * @return self
     */
    public function setSelectizeTemplates($selectizeTemplates)
    {
        if (!is_object($selectizeTemplates) && !is_array($selectizeTemplates)) {
            throw new \InvalidArgumentException(sprintf(
                '%s::%s given argument was of type %s instead of object',
                __CLASS__,
                __FUNCTION__,
                gettype($selectizeTemplates)
            ));
        }
        $this->selectizeTemplates = $selectizeTemplates;

        return $this;
    }

    /**
     * @return string
     */
    public function selectizeTemplatesAsJson()
    {
        return json_encode($this->selectizeTemplates());
    }

    /**
     * Convert the given value into selectize picker choices.
     *
     * @param  mixed $val     The value to parse into selectize choices.
     * @param  array $options Optional structure options.
     * @throws InvalidArgumentException If the choice structure is missing a value.
     * @return array
     */
    public function selectizeVal($val, array $options = [])
    {
        /** @todo Find a use for this */
        unset($options);

        $choices = [];

        if ($val === null) {
            return [];
        }

        $prop = $this->property();

        if ($prop instanceof AbstractProperty) {
            $val = $prop->parseVal($val);

            if (is_string($val)) {
                $val = explode($prop->multipleSeparator(), $val);
            }
        }

        if (!$prop->multiple()) {
            $val = (array)$val;
        }

        $selectizeTemplates = $this->selectizeTemplates();
        $itemTemplate = isset($selectizeTemplates['item']) ? $selectizeTemplates['item'] : null;
        $optionTemplate = isset($selectizeTemplates['option']) ? $selectizeTemplates['option'] : null;
        $selectizeController = isset($selectizeTemplates['controller']) ? $selectizeTemplates['controller'] : null;
        $selectizeData = isset($selectizeTemplates['data']) ? $selectizeTemplates['data'] : [];

        if ($prop instanceof ObjectProperty) {
            foreach ($val as &$v) {
                if (is_array($v)) {
                    if (!isset($v['value'])) {
                        throw new InvalidArgumentException('Missing [value] on choice structure.');
                    }
                    $v = $v['value'];
                }
            }

            $model = $this->modelFactory()->get($prop->objType());
            if (!$model->source()->tableExists()) {
                return $choices;
            }
            $loader = $this->collectionLoader();
            $loader->reset()
                ->setModel($model)
                ->addFilter([
                    'property' => $model->key(),
                    'value' => $val,
                    'operator' => 'IN'
                ]);

            $collection = $loader->load();

            if ($prop instanceof HierarchicalObjectProperty) {
                $collection = $this->sortObjects($collection);
            }

            $choices = [];
            foreach ($collection as $obj) {
                $c = $this->mapObjToChoice($obj);

                $obj->setData($selectizeData);

                if ($itemTemplate) {
                    $c['item_render'] = $this->selectizeRenderer->renderTemplate(
                        $itemTemplate,
                        $obj,
                        $selectizeController
                    );
                }

                if ($optionTemplate) {
                    $c['option_render'] = $this->selectizeRenderer->renderTemplate(
                        $optionTemplate,
                        $obj,
                        $selectizeController
                    );
                }

                $choices[] = $c;
            }
        } else {
            foreach ($val as $value) {
                $pChoices = $value;

                $c = $pChoices;
                $context = array_replace_recursive($selectizeData, $pChoices);

                if ($itemTemplate) {
                    $c['item_render'] = $this->selectizeRenderer->renderTemplate(
                        $itemTemplate,
                        $context,
                        $selectizeController
                    );
                }

                if ($optionTemplate) {
                    $c['option_render'] = $this->selectizeRenderer->renderTemplate(
                        $optionTemplate,
                        $context,
                        $selectizeController
                    );
                }

                $choices[] = $c;
            }
        }

        return $choices;
    }

    /**
     * Sort the objects before they are displayed as rows.
     *
     * @param ModelInterface[]|Collection $objects The objects colelction to sort
     * @see \Charcoal\Admin\Ui\CollectionContainerTrait::sortObjects()
     * @return array
     */
    public function sortObjects($objects)
    {
        $collection = new HierarchicalCollection($objects, false);
        $collection->sortTree();

        return $collection->all();
    }

    /**
     * Retrieve the object-to-choice data map.
     *
     * @return array Returns a data map to abide.
     */
    public function choiceObjMap()
    {
        $map = parent::choiceObjMap();

        if (!$this->isChoiceObjMapFinalized) {
            $this->isChoiceObjMapFinalized = true;

            $prop = $this->property();
            if ($prop instanceof ObjectProperty) {
                /** @var ModelInterface $model */
                $model = $this->modelFactory()->get($prop->objType());
                $objProperties = $model->properties();

                if ($objProperties instanceof \Iterator) {
                    $objProperties = iterator_to_array($objProperties);
                }

                foreach ($map as &$mapProp) {
                    $props = explode(':', $mapProp);
                    foreach ($props as $p) {
                        if (isset($objProperties[$p])) {
                            $mapProp = $p;
                            break;
                        }
                    }
                }
            }

            $this->choiceObjMap = $map;
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
            'label' => 'name:title:label:id',
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
            'title'                    => (string)$prop->label(),
            'translations'             => [
                'statusTemplate'       => $this->translator()->translate('Step [[ current ]] of [[ total ]]'),
            ],
            'copy_items'               => $this->allowClipboardCopy(),
            'allow_update'             => $this->allowUpdate(),
            'allow_create'             => $this->allowCreate(),

            'form_ident'               => $this->formIdent(),
            'selectize_selector'       => '#'.$this->inputId(),
            'selectize_options'        => $this->selectizeOptions(),
            'choice_obj_map'           => $this->choiceObjMap(),
            'selectize_property_ident' => $prop->ident(),
            'selectize_obj_type'       => $this->render('{{& objType }}'),
            'selectize_templates'      => $this->selectizeTemplates(),

            // Base Property
            'required'                 => $this->required(),
            'l10n'                     => $this->property()->l10n(),
            'multiple'                 => $this->multiple(),
            'multiple_separator'       => $this->property()->multipleSeparator(),
            'multiple_options'         => $this->property()->multipleOptions(),
        ];

        if ($prop instanceof ObjectProperty) {
            if ($prop->objType()) {
                $data['pattern']  = $prop->pattern();
                $data['obj_type'] = $prop->objType();
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
        $this->selectizeRenderer = $container['selectize/renderer'];
    }

    /**
     * Set an object model factory.
     *
     * @param  FactoryInterface $factory The model factory, to create objects.
     * @return void
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
    }
}
