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
use \Charcoal\Admin\Property\PropertyDisplayInterface;

/**
 *
 */
abstract class AbstractPropertyDisplay implements PropertyDisplayInterface
{
    private $ident;

    private $multiple;

    protected $type;
    protected $displayType;

    protected $displayId = null;
    protected $displayClass = '';

    private $propertyData = [];
    private $property;
    protected $displayOptions;

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
     * @return Display Chainable
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
     * @param string $displayId
     * @return Display Chainable
     */
    public function setDisplayId($displayId)
    {
        $this->displayId = $displayId;
        return $this;
    }

    /**
     * Get the display ID.
     *
     * If none was previously set, than a unique random one will be generated.
     *
     * @return string
     */
    public function displayId()
    {
        if (!$this->displayId) {
            $this->displayId = 'display_'.uniqid();
        }
        return $this->displayId;
    }

    /**
     * @param string $displayClass
     * @throws InvalidArgumentException
     * @return AbstractPropertyDisplay Chainable
     */
    public function setDisplayClass($displayClass)
    {
        if (!is_string($displayClass)) {
            throw new InvalidArgumentException(
                'Display class must be a string'
            );
        }
        $this->displayClass = $displayClass;
        return $this;
    }

    public function displayClass()
    {
        return $this->displayClass;
    }

    /**
     * The display name should always be the property's ident.
     *
     * @return string
     */
    public function displayName()
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
    public function displayVal()
    {
        $prop = $this->p();
        return $prop->displayVal();
    }

    /**
     * @param string $displayType
     * @throws InvalidArgumentException if provided argument is not of type 'string'.
     * @return  AbstractPropertyDisplay Chainable
     */
    public function setDisplayType($displayType)
    {
        if (!is_string($displayType)) {
            throw new InvalidArgumentException(
                'Display type must be a string.'
            );
        }
        $this->displayType = $displayType;
        return $this;
    }

    /**
     * @return string
     */
    public function displayType()
    {
        if ($this->displayType === null) {
            $this->displayType = 'charcoal/admin/property/display/text';
        }
        return $this->displayType;
    }

    /**
     * @param PropertyInterface $p
     * @return AbstractPropertyDisplay Chainable
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
            $this->property = $propertyFactory->create($this->displayType(), [
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
