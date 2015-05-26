<?php

namespace Charcoal\Admin\Property;

use \InvalidArgumentException as InvalidArgumentException;

class Input
{
    private $_ident;

    private $_read_only;
    private $_required;
    private $_disabled;

    protected $_type;
    protected $_input_type;
    protected $_input_options;

    protected $_input_id;
    protected $_input_name;
    protected $_input_class;

    private $_property_data = [];
    private $_property;

    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        //parent::set_data($data);
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['read_only']) && $data['read_only'] !== null) {
            $this->set_read_only($data['read_only']);
        }
        if (isset($data['required']) && $data['required'] !== null) {
            $this->set_required($data['required']);
        }
        if (isset($data['disabled']) && $data['disabled'] !== null) {
            $this->set_disabled($data['disabled']);
        }
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if (isset($data['input_type']) && $data['input_type'] !== null) {
            $this->set_input_type($data['input_type']);
        }
        // input_options
        // input_name
        // input_id
        // input_class
        // property_data

        $this->_property_data = $data;

        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException if the ident is not a string
    * @return Widget (Chainable)
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->_ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->_ident;
    }

    /**
    * @param boolean $read_only
    * @throws InvalidArgumentException if the read_only is not a string
    * @return Widget (Chainable)
    */
    public function set_read_only($read_only)
    {
        if (!is_bool($read_only)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - read_only must be a boolean.');
        }
        $this->_read_only = $read_only;
        return $this;
    }

    /**
    * @return boolean
    */
    public function read_only()
    {
        return $this->_read_only;
    }

    /**
    * @param boolean $required
    * @throws InvalidArgumentException if the required is not a string
    * @return Widget (Chainable)
    */
    public function set_required($required)
    {
        if (!is_bool($required)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - required must be a boolean.');
        }
        $this->_required = $required;
        return $this;
    }

    /**
    * @return boolean
    */
    public function required()
    {
        return $this->_required;
    }


    /**
    * @param boolean $disabled
    * @throws InvalidArgumentException if the disabled is not a string
    * @return Widget (Chainable)
    */
    public function set_disabled($disabled)
    {
        if (!is_bool($disabled)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - disabled must be a boolean.');
        }
        $this->_disabled = $disabled;
        return $this;
    }

    /**
    * @return boolean
    */
    public function disabled()
    {
        return $this->_disabled;
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
