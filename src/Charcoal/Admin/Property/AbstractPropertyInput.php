<?php

namespace Charcoal\Admin\Property;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-base`
use \Charcoal\Property\PropertyFactory as PropertyFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

// From `charcoal-core`
use \Charcoal\Translation\TranslationConfig;

// Local namespace dependencies
use \Charcoal\Admin\Property\PropertyInputInterface as PropertyInputInterface;

/**
*
*/
abstract class AbstractPropertyInput implements PropertyInputInterface
{
    private $ident;

    private $read_only;
    private $required;
    private $disabled;
    private $multiple;

    protected $type;
    protected $input_type;
    protected $input_options;

    protected $input_id = null;
    protected $input_class = '';
    //protected $input_name;

    private $property_data = [];
    private $property;

    /**
    * This function takes an array and fill the model object with its value.
    *
    * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
    *
    * For example, calling with `set_data(['properties'=>$properties])` would call
    *`set_properties($properties)`, becasue `set_properties()` exists.
    *
    * But calling with `set_data(['foobar'=>$foo])` would set the `$foobar` member
    * on the metadata object, because the method `set_foobar()` does not exist.
    *
    * @param array $data
    * @return Input Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        $this->property_data = $data;

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
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
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
        $this->read_only = $read_only;
        return $this;
    }

    /**
    * @return boolean
    */
    public function read_only()
    {
        return $this->read_only;
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
        $this->required = $required;
        return $this;
    }

    /**
    * @return boolean
    */
    public function required()
    {
        return $this->required;
    }


    /**
    * @param boolean $disabled
    * @return Widget (Chainable)
    */
    public function set_disabled($disabled)
    {
        $this->disabled = !!$disabled;
        return $this;
    }

    /**
    * @return boolean
    */
    public function disabled()
    {
        return $this->disabled;
    }

    /**
    * @param boolean $multiple
    * @return Widget (Chainable)
    */
    public function set_multiple($multiple)
    {
        $this->multiple = !!$multiple;
        return $this;
    }

    /**
    * @return boolean
    */
    public function multiple()
    {
        return $this->multiple;
    }

    /**
    * @param string $input_id
    * @return Input Chainable
    */
    public function set_input_id($input_id)
    {
        $this->input_id = $input_id;
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
        if (!$this->input_id) {
            $this->input_id = 'input_'.uniqid();
        }
        return $this->input_id;
    }

    /**
    * @param string $input_class
    * @throws InvalidArgumentException
    * @return AbstractPropertyInput Chainable
    */
    public function set_input_class($input_class)
    {
        if (!is_string($input_class)) {
            throw new InvalidArgumentException('Input class must be a string');
        }
        $this->input_class = $input_class;
        return $this;
    }

    public function input_class()
    {
        return $this->input_class;
    }

    /**
    * The input name should always be the property's ident.
    *
    * @return string
    */
    public function input_name()
    {
        $name = $this->p()->ident();
        if ($this->multiple()) {
            $name .= '[]';
        }
        if ($this->p()->l10n()) {
            $name .= '[fr]';
        }
        return $name;
    }

    /**
    * @return string
    */
    public function input_val()
    {
        $prop = $this->p();
        $val = $prop->val();

        if ($val === null) {
            return '';
        }

        if ($prop->l10n()) {
            $lang = TranslationConfig::instance()->current_language();

            if (isset($val[$lang])) {
                $val = $val[$lang];
            }
        }

        if (!is_scalar($val)) {
            $val = json_encode($val, true);
        }

        return $val;
    }

    /**
    * @param string $input_type
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return  AbstractPropertyInput Chainable
    */
    public function set_input_type($input_type)
    {
        if (!is_string($input_type)) {
            throw new InvalidArgumentException('Input type must be a string.');
        }
        $this->input_type = $input_type;
        return $this;
    }

    /**
    * @return string
    */
    public function input_type()
    {
        if ($this->input_type === null) {
            $this->input_type = 'charcoal/admin/property/input/text';
        }
        return $this->input_type;
    }

    /**
    * @param PropertyInterface $p
    * @return AbstractPropertyInput Chainable
    */
    public function set_property(PropertyInterface $p)
    {
        $this->property = $p;
        return $this;
    }

    /**
    * @return PropertyInterface
    */
    public function property()
    {
        if ($this->property === null) {
            $property_factory = new PropertyFactory();
            $this->property = $property_factory->create($this->input_type());
            $this->property->set_data($this->property_data);
        }
        return $this->property;
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
