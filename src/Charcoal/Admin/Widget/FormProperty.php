<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Widget as Widget;

// From `charcoal-core`
use \Charcoal\Property\PropertyFactory as PropertyFactory;

class FormProperty extends Widget
{
    private $_type;
    private $_input_type;
    private $_input_options;

    private $_property_data = [];
    private $_property;

    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        parent::set_data($data);
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if (isset($data['input_type']) && $data['input_type'] !== null) {
            $this->set_input_type($data['input_type']);
        }

        $this->_property_data = $data;

        return $this;
    }

    public function show_label()
    {
        return true;
    }

    public function show_description()
    {
        return true;
    }

    public function show_header()
    {
        return true;
    }

    public function show_footer()
    {
        return true;
    }

    public function show_notes()
    {
        return true;
    }

    public function label()
    {
        return 'Property Label';
    }

    public function description()
    {
        return 'Property Description';
    }

    public function notes()
    {
        return 'Property Notes';
    }

    public function input_id()
    {
        return 'input_id';
    }

    public function input_name()
    {
        return 'input_name';
    }

    public function set_input_type($input_type)
    {
        $this->_input_type = $input_type;
        return $this;
    }

    public function input_type()
    {
        if ($this->_input_type === null) {
            $this->_input_type = 'charcoal/admin/property/input/text';
        }
        return $this->_input_type;
    }

    public function property()
    {
        if ($this->_property === null) {
            $this->_property = PropertyFactory::instance()->get($this->type());
            $this->_property->set_data($this->_property_data);
        }
        return $this->_property;
    }

    public function p()
    {
        return $this->property();
    }
}
