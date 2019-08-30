<?php

namespace Charcoal\Admin\Property;

use Traversable;
use InvalidArgumentException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\DescribableTrait;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-property'
use Charcoal\Property\PropertyFactory;
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\PropertyMetadata;

// From 'charcoal-admin'
use Charcoal\Admin\Property\PropertyDisplayInterface;

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
    use TranslatorAwareTrait;

    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $ident;

    /**
     * @var boolean
     */
    private $multiple;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $displayType;

    /**
     * @var string
     */
    protected $displayName;

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
     * @param array $data The display data.
     * @return self
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
     * @param mixed $val The property value.
     * @return self
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
     * @param string $lang The language code / ident.
     * @return self
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Get the input language
     * @return string
     */
    public function lang()
    {
        if ($this->lang === null) {
            return $this->translator()->getLocale();
        }

        return $this->lang;
    }

    /**
     * @param string $ident Display identifier.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return self
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Property Display identifier must be string'
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setDisplayClass($displayClass)
    {
        if (!is_string($displayClass)) {
            throw new InvalidArgumentException('CSS Class(es) must be a string');
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
     * Set the display name.
     *
     * Used for the HTML "name" attribute.
     *
     * @param  string $displayName HTML id attribute.
     * @return self
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Retrieve the display name.
     *
     * The input name should always be the property's ident.
     *
     * @return string
     */
    public function displayName()
    {
        if ($this->displayName) {
            $name = $this->displayName;
        } else {
            $name = $this->propertyIdent();
        }

        if ($this->p()['l10n']) {
            $name .= '['.$this->lang().']';
        }

        if ($this->multiple()) {
            $name .= '[]';
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
                'Property Display Type must be a string.'
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
     * @return string
     */
    public function propertyIdent()
    {
        return $this->p()['ident'];
    }

    /**
     * @param PropertyInterface $p The property.
     * @return AbstractPropertyDisplay Chainable
     */
    public function setProperty(PropertyInterface $p)
    {
        $this->property = $p;
        $this->displayName = null;

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
     * Alias of {@see self::property()}
     *
     * @return PropertyInterface
     */
    public function p()
    {
        return $this->property();
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        // Fullfill TranslatorAwareTrait dependencies
        $this->setTranslator($container['translator']);
    }

    /**
     * Create a new metadata object.
     *
     * @param  array $data Optional metadata to merge on the object.
     * @return PropertyMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $class = $this->metadataClass();
        return new $class($data);
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @return string
     */
    protected function metadataClass()
    {
        return PropertyMetadata::class;
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
