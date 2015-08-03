<?php

namespace Charcoal\Admin\Property;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-base`
use \Charcoal\Property\PropertyInterface as PropertyInterface;

// Local namespace dependencies
use \Charcoal\Admin\Property\PropertyInputInterface as PropertyInputInterface;

/**
*
*/
abstract class AbstractPropertyInput implements PropertyInputInterface
{
    private $_ident;

    private $_read_only;
    private $_required;
    private $_disabled;

    protected $_type;
    protected $_input_type;
    protected $_input_options;

    protected $_input_id = null;
    protected $_input_class = '';
    //protected $_input_name;

    private $_property_data = [];
    private $_property;

    /**
    * @param array $data
    * @return Input Chainable
    */
    public function set_data(array $data)
    {
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
        /*if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }*/
        if (isset($data['input_type']) && $data['input_type'] !== null) {
            $this->set_input_type($data['input_type']);
        }
        if (isset($data['input_id']) && $data['input_id'] !== null) {
            $this->set_input_id($data['input_id']);
        }
        if (isset($data['input_class']) && $data['input_class'] !== null) {
            $this->set_input_class($data['input_class']);
        }
        // input_options
        // input_name
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
    * @throws InvalidArgumentException if the read_only is not a boolean
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

    /**
    * @param string $input_id
    * @return Input Chainable
    */
    public function set_input_id($input_id)
    {
        $this->_input_id = $input_id;
        return $this;
    }

    /**
    * Get the input ID.
    *
    * If none was previously set, than a unique random one will be generated.
    *
    * @return string
    */
    public function input_id()
    {
        if (!$this->_input_id) {
            $this->_input_id = 'input_'.uniqid();
        }
        return $this->_input_id;
    }

    /**
    * @param string $input_class
    * @throws InvalidArgumentException
    * @return AbstractPropertyInput Chainable
    */
    public function set_input_class($input_class)
    {
        if(!is_string($input_class)) {
            throw new InvalidArgumentException('Input class must be a string');
        }
        $this->_input_class = $input_class;
        return $this;
    }

    public function input_class()
    {
        return $this->_input_class;
    }

    /**
    * The input name should always be the property's ident.
    *
    * @return string
    */
    public function input_name()
    {
        return $this->p()->ident();
    }

    /**
    * @return string
    */
    public function input_val()
    {
        return $this->p()->val();
    }

    public function set_input_type($input_type)
    {
        if (!is_string($input_type)) {
            throw new InvalidArgumentException('Input type must be a string.');
        }
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


    public function set_property(PropertyInterface $p)
    {
        $this->_property = $p;
        return $this;
    }

    /**
    * @return PropertyInterface
    */
    public function property()
    {
        if ($this->_property === null) {
            $this->_property = PropertyFactory::instance()->get($this->type());
            $this->_property->set_data($this->_property_data);
        }
        return $this->_property;
    }

    /**
    * Alias of the `property` method.
    *
    * @return PropertyInterface
    */
    public function p()
    {
        return $this->property();
    }
}
