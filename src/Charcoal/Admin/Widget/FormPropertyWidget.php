<?php

namespace Charcoal\Admin\Widget;

use \RuntimeException;
use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From `charcoal-property`
use \Charcoal\Property\PropertyInterface;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationConfig;

// From 'charcoal-ui'
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;
use \Charcoal\Ui\FormInput\FormInputInterface;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;


// From 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;

/**
 *
 */
class FormPropertyWidget extends AdminWidget implements
    FormInputInterface
{
    /**
     * In memory copy of the PropertyInput object
     * @var PropertyInputInterface $input
     */
    private $input;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var string $inputType
     */
    protected $inputType;

    /**
     * @var array $inputOptions
     */
    protected $inputOptions;

    /**
     * @var string $propertyIdent
     */
    private $propertyIdent;

    /**
     * @var mixed $propertyVal
     */
    private $propertyVal;

    /**
     * @var array $propertyData
     */
    private $propertyData = [];

    /**
     * @var PropertyInterface $property
     */
    private $property;

    /**
     * @var boolean $active
     */
    private $active = true;

    /**
     * @var string $l10nMode
     */
    private $l10nMode;

    /**
     * The form group the input belongs to.
     *
     * @var FormGroupInterface
     */
    protected $formGroup;

    /**
     * @var PropertyFactory $factory
     */
    private $propertyFactory;

    /**
     * @var FactoryInterface $factory
     */
    private $propertyInputFactory;

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setPropertyFactory($container['property/factory']);
        $this->setPropertyInputFactory($container['property/input/factory']);
    }

    /**
     * @param  FactoryInterface $factory The property factory, used to create properties.
     * @return FormPropertyWidget Chainable
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;
        return $this;
    }

    /**
     * @throws RuntimeException If the property factory dependency was not set / injected.
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(
                'Property factory was not set'
            );
        }
        return $this->propertyFactory;
    }

    /**
     * @param  FactoryInterface $factory The property input factory, used to create property inputs.
     * @return FormPropertyWidget Chainable
     */
    protected function setPropertyInputFactory(FactoryInterface $factory)
    {
        $this->propertyInputFactory = $factory;
        return $this;
    }

    /**
     * @throws RuntimeException If the property input factory dependency was not set / injected.
     * @return FactoryInterface
     */
    public function propertyInputFactory()
    {
        if ($this->propertyInputFactory === null) {
            throw new RuntimeException(
                'Property input factory was not set'
            );
        }
        return $this->propertyInputFactory;
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
     * Set the widget and property data.
     *
     * @param  array|ArrayAccess $data Widget and property data.
     * @return FormPropertyWidget Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->propertyData = $data;

        return $this;
    }

    /**
     * Merge widget and property data.
     *
     * @param  array|\Traversable $data Widget and property data.
     * @return FormPropertyWidget Chainable
     */
    public function merge($data)
    {
        $this->propertyData = array_replace($this->propertyData, $data);

        return $this;
    }

    /**
     * @param  boolean $active The active flag.
     * @return FormPropertyWidget Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * @return boolean
     */
    public function active()
    {
        return $this->active;
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
     * @return string
     */
    public function propertyIdent()
    {
        return $this->propertyIdent;
    }

    /**
     * @param  mixed $propertyVal The property value.
     * @return FormPropertyWidget Chainable
     */
    public function setPropertyVal($propertyVal)
    {
        $this->propertyVal = $propertyVal;
        return $this;
    }

    /**
     * @return mixed
     */
    public function propertyVal()
    {
        return $this->propertyVal;
    }

    /**
     * @return boolean
     */
    public function showLabel()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function showDescription()
    {
        $description = $this->prop()->description();
        return !!$description;
    }

    /**
     * @return boolean
     */
    public function showNotes()
    {
        $prop = $this->prop();
        $show = $prop['show_notes'];

        if ($show === false) {
            return false;
        }

        $notes = $this->prop()->notes();

        return !!$notes;
    }

    /**
     * @return boolean
     */
    public function showNotesAbove()
    {
        $prop = $this->prop();
        $show = $prop['show_notes'];

        if ($show !== 'above') {
            return false;
        }

        $notes = $this->prop()->notes();

        return !!$notes;
    }

    /**
     * @return Translation|string|null
     */
    public function description()
    {
        return $this->prop()->description();
    }

    /**
     * @return Translation|string|null
     */
    public function notes()
    {
        return $this->prop()->notes();
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
     * @param  string $inputType The property input type.
     * @return FormPropertyWidget Chainable
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
        return $this;
    }

    /**
     * @return string
     */
    public function inputType()
    {
        if ($this->inputType === null) {
            $prop     = $this->prop();
            $metadata = $prop->metadata();

            $inputType = (isset($metadata['admin']) ? $metadata['admin']['input_type'] : '');

            if (!$inputType) {
                $inputType = 'charcoal/admin/property/input/text';
            }

            $this->inputType = $inputType;
        }
        return $this->inputType;
    }

    /**
     * @param  PropertyInterface $property The property.
     * @return FormPropertyWidget Chainable
     */
    public function setProp(PropertyInterface $property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return PropertyInterface
     */
    public function prop()
    {
        if ($this->property === null) {
            $p = $this->propertyFactory()->create($this->type());

            $p->setIdent($this->propertyIdent());
            $p->setData($this->propertyData);

            $this->property = $p;
        }

        return $this->property;
    }

    /**
     * @return array
     */
    public function availableLanguages()
    {
        $trans = TranslationConfig::instance();

        return $trans->availableLanguages();
    }

    /**
     * Determine if the form control's active language should be displayed.
     *
     * @return boolean
     */
    public function showActiveLanguage()
    {
        $prop  = $this->prop();
        $trans = TranslationConfig::instance();

        return ($trans->isMultilingual() && $prop->l10n());
    }

    /**
     * @param  string $mode The l10n mode.
     * @return FormPropertyWidget Chainable
     */
    public function setL10nMode($mode)
    {
        $this->l10nMode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function l10nMode()
    {
        return $this->l10nMode;
    }

    /**
     * @return boolean
     */
    public function loopL10n()
    {
        return ($this->l10nMode() == 'loop_inputs');
    }

    /**
     * @return PropertyInputInterface
     */
    public function input()
    {
        $inputType = $this->inputType();

        if ($this->input === null) {
            $prop = $this->prop();

            /** @todo Needs fix. Must be manually triggered after setting data for metadata to work */
            $metadata = $prop->metadata();

            $input = $this->propertyInputFactory()->create($inputType);

            if ($this->formGroup() && ($input instanceof FormInputInterface)) {
                $input->setFormGroup($this->formGroup());
            }

            $input->setInputType($inputType);
            $input->setProperty($prop);
            $input->setPropertyVal($this->propertyVal);
            $input->setData($prop->data());
            $input->setViewController($this->viewController());

            if (isset($metadata['admin'])) {
                $input->setData($metadata['admin']);
            }

            $this->input = $input;
        } else {
            $input = $this->input;
        }

        $GLOBALS['widget_template'] = $inputType;

        if ($this->loopL10n() && $prop->l10n()) {
            $langs = $this->availableLanguages();
            $inputId = $input->inputId();
            foreach ($langs as $lang) {
                // Set a unique input ID for language.
                $input->setInputId($inputId.'_'.$lang);
                $input->setLang($lang);

                yield $input;
            }
            $input->setInputId($inputId);
        } else {
            yield $input;
        }
    }
}
