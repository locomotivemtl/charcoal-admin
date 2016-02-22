<?php

namespace Charcoal\Admin\Property;

use \InvalidArgumentException;

// PSR-3 logger dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyFactory;
use \Charcoal\Property\PropertyInterface;

// Module `charcoal-translation` dependencies
use \Charcoal\Translation\TranslationConfig;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Property\PropertyInputInterface;

/**
 *
 */
abstract class AbstractPropertyInput implements
    PropertyInputInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $ident;

    private $readOnly;
    private $required;
    private $disabled;
    private $multiple;

    protected $type;
    protected $inputType;

    protected $inputId = null;
    protected $inputClass = '';
    //protected $inputName;

    private $propertyData = [];
    private $property;

    public function __construct($data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);
    }

    /**
     * This function takes an array and fill the model object with its value.
     *
     * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
     *
     * For example, calling with `setData(['properties'=>$properties])` would call
     * `setProperties($properties)`, becasue `setProperties()` exists.
     *
     * But calling with `setData(['foobar'=>$foo])` would set the `$foobar` member
     * on the metadata object, because the method `set_foobar()` does not exist.
     *
     * @param array $data
     * @return Input Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, $this->setter($prop)];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        $this->propertyData = $data;

        return $this;
    }

    /**
     * @param string $ident
     * @throws InvalidArgumentException if the ident is not a string
     * @return Widget (Chainable)
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.'
            );
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
     * @param boolean $readOnly
     * @throws InvalidArgumentException if the readOnly is not a boolean
     * @return Widget (Chainable)
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = !!$readOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function readOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param boolean $required
     * @return Widget (Chainable)
     */
    public function setRequired($required)
    {
        $this->required = !!$required;
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
    public function setDisabled($disabled)
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
    public function setMultiple($multiple)
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
     * @param string $inputId
     * @return Input Chainable
     */
    public function setInputId($inputId)
    {
        $this->inputId = $inputId;
        return $this;
    }

    /**
     * Get the input ID.
     *
     * If none was previously set, than a unique random one will be generated.
     *
     * @return string
     */
    public function inputId()
    {
        if (!$this->inputId) {
            $this->inputId = 'input_'.uniqid();
        }
        return $this->inputId;
    }

    /**
     * @param string $inputClass
     * @throws InvalidArgumentException
     * @return AbstractPropertyInput Chainable
     */
    public function setInputClass($inputClass)
    {
        if (!is_string($inputClass)) {
            throw new InvalidArgumentException(
                'Input class must be a string'
            );
        }
        $this->inputClass = $inputClass;
        return $this;
    }

    public function inputClass()
    {
        return $this->inputClass;
    }

    /**
     * The input name should always be the property's ident.
     *
     * @return string
     */
    public function inputName()
    {
        $name = $this->p()->ident();
        if ($this->multiple()) {
            $name .= '[]';
        }
        if ($this->p()->l10n()) {
            $lang = TranslationConfig::instance()->currentLanguage();
            $name .= '['.$lang.']';
        }
        return $name;
    }

    /**
     * @return string
     */
    public function inputVal()
    {
        $prop = $this->p();
        $val = $prop->val();

        if ($val === null) {
            return '';
        }

        if ($prop->l10n()) {
            $lang = TranslationConfig::instance()->currentLanguage();

            if (isset($val[$lang])) {
                $val = $val[$lang];
            }
        }

        // if (!is_scalar($val)) {
        //     $val = json_encode($val, true);
        // }

        return $val;
    }

    /**
     * @param string $inputType
     * @throws InvalidArgumentException if provided argument is not of type 'string'.
     * @return  AbstractPropertyInput Chainable
     */
    public function setInputType($inputType)
    {
        if (!is_string($inputType)) {
            throw new InvalidArgumentException(
                'Input type must be a string.'
            );
        }
        $this->inputType = $inputType;
        return $this;
    }

    /**
     * @return string
     */
    public function inputType()
    {
        if ($this->inputType === null) {
            $this->inputType = 'charcoal/admin/property/input/text';
        }
        return $this->inputType;
    }

    /**
     * @param PropertyInterface $p
     * @return AbstractPropertyInput Chainable
     */
    public function setProperty(PropertyInterface $p)
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
            $propertyFactory = new PropertyFactory();
            $this->property = $propertyFactory->create($this->inputType(), [
                'logger'=>$this->logger
            ]);
            $this->property->setData($this->propertyData);
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

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);

    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
