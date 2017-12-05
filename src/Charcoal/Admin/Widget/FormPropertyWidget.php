<?php

namespace Charcoal\Admin\Widget;

use LogicException;
use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From `charcoal-property`
use Charcoal\Property\PropertyInterface;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormInput\FormInputInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 * Form Control Widget
 *
 * For model properties.
 */
class FormPropertyWidget extends AdminWidget implements
    FormInputInterface
{
    const HIDDEN_FORM_CONTROL     = 'charcoal/admin/property/input/hidden';
    const DEFAULT_FORM_CONTROL    = 'charcoal/admin/property/input/text';

    const PROPERTY_CONTROL = 'input';
    const PROPERTY_DISPLAY = 'display';
    const DEFAULT_OUTPUT   = self::PROPERTY_CONTROL;

    /**
     * The widget's type.
     *
     * @var string|null
     */
    protected $type;

    /**
     * The widget's property output type.
     *
     * @var string
     */
    protected $outputType;

    /**
     * Store the model property.
     *
     * @var PropertyInterface|null
     */
    private $property;

    /**
     * The model's property type.
     *
     * @var string|null
     */
    protected $propertyType;

    /**
     * The model property's name.
     *
     * @var string|null
     */
    private $propertyIdent;

    /**
     * The model property's value.
     *
     * @var mixed
     */
    private $propertyVal;

    /**
     * The model property's metadata.
     *
     * @var array
     */
    private $propertyData = [];

    /**
     * Store the property control instance.
     *
     * @var PropertyInputInterface|null
     */
    private $inputProperty;

    /**
     * The property control type.
     *
     * @var string|null
     */
    protected $inputType;

    /**
     * Store the property display instance.
     *
     * @var PropertyDisplayInterface|null
     */
    private $displayProperty;

    /**
     * The property display type.
     *
     * @var string|null
     */
    protected $displayType;

    /**
     * The label is displayed by default.
     *
     * @var boolean
     */
    protected $showLabel;

    /**
     * The description is displayed by default.
     *
     * @var boolean
     */
    protected $showDescription;

    /**
     * The notes are displayed by default.
     *
     * @var boolean
     */
    protected $showNotes;

    /**
     * The CSS class names for the `.form-field`.
     *
     * @var string[]
     */
    protected $formFieldCssClass = [];

    /**
     * The CSS class names for the `.form-group`.
     *
     * @var string[]
     */
    protected $formGroupCssClass = [];

    /**
     * The L10N display mode.
     *
     * @var string
     */
    private $l10nMode;

    /**
     * The form group the input belongs to.
     *
     * @var FormGroupInterface
     */
    protected $formGroup;

    /**
     * Store the model property factory.
     *
     * @var FactoryInterface
     */
    private $propertyFactory;

    /**
     * Store the property form control factory.
     *
     * @var FactoryInterface
     */
    private $propertyInputFactory;

    /**
     * Store the property display factory.
     *
     * @var FactoryInterface
     */
    private $propertyDisplayFactory;

    /**
     * Track the state of data merging.
     *
     * @var boolean
     */
    private $isMergingWidgetData = false;

    /**
     * Set the widget's dependencies.
     *
     * @param  Container $container Service container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setView($container['view']);
        $this->setPropertyFactory($container['property/factory']);
        $this->setPropertyInputFactory($container['property/input/factory']);
        $this->setPropertyDisplayFactory($container['property/display/factory']);
    }

    /**
     * Set a property factory.
     *
     * @param  FactoryInterface $factory The factory to create property values.
     * @return FormPropertyWidget Chainable
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property factory.
     *
     * @throws RuntimeException If the property factory is missing.
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(
                'Missing Property Factory'
            );
        }

        return $this->propertyFactory;
    }

    /**
     * Set a property control factory.
     *
     * @param  FactoryInterface $factory The factory to create form controls for property values.
     * @return FormPropertyWidget Chainable
     */
    protected function setPropertyInputFactory(FactoryInterface $factory)
    {
        $this->propertyInputFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property control factory.
     *
     * @throws RuntimeException If the property control factory is missing.
     * @return FactoryInterface
     */
    public function propertyInputFactory()
    {
        if ($this->propertyInputFactory === null) {
            throw new RuntimeException(
                'Missing Property Input Factory'
            );
        }

        return $this->propertyInputFactory;
    }

    /**
     * Set a property display factory.
     *
     * @param  FactoryInterface $factory The factory to create displayable property values.
     * @return FormPropertyWidget Chainable
     */
    protected function setPropertyDisplayFactory(FactoryInterface $factory)
    {
        $this->propertyDisplayFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property display factory.
     *
     * @throws RuntimeException If the property display factory is missing.
     * @return FactoryInterface
     */
    public function propertyDisplayFactory()
    {
        if ($this->propertyDisplayFactory === null) {
            throw new RuntimeException(
                'Missing Property Display Factory'
            );
        }

        return $this->propertyDisplayFactory;
    }

    /**
     * Retrieve the widget ID.
     *
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $type = $this->type();
            switch ($type) {
                case static::PROPERTY_DISPLAY:
                    $id = $this->display()->displayId();
                    break;

                case static::PROPERTY_CONTROL:
                    $id = $this->input()->inputId();
                    break;

                default:
                    $id = 'widget_'.uniqid();
                    break;
            }

            $this->widgetId = $id;
        }

        return $this->widgetId;
    }

    /**
     * Set the widget or property type.
     *
     * @param  string $type The widget or property type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setType($type)
    {
        if (empty($type)) {
            $this->type = null;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Form property widget type must be a string'
            );
        }

        if ($this->propertyFactory()->isResolvable($type)) {
            $this->setPropertyType($type);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Set the model's property type.
     *
     * Can be either "input" or "display". A property input or property display identifier
     * is also accepted.
     *
     * @param  string $type The input or display property type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setOutputType($type)
    {
        if (empty($type)) {
            $this->outputType = static::DEFAULT_OUTPUT;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Form property widget type must be a string'
            );
        }

        if (!in_array($type, $this->supportedOutputTypes())) {
            $type = $this->resolveOutputType($type);
        }

        $this->outputType = $type;

        return $this;
    }

    /**
     * Retrieve the widget's property output type.
     *
     * Defaults to "input_type".
     *
     * @throws LogicException If the "input_type" and "display_type" are disabled.
     * @return string|null
     */
    public function outputType()
    {
        if ($this->outputType === null) {
            if ($this->inputType === false && $this->displayType === false) {
                throw new LogicException('Form property widget requires an "input_type" or a "display_type"');
            }

            $type = null;

            if ($this->inputType === false || is_string($this->displayType)) {
                $type = static::PROPERTY_DISPLAY;
            }

            if ($this->displayType === false || is_string($this->inputType)) {
                $type = static::PROPERTY_CONTROL;
            }

            $this->outputType = $type;
        }

        return $this->outputType;
    }

    /**
     * Resolve the property output type.
     *
     * Note: The "input_type" or "display_type" will be set
     * if the output type is a valid output property.
     *
     * @param  string $type The input or display property type.
     * @throws InvalidArgumentException If the property output type is invalid.
     * @return string Returns either "input" or "display".
     */
    protected function resolveOutputType($type)
    {
        if ($this->propertyInputFactory()->isResolvable($type)) {
            $this->setInputType($type);
            return static::PROPERTY_CONTROL;
        } elseif ($this->propertyDisplayFactory()->isResolvable($type)) {
            $this->setDisplayType($type);
            return static::PROPERTY_DISPLAY;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid form property output type, received %s',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
    }

    /**
     * Retrieved the resolved the property output type.
     *
     * @return string|null Returns the property's "input_type" or "display_type".
     */
    protected function resolvedOutputType()
    {
        switch ($this->outputType()) {
            case static::PROPERTY_DISPLAY:
                return $this->displayType();

            case static::PROPERTY_CONTROL:
                return $this->inputType();
        }
    }

    /**
     * Retrieve the supported property output types.
     *
     * @return array
     */
    public function supportedOutputTypes()
    {
        return [ static::PROPERTY_CONTROL, static::PROPERTY_DISPLAY ];
    }

    /**
     * Set the form input's parent group.
     *
     * @param  FormGroupInterface $formGroup The parent form group object.
     * @return FormPropertyWidget Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup)
    {
        $this->formGroup = $formGroup;

        return $this;
    }

    /**
     * Retrieve the input's parent group.
     *
     * @return FormGroupInterface
     */
    public function formGroup()
    {
        return $this->formGroup;
    }

    /**
     * Clear the input's parent group.
     *
     * @return FormPropertyWidget Chainable
     */
    public function clearFormGroup()
    {
        $this->formGroup = null;

        return $this;
    }

    /**
     * Set the core data for the widget and property's first.
     *
     * @param  array $data The widget and property data.
     * @return array The widget and property data.
     */
    private function setCoreData(array $data)
    {
        if (isset($data['input_type'])) {
            $this->setInputType($data['input_type']);
        }

        if (isset($data['display_type'])) {
            $this->setDisplayType($data['display_type']);
        }

        if (isset($data['property_type'])) {
            $this->setPropertyType($data['property_type']);
        }

        if (isset($data['output_type'])) {
            $this->setOutputType($data['output_type']);
        }

        if (isset($data['type'])) {
            $this->setType($data['type']);
        }

        return $data;
    }

    /**
     * Set the widget and property data.
     *
     * @param  array|ArrayAccess $data Widget and property data.
     * @return FormPropertyWidget Chainable
     */
    public function setData(array $data)
    {
        $this->isMergingWidgetData = true;

        $data = $this->setCoreData($data);

        parent::setData($data);

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->setPropertyData($data);

        $this->isMergingWidgetData = false;

        return $this;
    }

    /**
     * Merge widget and property data.
     *
     * @param  array $data Widget and property data.
     * @return FormPropertyWidget Chainable
     */
    public function merge(array $data)
    {
        $this->isMergingWidgetData = true;

        $data = $this->setCoreData($data);

        $this->mergePropertyData($data);

        $this->isMergingWidgetData = false;

        return $this;
    }

    /**
     * Set the model's property type.
     *
     * @param  string $type The property type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setPropertyType($type)
    {
        if (empty($type)) {
            $this->propertyType = null;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Property type must be a string'
            );
        }

        $this->propertyType = $type;

        return $this;
    }

    /**
     * Retrieve the model's property type.
     *
     * @return string|null
     */
    public function propertyType()
    {
        return $this->propertyType;
    }

    /**
     * Set the form's property identifier.
     *
     * @param  string $propertyIdent The property identifier.
     * @throws InvalidArgumentException If the property ident is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setPropertyIdent($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string'
            );
        }

        $this->propertyIdent = $propertyIdent;

        return $this;
    }

    /**
     * Retrieve the form's property identifier.
     *
     * @return string|null
     */
    public function propertyIdent()
    {
        return $this->propertyIdent;
    }

    /**
     * Set the property metadata.
     *
     * @param  array $data The property configset.
     * @return FormPropertyWidget Chainable
     */
    public function setPropertyData(array $data)
    {
        $this->propertyData = $data;

        if (!$this->isMergingWidgetData) {
            $this->setCoreData($this->propertyData);
        }

        if ($this->property) {
            $this->property->setData($data);
        }

        return $this;
    }

    /**
     * Merge the property metadata.
     *
     * @param  array $data The property configset.
     * @return FormPropertyWidget Chainable
     */
    public function mergePropertyData(array $data)
    {
        $this->propertyData = array_replace($this->propertyData, $data);

        if (!$this->isMergingWidgetData) {
            $this->setCoreData($this->propertyData);
        }

        if ($this->property) {
            $this->property->setData($data);
        }

        return $this;
    }

    /**
     * Retrieve the property metadata.
     *
     * @return array
     */
    public function propertyData()
    {
        return $this->propertyData;
    }

    /**
     * Set the property's value.
     *
     * @param  mixed $propertyVal The property value.
     * @return FormPropertyWidget Chainable
     */
    public function setPropertyVal($propertyVal)
    {
        $this->propertyVal = $propertyVal;

        return $this;
    }

    /**
     * Retrieve the property's value.
     *
     * @return mixed
     */
    public function propertyVal()
    {
        return $this->propertyVal;
    }

    /**
     * Show/hide the property's label.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the label.
     * @return FormPropertyWidget Chainable
     */
    public function setShowLabel($show)
    {
        $this->showLabel = !!$show;

        return $this;
    }

    /**
     * Determine if the label is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a label.
     */
    public function showLabel()
    {
        if ($this->showLabel === null) {
            $prop = $this->property();
            $show = $prop['show_label'];
            if ($show !== null) {
                $this->showLabel = $show;
            } else {
                $this->showLabel = true;
            }
        }

        if ($this->showLabel !== false) {
            return !!strval($this->property()->label());
        } else {
            return false;
        }
    }

    /**
     * Show/hide the property's description.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the description.
     * @return FormPropertyWidget Chainable
     */
    public function setShowDescription($show)
    {
        $this->showDescription = !!$show;

        return $this;
    }

    /**
     * Determine if the description is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a description.
     */
    public function showDescription()
    {
        if ($this->showDescription === null) {
            $prop = $this->property();
            $show = $prop['show_description'];
            if ($show !== null) {
                $this->showDescription = $show;
            } else {
                $this->showDescription = true;
            }
        }

        if ($this->showDescription !== false) {
            return !!strval($this->property()->description());
        } else {
            return false;
        }
    }

    /**
     * Show/hide the property's notes.
     *
     * @param  boolean|string $show Show (TRUE) or hide (FALSE) the notes.
     * @return FormPropertyWidget Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotes = ($show === 'above' ? $show : !!$show);

        return $this;
    }

    /**
     * Determine if the notes is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there are notes.
     */
    public function showNotes()
    {
        if ($this->showNotes === null) {
            $prop = $this->property();
            $show = $prop['show_notes'];
            if ($show !== null) {
                $this->showNotes = $show;
            } else {
                $this->showNotes = true;
            }
        }

        if ($this->showNotes !== false) {
            return !!strval($this->property()->notes());
        } else {
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function showNotesAbove()
    {
        if ($this->showNotes === null) {
            $this->showNotes();
        }

        $show = $this->showNotes;

        if ($show !== 'above') {
            return false;
        }

        $notes = $this->property()->notes();

        return !!$notes;
    }

    /**
     * @return Translation|string|null
     */
    public function description()
    {
        return $this->renderTemplate((string)$this->property()->description());
    }

    /**
     * @return Translation|string|null
     */
    public function notes()
    {
        return $this->renderTemplate((string)$this->property()->notes());
    }

    /**
     * @return boolean
     */
    public function hidden()
    {
        return ($this->inputType() === static::HIDDEN_FORM_CONTROL || $this->property()->hidden());
    }

    /**
     * @return string
     */
    public function inputId()
    {
        return 'input_id';
    }

    /**
     * @return string
     */
    public function inputName()
    {
        return 'input_name';
    }

    /**
     * @return string
     */
    public function displayId()
    {
        return 'display_id';
    }

    /**
     * @return string
     */
    public function displayName()
    {
        return 'display_name';
    }

    /**
     * Set the property control type.
     *
     * @param  string $type The form control type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setInputType($type)
    {
        if (empty($type)) {
            $this->inputType = null;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Property input type must be a string'
            );
        }

        $this->inputType = $type;

        return $this;
    }

    /**
     * Retrieve the property control type.
     *
     * @return string
     */
    public function inputType()
    {
        if ($this->inputType === null) {
            $this->inputType = $this->resolveInputType();
        }

        return $this->inputType;
    }

    /**
     * Resolve the property control type.
     *
     * @return string
     */
    private function resolveInputType()
    {
        $type = null;

        /** Attempt input type resolution without instantiating the property, at first. */
        $metadata = $this->propertyData();
        if ($metadata) {
            if (isset($metadata['hidden']) && $metadata['hidden']) {
                $type = static::HIDDEN_FORM_CONTROL;
            }

            if (!$type && isset($metadata['input_type'])) {
                $type = $metadata['input_type'];
            }
        }

        if ($this->propertyType || $this->property) {
            $property = $this->property();
            $metadata = $property->metadata();

            if ($property->hidden()) {
                $type = static::HIDDEN_FORM_CONTROL;
            }

            if (!$type && isset($metadata['input_type'])) {
                $type = $metadata['input_type'];
            }

            if (!$type && isset($metadata['admin']['input_type'])) {
                $type = $metadata['admin']['input_type'];
            }
        }

        if (!$type) {
            $type = static::DEFAULT_FORM_CONTROL;
        }

        return $type;
    }

    /**
     * Set the property display type.
     *
     * @param  string $type The property display type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return FormPropertyWidget Chainable
     */
    public function setDisplayType($type)
    {
        if (empty($type)) {
            $this->displayType = null;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Property display type must be a string'
            );
        }

        $this->displayType = $type;

        return $this;
    }

    /**
     * Retrieve the property display type.
     *
     * @return string
     */
    public function displayType()
    {
        if ($this->displayType === null) {
            $this->displayType = $this->resolveDisplayType();
        }

        return $this->displayType;
    }

    /**
     * Resolve the property display type.
     *
     * @return string
     */
    private function resolveDisplayType()
    {
        $type = null;

        /** Attempt display type resolution without instantiating the property, at first. */
        $metadata = $this->propertyData();
        if ($metadata) {
            if (isset($metadata['display_type'])) {
                $type = $metadata['display_type'];
            }
        }

        if ($this->propertyType || $this->property) {
            $type = $this->property()->displayType();
        }

        return $type;
    }

    /**
     * Set the widget's model property.
     *
     * @param  PropertyInterface $property The property.
     * @return FormPropertyWidget Chainable
     */
    public function setProperty(PropertyInterface $property)
    {
        $this->property      = $property;
        $this->propertyType  = $property->type();
        $this->propertyIdent = $property->ident();

        $inputType = $property['input_type'];
        if ($inputType) {
            $this->inputType = $inputType;
        }

        $displayType = $property['display_type'];
        if ($displayType) {
            $this->displayType = $displayType;
        }

        return $this;
    }

    /**
     * Retrieve the widget's model property.
     *
     * @return PropertyInterface
     */
    public function property()
    {
        if ($this->property === null) {
            $this->property = $this->createProperty();
        }

        return $this->property;
    }

    /**
     * Create the widget's model property from the property's dataset.
     *
     * @return PropertyInterface
     */
    private function createProperty()
    {
        $prop = $this->propertyFactory()->create($this->propertyType());

        $prop->setIdent($this->propertyIdent());
        $prop->setData($this->propertyData());

        return $prop;
    }

    /**
     * Alias of {@see self::property()}
     *
     * @return PropertyInterface
     */
    public function prop()
    {
        return $this->property();
    }

    /**
     * Determine if the form control's active language should be displayed.
     *
     * @see    FormSidebarWidget::showLanguageSwitch()
     * @return boolean
     */
    public function showActiveLanguage()
    {
        $property = $this->property();
        $locales  = count($this->translator()->availableLocales());

        return ($locales > 1 && $property->l10n());
    }

    /**
     * Generate a CSS class name for the property's input name.
     *
     * @return string
     */
    public function inputNameAsCssClass()
    {
        $name = str_replace([ ']', '[' ], [ '', '-' ], $this->propertyIdent());
        $name = $this->camelize($name);

        return $name;
    }

    /**
     * Set the CSS class name(s) for the `.form-field`.
     *
     * @param  mixed $cssClass One or more CSS class names.
     * @return self
     */
    public function setFormFieldCssClass($cssClass)
    {
        $cssClass = array_merge($this->defaultFormFieldCssClasses(), $this->parseCssClasses($cssClass));
        $this->formFieldCssClass = array_unique($cssClass);
        return $this;
    }

    /**
     * Add CSS class name(s) for the `.form-field`.
     *
     * @param  mixed $cssClass One or more CSS class names.
     * @return self
     */
    public function addFormFieldCssClass($cssClass)
    {
        $cssClass = array_merge($this->formFieldCssClass, $this->parseCssClasses($cssClass));
        $this->formFieldCssClass = array_unique($cssClass);
        return $this;
    }

    /**
     * Retrieve the default CSS class name(s) for the `.form-field`.
     *
     * @return string[]
     */
    protected function defaultFormFieldCssClasses()
    {
        $classes = [ 'form-field', 'form-field-'.$this->widgetId() ];

        if ($this->prop()) {
            $classes[] = 'form-property-'.$this->inputNameAsCssClass();

            if ($this->prop()->type()) {
                $classes[] = 'form-property-'.$this->prop()->type();
            }

            if ($this->prop()->multiple()) {
                $classes[] = '-multiple';
            }
        }

        if ($this->showActiveLanguage()) {
            $classes[] = '-l10n';
        }

        if ($this->hidden()) {
            $classes[] = 'hidden';
        }

        return $classes;
    }

    /**
     * Retrieve the CSS class name(s) for the `.form-field`.
     *
     * @return string
     */
    public function formFieldCssClass()
    {
        if (empty($this->formFieldCssClass)) {
            $this->formFieldCssClass = $this->defaultFormFieldCssClasses();
        }

        return implode(' ', $this->formFieldCssClass);
    }

    /**
     * Set the CSS class name(s) for the `.form-group`.
     *
     * @param  mixed $cssClass One or more CSS class names.
     * @return self
     */
    public function setFormGroupCssClass($cssClass)
    {
        $cssClass = array_merge($this->defaultFormGroupCssClasses(), $this->parseCssClasses($cssClass));
        $this->formGroupCssClass = array_unique($cssClass);
        return $this;
    }

    /**
     * Add CSS class name(s) for the `.form-group`.
     *
     * @param  mixed $cssClass One or more CSS class names.
     * @return self
     */
    public function addFormGroupCssClass($cssClass)
    {
        $cssClass = array_merge($this->formGroupCssClass, $this->parseCssClasses($cssClass));
        $this->formGroupCssClass = array_unique($cssClass);
        return $this;
    }

    /**
     * Retrieve the default CSS class name(s) for the `.form-group`.
     *
     * @return string[]
     */
    protected function defaultFormGroupCssClasses()
    {
        return [ 'form-group' ];
    }

    /**
     * Retrieve the CSS class name(s) for the `.form-group`.
     *
     * @return string
     */
    public function formGroupCssClass()
    {
        if (empty($this->formGroupCssClass)) {
            $this->formGroupCssClass = $this->defaultFormGroupCssClasses();
        }

        return implode(' ', $this->formGroupCssClass);
    }

    /**
     * Parse the CSS class name(s).
     *
     * @param  mixed $classes One or more CSS class names.
     * @throws InvalidArgumentException If a class name is not a string.
     * @return string[]
     */
    protected function parseCssClasses($classes)
    {
        if (is_string($classes)) {
            $classes = explode(' ', $classes);
        }

        if (!is_array($classes)) {
            throw new InvalidArgumentException('CSS Class(es) must be a space-delimited string or an array');
        }

        return array_filter($classes, 'strlen');
    }

    /**
     * Set the L10N display mode.
     *
     * @param  string $mode The L10N display mode.
     * @return FormPropertyWidget Chainable
     */
    public function setL10nMode($mode)
    {
        $this->l10nMode = $mode;
        return $this;
    }

    /**
     * Retrieve the L10N display mode.
     *
     * @return string
     */
    public function l10nMode()
    {
        return $this->l10nMode;
    }

    /**
     * Determine if the property should output for each language.
     *
     * @return boolean
     */
    public function loopL10n()
    {
        return ($this->l10nMode() === 'loop_inputs');
    }

    /**
     * Alias of {@see PropertyInterface::l10n()}.
     *
     * @return boolean
     */
    public function l10n()
    {
        return $this->property()->l10n();
    }

    /**
     * Retrieve the form control property.
     *
     * @return PropertyInputInterface
     */
    public function input()
    {
        if ($this->inputProperty === null) {
            $this->inputProperty = $this->createInputProperty();
        }

        return $this->inputProperty;
    }

    /**
     * Create the widget's form control property.
     *
     * @return PropertyInputInterface
     */
    private function createInputProperty()
    {
        $prop  = $this->property();
        $type  = $this->inputType();
        $input = $this->propertyInputFactory()->create($type);

        if ($this->formGroup() && ($input instanceof FormInputInterface)) {
            $input->setFormGroup($this->formGroup());
        }

        if ($input instanceof ViewableInterface) {
            $input->setViewController($this->viewController());
        }

        $input->setInputType($type);
        $input->setProperty($prop);
        $input->setPropertyVal($this->propertyVal());
        $input->setData($prop->data());

        $metadata = $prop->metadata();
        if (isset($metadata['admin'])) {
            $input->setData($metadata['admin']);
        }

        return $input;
    }

    /**
     * Retrieve the display property.
     *
     * @return PropertyDisplayInterface
     */
    public function display()
    {
        if ($this->displayProperty === null) {
            $this->displayProperty = $this->createDisplayProperty();
        }

        return $this->displayProperty;
    }

    /**
     * Create the widget's display property.
     *
     * @return PropertyDisplayInterface
     */
    private function createDisplayProperty()
    {
        $prop    = $this->property();
        $type    = $this->displayType();
        $display = $this->propertyDisplayFactory()->create($type);

        if ($this->formGroup() && ($display instanceof FormInputInterface)) {
            $display->setFormGroup($this->formGroup());
        }

        if ($display instanceof ViewableInterface) {
            $display->setViewController($this->viewController());
        }

        $display->setDisplayType($type);
        $display->setProperty($prop);
        $display->setPropertyVal($this->propertyVal());
        $display->setData($prop->data());

        $metadata = $prop->metadata();
        if (isset($metadata['admin'])) {
            $display->setData($metadata['admin']);
        }

        return $display;
    }

    /**
     * Yield the property output.
     *
     * Either a display property or a form control property.
     *
     * @return PropertyInputInterface|PropertyDisplayInterface
     */
    public function output()
    {
        $output = $this->outputType();
        switch ($output) {
            case static::PROPERTY_DISPLAY:
                $type   = $this->displayType();
                $prop   = $this->display();
                $getter = 'displayId';
                $setter = 'setDisplayId';
                break;

            case static::PROPERTY_CONTROL:
                $type   = $this->inputType();
                $prop   = $this->input();
                $getter = 'inputId';
                $setter = 'setInputId';
                break;
        }

        $GLOBALS['widget_template'] = $type;

        if ($this->l10n() && $this->loopL10n()) {
            $locales  = $this->translator()->availableLocales();
            $outputId = $prop->{$getter}();
            foreach ($locales as $langCode) {
                // Set a unique property output ID for each locale.
                $prop->{$setter}($outputId.'_'.$langCode);
                $prop->setLang($langCode);

                yield $prop;
            }

            $GLOBALS['widget_template'] = '';
            $prop->{$setter}($outputId);
        } else {
            yield $prop;

            $GLOBALS['widget_template'] = '';
        }
    }
}
