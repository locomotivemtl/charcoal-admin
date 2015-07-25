<?php

namespace Charcoal\Admin\Widget;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\AdminWidget as AdminWidget;
use \Charcoal\Admin\Property\PropertyInputFactory as PropertyInputFactory;

// From `charcoal-core`
use \Charcoal\Property\PropertyFactory as PropertyFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

/**
*
*/
class FormPropertyWidget extends AdminWidget
{
    protected $_type;
    protected $_input_type;
    protected $_input_options;

    private $_property_ident;
    private $_property_val;
    private $_property_data = [];
    private $_property;

    private $_active = true;

    /**
    * @param array $data
    * @return FormProperty Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if (isset($data['input_type']) && $data['input_type'] !== null) {
            $this->set_input_type($data['input_type']);
        }
        if (isset($data['property_ident']) && $data['property_ident'] !== null) {
            $this->set_property_ident($data['property_ident']);
        }
        if (isset($data['property_val']) && $data['property_val'] !== null) {
            $this->set_property_val($data['property_val']);
        }
        if (isset($data['active']) && $data['active'] !==null) {
            $this->set_active($data['active']);
        }

        // Keep the data in copy, this will be passed to the property and/or input later
        $this->_property_data = $data;

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
            throw new InvalidArgumentException('Active must be a boolean');
        }
        $this->_active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->_active;
    }

    /**
    * @param string $property
    * @throws InvalidArgumentException
    * @return FormPropertyWidget
    */
    public function set_property_ident($property_ident)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException('Property ident must be a string');
        }
        $this->_property_ident = $property_ident;
        return $this;
    }

    /**
    *
    */
    public function property_ident()
    {
        return $this->_property_ident;
    }

    /**
    *
    */
    public function set_property_val($property_val)
    {
        $this->_property_val = $property_val;
        return $this;
    }

    /**
    *
    */
    public function property_val()
    {
        return $this->_property_val;
    }

    /**
    *
    */
    public function show_label()
    {
        return true;
    }

    /**
    *
    */
    public function show_description()
    {
        return true;
    }

    /**
    *
    */
    public function show_header()
    {
        return true;
    }

    /**
    *
    */
    public function show_footer()
    {
        return true;
    }

    /**
    *
    */
    public function show_notes()
    {
        return true;
    }

    /**
    *
    */
    public function description()
    {
        return 'Property Description';
    }

    /**
    *
    */
    public function notes()
    {
        return 'Property Notes';
    }

    /**
    *
    */
    public function input_id()
    {
        return 'input_id';
    }

    /**
    *
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
        $this->_input_type = $input_type;
        return $this;
    }

    /**
    *
    */
    public function input_type()
    {
        if ($this->_input_type === null) {
            try {
                $prop = $this->prop();
                $metadata = $prop->metadata();
                $admin = $metadata['admin'];
                $input_type = $admin['input_type'];

            } catch (Exception $e) {
                $input_type = 'charcoal/admin/property/input/text';
            }
            if (!$input_type) {
                $input_type = 'charcoal/admin/property/input/text';
            }
            $this->_input_type = $input_type;
        }
        return $this->_input_type;
    }

    /**
    * @param PropertyInterface $property
    * @return FormProperty Chainable
    */
    public function set_prop(PropertyInterface $property)
    {
        $this->_property = $property;
        //$this->_property->set_val($this->property_val());
        return $this;
    }

    /**
    * @return PropertyInterface
    */
    public function prop()
    {
        if ($this->_property === null) {
            //var_dump($this->ident());
            $p = PropertyFactory::instance()->get($this->type());


            $p->set_ident($this->property_ident());
            $p->set_data($this->_property_data);

            $this->_property = $p;
        }
        $this->_property->set_val($this->property_val());
        return $this->_property;
    }

    /**
    * @return PropertyInputInterface
    */
    public function input()
    {
        $prop = $this->prop();
        $input_type = $this->input_type();

        $input = PropertyInputFactory::instance()->get($input_type);
        $input->set_property($prop);
        $input->set_data($this->_property_data);
        $GLOBALS['widget_template'] = $input_type;
        return $input;
    }

}
