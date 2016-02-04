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

    protected $type;

    protected $inputType;
    protected $inputOptions;

    private $propertyIdent;
    private $propertyVal;
    private $propertyData = [];
    private $property;

    /**
     * @param boolean $active
     */
    private $active = true;


    private $propertyFactory;
    private $propertyInputFactory;

    /**
     * @param array $data
     * @return FormProperty Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->propertyData = $data;

        return $this;
    }

    /**
     * @param boolean $active
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
     * @param string $property
     * @throws InvalidArgumentException
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
     *
     */
    public function propertyIdent()
    {
        return $this->propertyIdent;
    }

    /**
     *
     */
    public function setPropertyVal($propertyVal)
    {
        $this->propertyVal = $propertyVal;
        return $this;
    }

    /**
     *
     */
    public function propertyVal()
    {
        return $this->propertyVal;
    }

    /**
     *
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
     *
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
        return $this;
    }

    /**
     *
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
     * @param PropertyInterface $property
     * @return FormProperty Chainable
     */
    public function setProp(PropertyInterface $property)
    {
        $this->property = $property;
        //$this->property->setVal($this->propertyVal());
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
     * @return PropertyInputInterface
     */
    public function input()
    {
        if ($this->input !== null) {
            return $this->input;
        }
        $prop = $this->prop();
        $inputType = $this->inputType();

        $this->input = $this->propertyInputFactory()->create($inputType, [
            'logger'=>$this->logger
        ]);
        $this->input->setProperty($prop);
        $this->input->setData($this->propertyData);

        $GLOBALS['widget_template'] = $inputType;
        return $this->input;
    }

    private function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            $this->propertyFactory = new PropertyFactory();
        }
        return $this->propertyFactory;
    }

    private function propertyInputFactory()
    {
        if ($this->propertyInputFactory === null) {
            $this->propertyInputFactory = new PropertyInputFactory();
        }
        return $this->propertyInputFactory;
    }
}
