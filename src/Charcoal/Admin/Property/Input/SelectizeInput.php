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
     * The form data to use while creating objects through Selectize.
     *
     * Must be an array
     *
     * @var array|null
     */
    private $formData;

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
     * @var array
     */
    private $disabledFields = [];

    /**
     * @var string $remoteSource
     */
    private $remoteSource;

    /**
     * @var string|null
     */
    private $optgroupProperty;

    /**
     * @var array|null
     */
    private $optgroupObjMap;

    /**
     * Check used to parse multi Optgroup map against the obj properties.
     *
     * @var boolean
     */
    private $isOptgroupObjMapFinalized = false;

    /**
     * This function takes an array and fill the model object with its value.
     *
     * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
     *
     * For example, calling with `setData(['properties'=>$properties])` would call
     * `setProperties($properties)`, becasue `setProperties()` exists.
     *
     * But calling with `setData(['foobar'=>$foo])` would set the `$foobar` member
     * on the metadata object, because the method `set_foobar()` does not exist.
     *
     * @param array $data The input data.
     * @return self
     */
    public function setData(array $data)
    {
        // Push selectize options back at the end of the data container.
        if (isset($data['selectizeOptions'])) {
            $selectizeOptions = $data['selectizeOptions'];
            unset($data['selectizeOptions']);
            $data['selectizeOptions'] = $selectizeOptions;
        }

        parent::setData($data);

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
     * Retrieve the default empty option structure.
     *
     * @return array
     */
    protected function defaultEmptyChoice()
    {
        return [
            'value' => '',
            'label' => ''
        ];
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
        if ($this->p()['allowNull'] && !$this->p()['multiple']) {
            $prepend = $this->parseChoice('', $this->emptyChoice());
            yield $prepend;
        }

        // When deferred, we want to fetch choices for current values only.
        if ($this->deferred()) {
            $choices = $this->selectizeVal($this->propertyVal());
        } else {
            $choices = $this->selectizeVal($this->p()->choices());
        }

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
     * @return self
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
        $this->deferred = ($this->property() instanceof ObjectProperty || $this->remoteSource()) ? $deferred : false;

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
     * @return array|null
     */
    public function getFormData(): ?array
    {
        return $this->formData;
    }

    /**
     * @param array|null $formData FormData for SelectizeInput.
     * @return self
     */
    public function setFormData(?array $formData): self
    {
        $this->formData = $formData;

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

        if ($this->deferred()) {
            $placeholder = $this->translator()->trans('Search…');
        } else {
            $placeholder = $this->translator()->trans('Select…');
        }

        $options['placeholder'] = $placeholder;
        $prop = $this->property();

        // Generate Optgroups from model.
        if ($this->optgroupProperty() && $prop instanceof ObjectProperty) {
            $model = $this->modelFactory()->get($prop['objType']);

            $optgroupProp = $model->p($this->optgroupProperty());

            if ($optgroupProp instanceof ObjectProperty) {
                $method = [ $this, 'mapObjToOptgroup' ];

                $loader = $this->collectionLoader()->setModel($optgroupProp['objType']);

                if ($loader->source()->tableExists()) {
                    $loader->addFilter([
                        'property' => 'active',
                        'value'    => 1,
                    ]);

                    $loader->setCallback($method);
                    $collection = $loader->load();

                    $optgroups = array_map($method, $collection->values());
                } else {
                    $optgroups = [];
                }

                $options['optgroups'] = $optgroups;
            } elseif ($optgroupProp instanceof SelectablePropertyInterface) {
                $optgroups = array_values($optgroupProp->choices());

                // Make sure label is converted to string.
                array_walk($optgroups, function (&$item) {
                    $item['label'] = (string)$item['label'];
                });

                $options['optgroups'] = $optgroups;
            }
        }

        if ($prop instanceof SelectablePropertyInterface) {
            $choices = iterator_to_array($this->choices());

            if (isset($options['options'])) {
                $options['options'] = array_merge($options['options'], $choices);
            } else {
                $options['options'] = $choices;
            }

            // L10n properties is not supported through selectize items array,
            $items = !$prop['l10n'] ? $this->propertyVal() : null;

            if ($items !== null && $prop instanceof AbstractProperty) {
                $items = $this->property()->inputVal($items);

                if (is_string($items)) {
                    $items = explode($prop->multipleSeparator(), $items);
                }
            }

            if (!$prop['multiple']) {
                $items = (array)$items;
            }

            if (!$items) {
                $items = [];
            }

            // workaround for object setter casting the property as object.
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
     * @return boolean
     */
    public function isObject()
    {
        return !!($this->p() instanceof ObjectProperty);
    }

    /**
     * Retrieve the input name.
     *
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

        if ($this->multiple() && $this->isObject()) {
            $name .= '[]';
        }

        return $name;
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
            $selectizeTemplates = [
                'item' => $selectizeTemplates,
                'option' => $selectizeTemplates,
                'controller' => class_exists($selectizeTemplates) ? $selectizeTemplates : null
            ];
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

        if ($val === null || $val === '') {
            return [];
        }

        $prop = $this->property();

        if ($prop instanceof AbstractProperty) {
            $val = $prop->parseVal($val);

            if (is_string($val)) {
                $val = explode($prop->multipleSeparator(), $val);
            }
        }

        if (!$prop['multiple']) {
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

            if (empty($val)) {
                return $choices;
            }

            $model = $this->modelFactory()->get($prop['objType']);
            if (!$model->source()->tableExists()) {
                return $choices;
            }

            $loader = $this->collectionLoader()->reset()->setModel($model);

            if (!$loader->source()->tableExists()) {
                return [];
            }

            $loader->addFilter([
                'property' => $model->key(),
                'operator' => 'IN',
                'value'    => $val,
            ]);

            $collection = $loader->load();

            if ($prop instanceof HierarchicalObjectProperty) {
                $collection = $this->sortObjects($collection);
            }

            $choices = [];
            foreach ($collection as $obj) {
                $c = $this->mapObjToChoice($obj);
                $obj->setData($selectizeData);

                if (in_array($c['value'], $this->disabledFields())) {
                    $c['disabled'] = 'disabled';
                }

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
     * @param ModelInterface[]|Collection $objects The objects collection to sort.
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
                $model = $this->modelFactory()->get($prop['objType']);
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
     * @param  array|\ArrayAccess|ModelInterface $obj The object to map to a optgroup.
     * @return array
     */
    public function mapObjToOptgroup($obj)
    {
        $map = $this->optgroupObjMap();

        $optgroup = [];

        foreach ($map as $key => $props) {
            $optgroup[$key] = null;

            $props = explode(':', $props);
            foreach ($props as $prop) {
                // I think we can still use this CHOICE method here for render.
                $optgroup[$key] = $this->renderChoiceObjMap($obj, $prop);
                break;
            }
        }

        return $optgroup;
    }

    /**
     * Retrieve the object-to-choice data map.
     *
     * @return array Returns a data map to abide.
     */
    public function optgroupObjMap()
    {
        return $this->optgroupObjMap;
    }

    /**
     * @param array|null $optgroupObjMap OptgroupObjMap for SelectizeInput.
     * @return self
     */
    public function setOptgroupObjMap($optgroupObjMap)
    {
        $map = $optgroupObjMap ?: $this->defaultOptgroupObjMap();

        if (!$this->isOptgroupObjMapFinalized) {
            $this->isOptgroupObjMapFinalized = true;

            // Get the optgroup property.

            $prop = $this->property();
            if ($prop instanceof ObjectProperty) {
                /** @var ModelInterface $model */
                $model = $this->modelFactory()->get($prop['objType']);

                // fetch the optgroup property
                $optgroupProp = $model->p($this->optgroupProperty());
                if ($optgroupProp instanceof ObjectProperty) {
                    $optgroupModel = $this->modelFactory()->get($optgroupProp['objType']);

                    $objProperties = $optgroupModel->properties();

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
            }

            $this->optgroupObjMap = $map;
        }

        return $this;
    }


    /**
     * Retrieve the default object-to-optgroup data map.
     *
     * @return array
     */
    public function defaultOptgroupObjMap()
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
            'title'                    => (string)$prop['label'],
            'translations'             => [
                'statusTemplate'       => $this->translator()->translate('Step [[ current ]] of [[ total ]]'),
            ],
            'copy_items'               => $this->allowClipboardCopy(),
            'allow_update'             => $this->allowUpdate(),
            'allow_create'             => $this->allowCreate(),

            'form_data'                => $this->getFormData(),
            'form_ident'               => $this->formIdent(),
            'selectize_selector'       => '#'.$this->inputId(),
            'selectize_options'        => $this->selectizeOptions(),
            'choice_obj_map'           => $this->choiceObjMap(),
            'selectize_property_ident' => $prop->ident(),
            'selectize_templates'      => $this->selectizeTemplates(),
            'selectize_property'       => json_encode($this->property()),
            'remote_source'            => $this->remoteSource(),

            // Base Property
            'required'                 => $this->required(),
            'l10n'                     => $this->property()['l10n'],
            'multiple'                 => $this->multiple(),
            'multiple_separator'       => $this->property()->multipleSeparator(),
            'multiple_options'         => $this->property()['multipleOptions'],
        ];

        if ($prop instanceof ObjectProperty) {
            if ($prop['objType']) {
                $data['pattern']  = $prop['pattern'];
                $data['obj_type'] = $prop['objType'];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function disabledFields()
    {
        return $this->disabledFields;
    }

    /**
     * @param array $disabledFields DisabledFields for SelectizeInput.
     * @return self
     */
    public function setDisabledFields(array $disabledFields)
    {
        $this->disabledFields = $disabledFields;

        return $this;
    }

    /**
     * @return string
     */
    public function remoteSource()
    {
        return $this->remoteSource;
    }

    /**
     * @param string $remoteSource RemoteSource for SelectizeInput.
     * @return self
     */
    public function setRemoteSource($remoteSource)
    {
        $this->remoteSource = $remoteSource;

        if ($this->remoteSource) {
            $this->remoteSource = $this->renderTemplate($this->remoteSource);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function optgroupProperty()
    {
        return $this->optgroupProperty;
    }

    /**
     * @param string|null $optgroupProperty OptgroupProperty for SelectizeInput.
     * @return self
     */
    public function setOptgroupProperty($optgroupProperty)
    {
        $this->optgroupProperty = $optgroupProperty;

        return $this;
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
