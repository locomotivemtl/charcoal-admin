<?php

namespace Charcoal\Admin\Widget;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Admin\Ui\FormPropertyInterface;
use \Charcoal\Admin\Ui\FormPropertyTrait;
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Property\PropertyInputFactory;

// From `charcoal-core`
use \Charcoal\Property\PropertyFactory;
use \Charcoal\Property\PropertyInterface;

/**
*
*/
class FormPropertyWidget extends AdminWidget implements FormPropertyInterface
{
    use FormPropertyTrait;

    /**
    * In memory copy of the PropertyInput object
    * @var PropertyInputInterface $input
    */
    private $input;

    protected $type;

    protected $input_type;
    protected $input_options;

    private $property_ident;
    private $property_val;
    private $property_data = [];
    private $property;

    /**
    * @param boolean $active
    */
    private $active = true;


    private $property_factory;
    private $property_input_factory;

    /**
    * @param array $data
    * @return FormProperty Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->property_data = $data;

        return $this;
    }

    /**
    * @param boolean $active
    * @throws InvalidArgumentException
    * @return FormPropertyWidget Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException(
                'Active must be a boolean'
            );
        }
        $this->active = $active;
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
    public function set_property_ident($property_ident)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }
        $this->property_ident = $property_ident;
        return $this;
    }

    /**
    *
    */
    public function property_ident()
    {
        return $this->property_ident;
    }

    /**
    *
    */
    public function set_property_val($property_val)
    {
        $this->property_val = $property_val;
        return $this;
    }

    /**
    *
    */
    public function property_val()
    {
        return $this->property_val;
    }

    /**
    *
    */
    public function show_label()
    {
        return true;
    }

    /**
    * @return boolean
    */
    public function show_description()
    {
        $description = $this->prop()->description();
        return !!$description;
    }


    /**
    * @return boolean
    */
    public function show_notes()
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
    public function input_id()
    {
        return 'input_id';
    }

    /**
    * @return string
    */
    public function input_name()
    {
        return 'input_name';
    }

    /**
    *
    */
    public function set_input_type($input_type)
    {
        $this->input_type = $input_type;
        return $this;
    }

    /**
    *
    */
    public function input_type()
    {
        if ($this->input_type === null) {
            try {
                $prop = $this->prop();
                $metadata = $prop->metadata();
                $input_type = isset($metadata['admin']) ? $metadata['admin']['input_type'] : '';

            } catch (Exception $e) {
                $input_type = 'charcoal/admin/property/input/text';
            }
            if (!$input_type) {
                $input_type = 'charcoal/admin/property/input/text';
            }
            $this->input_type = $input_type;
        }
        return $this->input_type;
    }

    /**
    * @param PropertyInterface $property
    * @return FormProperty Chainable
    */
    public function set_prop(PropertyInterface $property)
    {
        $this->property = $property;
        //$this->property->set_val($this->property_val());
        return $this;
    }

    /**
    * @return PropertyInterface
    */
    public function prop()
    {
        if ($this->property === null) {
            $p = $this->property_factory()->get($this->type());


            $p->set_ident($this->property_ident());
            $p->set_data($this->property_data);

            $this->property = $p;
        }
        $this->property->set_val($this->property_val());
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
        $input_type = $this->input_type();

        $this->input = $this->property_input_factory()->create($input_type);
        $this->input->set_property($prop);
        $this->input->set_data($this->property_data);

        $GLOBALS['widget_template'] = $input_type;
        return $this->input;
    }

    private function property_factory()
    {
        if ($this->property_factory === null) {
            $this->property_factory = new PropertyFactory();
        }
        return $this->property_factory;
    }

    private function property_input_factory()
    {
        if ($this->property_input_factory === null) {
            $this->property_input_factory = new PropertyInputFactory();
        }
        return $this->property_input_factory;
    }

}
