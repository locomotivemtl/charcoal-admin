<?php

namespace Charcoal\Admin\Widget;

// Dependencies from `PHP`
use \InvalidArgumentException;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Property\PropertyInputFactory;

// From `charcoal-core`
use \Charcoal\Property\PropertyFactory;
use \Charcoal\Property\PropertyInterface;

/**
 *
 */
class FormPropertyWidget extends AdminWidget
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
     * @var PropertyFactory $factory
     */
    private $propertyFactory;

    /**
     * @var PropertyInputFactory $factory
     */
    private $propertyInputFactory;

    /**
     * @param array|ArrayInterface $data The widget AND property data.
     * @return FormProperty Chainable
     */
    public function setData($data)
    {
        parent::setData($data);

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->propertyData = $data;

        return $this;
    }

    /**
     * @param boolean $active The active flag.
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
     * @param string $propertyIdent The property ident.
     * @throws InvalidArgumentException If the property ident is not a string.
     * @return FormPropertyWidget
     */
    public function setPropertyIdent($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }
        $this->propertyIdent = $propertyIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function propertyIdent()
    {
        return $this->propertyIdent;
    }

    /**
     * @param mixed $propertyVal The property value.
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
        $notes = $this->prop()->notes();
        return !!$notes;
    }

    /**
     * @return TranslationString
     */
    public function description()
    {
        return $this->prop()->description();
    }

    /**
     * @return TranslationString
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
     * @param string $inputType The property input type.
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
            $prop = $this->prop();
            $metadata = $prop->metadata();
            $inputType = isset($metadata['admin']) ? $metadata['admin']['input_type'] : '';

            if (!$inputType) {
                $inputType = 'charcoal/admin/property/input/text';
            }
            $this->inputType = $inputType;
        }
        return $this->inputType;
    }

    /**
     * @param PropertyInterface $property The property.
     * @return FormProperty Chainable
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
            $p = $this->propertyFactory()->get($this->type(), [
                'logger'=>$this->logger
            ]);


            $p->setIdent($this->propertyIdent());
            $p->setData($this->propertyData);

            $this->property = $p;
        }
        $this->property->setVal($this->propertyVal());
        return $this->property;
    }

    /**
     * @return array
     */
    public function langs()
    {
        $langs = \Charcoal\Translation\TranslationConfig::instance()->availableLanguages();

        return $langs;
    }

    /**
     * @return PropertyInputInterface
     */
    public function input()
    {
        $prop = $this->prop();

        $inputType = $this->inputType();
        $this->input = $this->propertyInputFactory()->create($inputType, [
            'logger'=>$this->logger
        ]);

        $this->input->setProperty($prop);
        $this->input->setPropertyVal($this->propertyVal);
        $this->input->setData($this->propertyData);

        $GLOBALS['widget_template'] = $inputType;

        // Currently disabled.
        $loopL10n = false;

        $res = [];
        if ($loopL10n && $prop->l10n()) {
            $langs = $this->langs();
            foreach ($langs as $lang) {
                $this->input->setLang($lang);
                yield $this->input;
            }
        } else {
            yield $this->input;
        }
    }

    /**
     * @return PropertyFactory
     */
    private function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            $this->propertyFactory = new PropertyFactory();
        }
        return $this->propertyFactory;
    }

    /**
     * @return PropertyInputFactory
     */
    private function propertyInputFactory()
    {
        if ($this->propertyInputFactory === null) {
            $this->propertyInputFactory = new PropertyInputFactory();
        }
        return $this->propertyInputFactory;
    }
}
