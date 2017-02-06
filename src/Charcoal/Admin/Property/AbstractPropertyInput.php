<?php

namespace Charcoal\Admin\Property;

use Traversable;
use UnexpectedValueException;
use InvalidArgumentException;

use Pimple\Container;

// Dependencies from PSR-3 (Logger)
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

// Dependencies from 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\DescribableTrait;

// Dependencies from 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;

// Dependencies from 'charcoal-translation'
use Charcoal\Translator\Translator;
use Charcoal\Translator\Translation;

// Dependency from 'charcoal-admin'
use Charcoal\Admin\Property\PropertyInputInterface;

// Local namespace dependencies
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\PropertyMetadata;

/**
 *
 */
abstract class AbstractPropertyInput implements
    DescribableInterface,
    PropertyInputInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    use DescribableTrait;
    use LoggerAwareTrait;
    use ViewableTrait;

    const DEFAULT_INPUT_TYPE = 'text';

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var boolean $readOnly
     */
    private $readOnly;

    /**
     * @var boolean $required
     */
    private $required;

    /**
     * @var boolean $disabled
     */
    private $disabled;

    /**
     * @var boolean $multiple
     */
    private $multiple;

    /**
     * The control type for the HTML element `<input>`.
     *
     * @var string $type
     */
    protected $type;

    /**
     * @var string $inputType
     */
    protected $inputType;

    /**
     * @var string $inputMode
     */
    protected $inputMode;

    /**
     * @var string $inputName
     */
    protected $inputName;

    /**
     * @var string $inputId
     */
    protected $inputId;

    /**
     * @var string $inputClass
     */
    protected $inputClass = '';

    /**
     * @var Translation $placeholder
     */
    private $placeholder;

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
     * @var Translator
     */
    private $translator;

    /**
     * @param array|\ArrayAccess $data Constructor data.
     */
    public function __construct($data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);

        if (isset($data['metadata_loader'])) {
            $this->setMetadataLoader($data['metadata_loader']);
        }

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
        $this->setTranslator($container['translator']);
        $this->setView($container['view']);
        $this->setMetadataLoader($container['metadata/loader']);
    }

        /**
         * @param Translator $translator The translator service.
         * @return void
         */
    private function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return Translator
     */
    protected function translator()
    {
        return $this->translator;
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
     * @param array $data The input data.
     * @return AbstractPropertyInput Chainable
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
     * @return PropertyInputInterface Chainable
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
     * @return PropertyInputInterface Chainable
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
     * @return boolean
     */
    public function hidden()
    {
        if ($this->p()->l10n()) {
            if ($this->lang() != $this->translator()->getLocale()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $ident Input identifier.
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
     * @param boolean $readOnly The read-only flag.
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
     * @param boolean $required Required flag.
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
     * @param boolean $disabled Disabled flag.
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
     * Set the form control's placeholder.
     *
     * A placeholder is a hint to the user of what can be entered
     * in the property control.
     *
     * @param mixed $placeholder The placeholder attribute.
     * @return AbstractPropertyInput Chainable
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $this->translator()->translation($placeholder);
        $this->placeholder->isRendered = false;

        return $this;
    }

    /**
     * Retrieve the placeholder.
     *
     * @return string
     */
    public function placeholder()
    {
        if ($this->placeholder === null) {
            $metadata = $this->metadata();

            if (isset($metadata['data']['placeholder'])) {
                $this->setPlaceholder($metadata['data']['placeholder']);
            }
        }

        if (isset($this->placeholder->isRendered) && !$this->placeholder->isRendered) {
            $this->renderPlaceholder();
        }

        return $this->placeholder;
    }

    /**
     * Render the placeholders.
     *
     * @todo Implement data presenter as a better alternative.
     * @return AbstractPropertyInput Chainable
     */
    protected function renderPlaceholder()
    {
        if ($this->placeholder instanceof Translation) {
            foreach ($this->placeholder->data() as $lang => $value) {
                if ($value && $this instanceof ViewableInterface && $this->view() !== null) {
                    $this->placeholder[$lang] = $this->view()->render($value, $this->viewController());
                }
            }

            $this->placeholder->isRendered = true;
        }

        return $this;
    }

    /**
     * Set the input ID.
     *
     * Used for the HTML "ID" attribute.
     *
     * @param  string $inputId HTML input id attribute.
     * @return AbstractPropertyInput Chainable
     */
    public function setInputId($inputId)
    {
        $this->inputId = $inputId;

        return $this;
    }

    /**
     * Retrieve the input ID.
     *
     * If none was previously set then a unique random one will be generated.
     *
     * @return string
     */
    public function inputId()
    {
        if (!$this->inputId) {
            $this->inputId = $this->generateInputId();
        }

        return $this->inputId;
    }

    /**
     * Generate a unique input ID.
     *
     * @return string
     */
    protected function generateInputId()
    {
        return 'input_'.uniqid();
    }

    /**
     * @param string $inputClass The input class attribute.
     * @throws InvalidArgumentException If the class is not a string.
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

    /**
     * @return string
     */
    public function inputClass()
    {
        return $this->inputClass;
    }

    /**
     * Set the input Name.
     *
     * Used for the HTML "name" attribute.
     *
     * @param  string $inputName HTML input id attribute.
     * @return AbstractPropertyInput Chainable
     */
    public function setInputName($inputName)
    {
        $this->inputName = $inputName;

        return $this;
    }

    /**
     * Retrieve the input name.
     *
     * The input name should always be the property's ident.
     *
     * @return string
     */
    public function inputName()
    {
        if ($this->inputName) {
            $name = $this->inputName;
        } else {
            $name = $this->propertyIdent();
        }

        if ($this->p()->l10n()) {
            $name .= '['.$this->lang().']';
        }

        if ($this->multiple()) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * @uses   AbstractProperty::inputVal() Must handle string sanitization of value.
     * @throws UnexpectedValueException If the value is invalid.
     * @return string
     */
    public function inputVal()
    {
        $prop = $this->p();
        $val  = $prop->inputVal($this->propertyVal(), [
            'lang' => $this->lang()
        ]);

        if ($val === null) {
            return '';
        }

        if (!is_scalar($val)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Input value must be a string, received %s',
                    (is_object($val) ? get_class($val) : gettype($val))
                )
            );
        }

        return $val;
    }

    /**
     * Set the hint to the browser for which keyboard to display.
     *
     * @param  string $inputMode The input type.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return AbstractPropertyInput Chainable
     */
    public function setInputMode($inputMode)
    {
        if (!is_string($inputMode)) {
            throw new InvalidArgumentException(
                'Input mode must be a string.'
            );
        }
        $this->inputMode = $inputMode;
        return $this;
    }

    /**
     * Retrieve the hint to the browser for which keyboard to display.
     *
     * @return string
     */
    public function inputMode()
    {
        return $this->inputMode;
    }

    /**
     * @param  string $inputType The input type.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return AbstractPropertyInput Chainable
     * @todo   [mcaskill 2016-11-16]: Rename to `controlType` or `controlTemplate`.
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
     * Set the control type for the HTML element `<input>`.
     *
     * @param  string $type The control type.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return AbstractPropertyInput Chainable
     * @todo   [mcaskill 2016-11-16]: Rename to `inputType`.
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'HTML input type must be a string.'
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        if ($this->type === null) {
            $this->type = self::DEFAULT_INPUT_TYPE;
        }

        return $this->type;
    }

    /**
     * @return string
     */
    public function propertyIdent()
    {
        return $this->p()->ident();
    }

    /**
     * @param PropertyInterface $p The property.
     * @return AbstractPropertyInput Chainable
     */
    public function setProperty(PropertyInterface $p)
    {
        $this->property  = $p;
        $this->inputName = null;

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
