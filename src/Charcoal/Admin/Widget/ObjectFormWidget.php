<?php

namespace Charcoal\Admin\Widget;

use UnexpectedValueException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-property'
use Charcoal\Property\ModelStructureProperty;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\Form\FormInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormWidget;
use Charcoal\Admin\Widget\FormPropertyWidget;

use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Object Admin Form
 */
class ObjectFormWidget extends FormWidget implements
    ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * @var string
     */
    protected $formIdent;

    /**
     * @var array
     */
    protected $formData;

    /**
     * **Experimental**
     * Allows the form widget to reload widgets after update.
     *
     * @var boolean $allowReload
     */
    protected $allowReload = false;

    /**
     * Force the form widget to refresh the page after update.
     *
     * @var boolean $forceRefresh
     */
    protected $forceRefresh = false;

    /**
     * @return string
     */
    public function widgetType()
    {
        return 'charcoal/admin/widget/object-form';
    }

    /**
     * Retrieve the default label for the form submission button.
     *
     * @return Translation|string|null
     */
    public function defaultSubmitLabel()
    {
        if ($this->objId()) {
            return $this->translator()->translation('Update');
        }

        return parent::defaultSubmitLabel();
    }

    /**
     * @param array $data The widget data.
     * @return ObjectForm Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (!$this->mergedDataSources) {
            $this->mergeDataSources($data);
            $this->mergedDataSources = true;
        }

        return $this;
    }

    /**
     * Set the key for the form structure to use.
     *
     * @param  string $formIdent The form identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return ObjectForm Chainable
     */
    public function setFormIdent($formIdent)
    {
        if (!is_string($formIdent)) {
            throw new InvalidArgumentException(
                'Form identifier must be a string'
            );
        }

        $this->formIdent = $formIdent;

        return $this;
    }

    /**
     * Retrieve a key for the form structure to use.
     *
     * If the form key is undefined, resolve a fallback.
     *
     * @return string
     */
    public function formIdentFallback()
    {
        $metadata = $this->obj()->metadata();

        if (isset($metadata['admin']['defaultForm'])) {
            return $metadata['admin']['defaultForm'];
        } elseif (isset($metadata['admin']['default_form'])) {
            return $metadata['admin']['default_form'];
        }

        return '';
    }

    /**
     * Retrieve the key for the form structure to use.
     *
     * @return string
     */
    public function formIdent()
    {
        return $this->formIdent;
    }

    /**
     * @param string $url The next URL.
     * @throws InvalidArgumentException If argument is not a string.
     * @return ActionInterface Chainable
     */
    public function setNextUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        $obj = $this->obj();
        if ($obj && $this->isObjRenderable($obj)) {
            $url = $obj->render($url);
        }

        $this->nextUrl = $url;
        return $this;
    }

    /**
     * @return boolean
     */
    public function allowReload()
    {
        return $this->allowReload;
    }

    /**
     * @param boolean $allowReload AllowReload for ObjectFormWidget.
     * @return self
     */
    public function setAllowReload($allowReload)
    {
        $this->allowReload = $allowReload;

        return $this;
    }

    /**
     * @return boolean
     */
    public function forceRefresh()
    {
        return $this->forceRefresh;
    }

    /**
     * @param boolean $forceRefresh ForceRefresh for ObjectFormWidget.
     * @return self
     */
    public function setForceRefresh($forceRefresh)
    {
        $this->forceRefresh = $forceRefresh;

        return $this;
    }

    /**
     * Form action (target URL)
     *
     * @return string Relative URL
     */
    public function action()
    {
        $action = parent::action();
        if (!$action) {
            $obj   = $this->obj();
            $objId = $obj->id();
            if ($objId) {
                return 'object/update';
            } else {
                return 'object/save';
            }
        } else {
            return $action;
        }
    }

    /**
     * Retrieve the object's properties as form controls.
     *
     * @param  array $group An optional group to use.
     * @throws UnexpectedValueException If a property data is invalid.
     * @return FormPropertyWidget[]|\Generator
     */
    public function formProperties(array $group = null)
    {
        $obj   = $this->obj();
        $props = $obj->metadata()->properties();

        // We need to sort form properties by form group property order if a group exists
        if (!empty($group)) {
            $group = array_map([ $this, 'camelize' ], $group);
            $group = array_flip($group);
            $props = array_intersect_key($props, $group);
            $props = array_merge($group, $props);
        }

        foreach ($props as $propertyIdent => $propertyMetadata) {
            $propertyIdent = $this->camelize($propertyIdent);
            if (method_exists($obj, 'filterPropertyMetadata')) {
                $propertyMetadata = $obj->filterPropertyMetadata($propertyMetadata, $propertyIdent);
            }

            if (!is_array($propertyMetadata)) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid property data for "%1$s", received %2$s',
                    $propertyIdent,
                    (is_object($propertyMetadata) ? get_class($propertyMetadata) : gettype($propertyMetadata))
                ));
            }

            $formProperty = $this->getOrCreateFormProperty($propertyIdent, $propertyMetadata);

            if (!$formProperty->hidden()) {
                yield $propertyIdent => $formProperty;
            }
        }
    }

    /**
     * Retrieve an object property as a form control.
     *
     * @param  string $propertyIdent An optional group to use.
     * @throws InvalidArgumentException If the property identifier is not a string.
     * @throws UnexpectedValueException If a property data is invalid.
     * @return FormPropertyWidget
     */
    public function formProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        $propertyIdent = $this->camelize($propertyIdent);

        if (isset($this->formProperties[$propertyIdent])) {
            return $this->formProperties[$propertyIdent];
        }

        $propertyMetadata = $this->obj()->metadata()->property($propertyIdent);

        if (!is_array($propertyMetadata)) {
            throw new UnexpectedValueException(sprintf(
                'Invalid property data for "%1$s", received %2$s',
                $propertyIdent,
                (is_object($propertyMetadata) ? get_class($propertyMetadata) : gettype($propertyMetadata))
            ));
        }

        $p = $this->getOrCreateFormProperty($propertyIdent, $propertyMetadata);

        return $p;
    }

    /**
     * Set the form's auxiliary data.
     *
     * This method is called via {@see self::setData()} if a "form_data" parameter
     * is present on the HTTP request.
     *
     * @param array $data Data.
     * @return ObjectFormWidget Chainable.
     */
    public function setFormData(array $data)
    {
        $objData = $this->objData();
        $merged  = array_replace_recursive($objData, $data);

        // Remove null values
        $merged = array_filter($merged, function ($val) {
            if ($val === null) {
                return false;
            }

            return true;
        });

        $this->formData = $merged;
        $this->obj()->setData($merged);

        return $this;
    }

    /**
     * Retrieve the form's auxiliary  data.
     *
     * @return array
     */
    public function formData()
    {
        if (!$this->formData) {
            $this->formData = $this->objData();
        }

        return $this->formData;
    }

    /**
     * Object data.
     * @return array Object data.
     */
    public function objData()
    {
        return $this->obj()->data();
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'obj_id'             => $this->objId(),
            'obj_type'           => $this->objType(),
            'template'           => $this->template(),
            'form_selector'      => '#'.$this->widgetId(),
            'tab'                => $this->isTabbable(),
            'group_display_mode' => $this->groupDisplayMode(),
            'group_conditions'   => $this->groupsConditionalLogic(),
            'allow_reload'       => $this->allowReload(),
            'force_refresh'      => $this->forceRefresh()
        ];
    }

    /**
     * Self recursive when a groups is an instance of FormInterface.
     *
     * @param array|null $groups Form groups to parse.
     * @return array
     */
    protected function groupsConditionalLogic(array $groups = null)
    {
        if (!$groups) {
            $groups = iterator_to_array($this->groups());
        }

        $conditions = [];

        foreach ($groups as $group) {
            if ($group instanceof FormInterface) {
                $groupGroups = iterator_to_array($group->groups());
                if (!empty($groupGroups)) {
                    $conditions = array_merge(
                        $conditions,
                        $this->groupsConditionalLogic($groupGroups)
                    );
                }
            }

            if ($group instanceof FormGroupInterface && $group->conditionalLogic()) {
                $conditions = array_merge($conditions, $group->conditionalLogic());
            }
        }

        return $conditions;
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fill ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function defaultDataSources()
    {
        return [static::DATA_SOURCE_REQUEST, static::DATA_SOURCE_OBJECT];
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * @return array
     */
    protected function defaultDataSourceFilters()
    {
        return [
            'request' => null,
            'object'  => 'array_replace_recursive'
        ];
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * Note: Adapted from {@see \Slim\CallableResolver}.
     *
     * @link   https://github.com/slimphp/Slim/blob/3.x/Slim/CallableResolver.php
     * @param  mixed $toResolve A callable used when merging data.
     * @return callable|null
     */
    protected function resolveDataSourceFilter($toResolve)
    {
        if (is_string($toResolve)) {
            $obj = $this->obj();

            $resolved = [$obj, $toResolve];

            // Sheck for Slim callable
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class  = $matches[1];
                $method = $matches[2];

                if ($class === 'parent') {
                    $resolved = [$obj, $class.'::'.$method];
                }
            }

            $toResolve = $resolved;
        }

        return parent::resolveDataSourceFilter($toResolve);
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return array_merge([
            'obj_type',
            'obj_id',
            'template'
        ], parent::acceptedRequestData());
    }

    /**
     * Fetch metadata from the current object type.
     *
     * @return array
     */
    protected function dataFromObject()
    {
        $obj           = $this->obj();
        $objMetadata   = $obj->metadata();
        $adminMetadata = (isset($objMetadata['admin']) ? $objMetadata['admin'] : null);

        $formIdent = $this->formIdent();
        if (!$formIdent) {
            $formIdent = $this->formIdentFallback();
        }

        if ($formIdent && $this->isObjRenderable($obj)) {
            $formIdent = $obj->render($formIdent);
        }

        if (isset($adminMetadata['forms'][$formIdent])) {
            $objFormData = $adminMetadata['forms'][$formIdent];
        } else {
            $objFormData = [];
        }

        $formGroups = [];

        if (isset($adminMetadata['form_groups'])) {
            $formGroups = array_merge($formGroups, $adminMetadata['form_groups']);
        }

        if (isset($adminMetadata['formGroups'])) {
            $formGroups = array_merge($formGroups, $adminMetadata['formGroups']);
        }

        if (isset($objFormData['groups']) && !empty($formGroups)) {
            $extraFormGroups = array_intersect(
                array_keys($formGroups),
                array_keys($objFormData['groups'])
            );
            foreach ($extraFormGroups as $groupIdent) {
                $objFormData['groups'][$groupIdent] = array_replace_recursive(
                    $formGroups[$groupIdent],
                    $objFormData['groups'][$groupIdent]
                );
            }
        }

        $formSidebars = [];

        if (isset($adminMetadata['form_sidebars'])) {
            $formSidebars = array_merge($formSidebars, $adminMetadata['form_sidebars']);
        }

        if (isset($adminMetadata['formSidebars'])) {
            $formSidebars = array_merge($formSidebars, $adminMetadata['formSidebars']);
        }

        if (isset($objFormData['sidebars']) && !empty($formSidebars)) {
            $extraFormSidebars = array_intersect(
                array_keys($formSidebars),
                array_keys($objFormData['sidebars'])
            );
            foreach ($extraFormSidebars as $sidebarIdent) {
                $objFormData['sidebars'][$sidebarIdent] = array_replace_recursive(
                    $formSidebars[$sidebarIdent],
                    $objFormData['sidebars'][$sidebarIdent]
                );
            }
        }

        return $objFormData;
    }

    /**
     * Determine if the form has any multilingual properties.
     *
     * @return boolean
     */
    public function hasL10nFormProperties()
    {
        if ($this->hasObj()) {
            $locales = count($this->translator()->availableLocales());
            if ($locales > 1) {
                foreach ($this->getFormProperties() as $formProp) {
                    $modelProp = $formProp->property();
                    if ($modelProp['l10n']) {
                        return true;
                    } elseif ($modelProp instanceof ModelStructureProperty) {
                        $metadata = $this->obj()->property($modelProp['ident'])->getStructureMetadata();
                        foreach ($metadata->properties() as $prop) {
                            if (isset($prop['l10n']) && $prop['l10n']) {
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        }

        return parent::hasL10nFormProperties();
    }

    /**
     * Parse a form group.
     *
     * @param  string                   $groupIdent The group identifier.
     * @param  array|FormGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     * @return FormGroupInterface
     */
    protected function parseFormGroup($groupIdent, $group)
    {
        $group = parent::parseFormGroup($groupIdent, $group);

        if (method_exists($this->obj(), 'filterAdminFormGroup')) {
            $group = $this->obj()->filterAdminFormGroup($group, $groupIdent);
        }

        return $group;
    }

    /**
     * Yield the form's property controls.
     *
     * @return array
     */
    public function parseFormProperties()
    {
        $props = [];
        foreach ($this->formProperties as $k => $v) {
            $props[$this->camelize($k)] = $v;
        }
        return $props;
    }

    /**
     * Create a new form group widget.
     *
     * @see    \Charcoal\Ui\Form\FormTrait::createFormGroup()
     * @param  array|null $data Optional. The form group data to set.
     * @return FormGroupInterface
     */
    protected function createFormGroup(array $data = null)
    {
        if (isset($data['type'])) {
            $type = $data['type'];
        } else {
            $type = $this->defaultGroupType();
        }

        $group = $this->formGroupFactory()->create($type);
        $group->setForm($this->formWidget());

        if ($group instanceof ObjectContainerInterface) {
            if (empty($group->objType())) {
                $group->setObjType($this->objType());
            }

            if (empty($group->objId()) && !empty($this->objId())) {
                $group->setObjId($this->objId());
            }
        }

        if ($data !== null) {
            $group->setData($data);
        }

        return $group;
    }

    /**
     * Update the given form group widget.
     *
     * @see    \Charcoal\Ui\Form\FormTrait::updateFormGroup()
     * @param  FormGroupInterface $group      The form group to update.
     * @param  array|null         $groupData  Optional. The new group data to apply.
     * @param  string|null        $groupIdent Optional. The new group identifier.
     * @return FormGroupInterface
     */
    protected function updateFormGroup(
        FormGroupInterface $group,
        array $groupData = null,
        $groupIdent = null
    ) {
        $group->setForm($this);

        if ($groupIdent !== null) {
            $group->setIdent($groupIdent);
        }

        if ($group instanceof ObjectContainerInterface) {
            if (empty($group->objType())) {
                $group->setObjType($this->objType());
            }

            if (empty($group->objId()) && !empty($this->objId())) {
                $group->setObjId($this->objId());
            }
        }

        if ($groupData !== null) {
            $group->setData($groupData);
        }

        return $group;
    }
}
