<?php

namespace Charcoal\Admin\Property;

use \Traversable;
use \UnexpectedValueException;
use \InvalidArgumentException;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

// Dependency from Pimple
use \Pimple\Container;

// Dependencies from 'charcoal-core'
use \Charcoal\Model\DescribableInterface;
use \Charcoal\Model\DescribableTrait;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationConfig;

// Dependencies from 'charcoal-property'
use \Charcoal\Property\PropertyFactory;
use \Charcoal\Property\PropertyInterface;
use \Charcoal\Property\PropertyMetadata;

// Local dependencies
use \Charcoal\Admin\Property\PropertyDisplayInterface;

/**
 *
 */
abstract class AbstractPropertyDisplay implements
    DescribableInterface,
    PropertyDisplayInterface,
    LoggerAwareInterface
{
    use DescribableTrait;
    use LoggerAwareTrait;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var boolean $multiple
     */
    private $multiple;

    /**
     * @var string $type
     */
    protected $type;
    /**
     * @var string $displayType
     */
    protected $displayType;

    /**
     * @var string $displayId
     */
    protected $displayId = null;
    /**
     * @var string $displayClass
     */
    protected $displayClass = '';

    /**
     * @var array $propertyData
     */
    private $propertyData = [];

    /**
     * @var mixed $propertyVal
     */
    private $propertyVal;

    /**
     * @var PropertyInterface $property
     */
    private $property;

    /**
     * @var mixed $displayOptions
     */
    protected $displayOptions;

    /**
     * @param array|ArrayAccess $data Optional. Dependencies.
     */
    public function __construct($data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);

        // DI Container can optionally be set in property constructor.
        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children method
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
     * @param array|Traversable $data The display data.
     * @return Display Chainable
     */
    public function setData($data)
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
     * @param array $data Optional. Metadata data.
     * @return PropertyMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $metadata = new PropertyMetadata();
        if (is_array($data)) {
            $metadata->setData($data);
        }
        return $metadata;
    }

    /**
     * @param mixed $val The property value.
     * @return PropertyDisplayInterface Chainable
     */
    public function setPropertyVal($val)
    {
        $this->propertyVal = $val;
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
     * @param string $ident Display identifier.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return Widget Chainable
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
     * @param boolean $multiple Multiple flag.
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
     * @param string $displayId HTML id attribute.
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
     * @param string $displayClass The display class attribute.
     * @throws InvalidArgumentException If the class is not a string.
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

    /**
     * @return string
     */
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
        return $prop->displayVal($this->propertyVal());
    }

    /**
     * @param string $displayType The display type.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
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
     * @param PropertyInterface $p The property.
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
