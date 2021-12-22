<?php

namespace Charcoal\Admin\Property;

use InvalidArgumentException;
use UnexpectedValueException;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractProperty;
use Charcoal\Admin\Property\PropertyInputInterface;

/**
 * Base Admin model property control
 */
abstract class AbstractPropertyInput extends AbstractProperty implements
    PropertyInputInterface
{
    const DEFAULT_INPUT_TYPE = 'charcoal/admin/property/input/text';

    /**
     * @var string $inputType
     */
    protected $inputType;

    /**
     * @var string $inputId
     */
    protected $inputId;

    /**
     * @var string $inputName
     */
    protected $inputName;

    /**
     * @var string $inputClass
     */
    protected $inputClass;

    /**
     * @var array $inputOptions
     */
    protected $inputOptions;

    /**
     * @var array|null
     */
    protected $inputEscapeOptions;

    /**
     * @var string $inputMode
     */
    protected $inputMode;

    /**
     * The control's prefix.
     *
     * @var Translation|string|null
     */
    protected $inputPrefix;

    /**
     * The control's suffix.
     *
     * @var Translation|string|null
     */
    protected $inputSuffix;

    /**
     * @var Translation|string|null $placeholder
     */
    protected $placeholder;

    /**
     * @var boolean $readOnly
     */
    protected $readOnly;

    /**
     * @var boolean $required
     */
    protected $required;

    /**
     * @var boolean $disabled
     */
    protected $disabled;

    /**
     * Set the model property instance.
     *
     * Reset the input name when the property changes.
     *
     * @param  PropertyInterface $property The property.
     * @return self
     */
    public function setProperty(PropertyInterface $property)
    {
        parent::setProperty($property);
        $this->inputName = null;

        return $this;
    }

    /**
     * @param  string $inputType The input type.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return self
     * @todo   [mcaskill 2016-11-16]: Rename to `controlType` or `controlTemplate`.
     */
    public function setInputType($inputType)
    {
        if (!is_string($inputType)) {
            throw new InvalidArgumentException(
                'Property Input Type must be a string.'
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
            $this->inputType = static::DEFAULT_INPUT_TYPE;
        }

        return $this->inputType;
    }

    /**
     * Set the input ID.
     *
     * Used for the HTML "ID" attribute.
     *
     * @param  string $inputId HTML input id attribute.
     * @return self
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
     * @param string $inputClass The input class attribute.
     * @throws InvalidArgumentException If the class is not a string.
     * @return self
     */
    public function setInputClass($inputClass)
    {
        if (!is_string($inputClass)) {
            throw new InvalidArgumentException('CSS Class(es) must be a string');
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
     * Set the input name.
     *
     * Used for the HTML "name" attribute.
     *
     * @param  string $inputName HTML input id attribute.
     * @return self
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

        if ($this->p()['l10n']) {
            $name .= '['.$this->lang().']';
        }

        if ($this->multiple()) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * Set the input options.
     *
     * @param  array $options Optional property input settings.
     * @return self
     */
    public function setInputOptions(array $options)
    {
        $this->inputOptions = array_merge($this->getDefaultInputOptions(), $options);

        return $this;
    }

    /**
     * Retrieve the input option value.
     *
     * @param  string $key     The input option key.
     * @param  mixed  $default The fallback input option.
     * @return mixed
     */
    public function getInputOption($key, $default = null)
    {
        $options = $this->getInputOptions();

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Retrieve the input options.
     *
     * @return array
     */
    public function getInputOptions()
    {
        if ($this->inputOptions === null) {
            $this->setInputOptions([]);
        }

        return $this->inputOptions;
    }

    /**
     * Retrieve the default display options.
     *
     * @return array
     */
    public function getDefaultInputOptions()
    {
        return [];
    }

    /**
     * Sets the escape callback.
     *
     * Alias of {@see self::setInputEscapeOptions()}.
     *
     * @param  mixed $escape The escape options.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return self
     */
    public function setInputEscape($escape)
    {
        $this->setInputEscapeOptions($escape);

        return $this;
    }

    /**
     * Retrieves the current escape callback.
     *
     * @return callable|null
     */
    public function getInputEscape()
    {
        return $this->getInputEscapeOptions()['function'] ?? null;
    }

    /**
     * Sets the escape options.
     *
     * @param  mixed $escape The escape options.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return self
     */
    public function setInputEscapeOptions($escape)
    {
        $this->inputEscapeOptions = $this->parseEscapeOptions($escape);

        return $this;
    }

    /**
     * Retrieves the current escape options.
     *
     * @return array|null
     */
    public function getInputEscapeOptions()
    {
        return $this->inputEscapeOptions;
    }

    /**
     * Escapes the given value according to input escape options.
     *
     * @param  string $val    The value to escape.
     * @param  array $options Optional escape options.
     * @throws InvalidArgumentException If the value to escape is not a string.
     * @return string
     */
    public function escapeVal($val, array $options = [])
    {
        if (!is_string($val)) {
            throw new InvalidArgumentException(
                'Expected string to escape'
            );
        }

        if (isset($options['function'])) {
            $escape  = $this->parseEscapeOptions($options);
            $options = [];
        } else {
            $escape = $this->getInputEscapeOptions();

            if (!isset($escape['function'])) {
                return $val;
            }
        }

        $callback = $escape['function'];

        if (!isset($escape['parameters'])) {
            return $callback($val);
        }

        $args = $escape['parameters'];

        if (isset($options['parameters']) && is_array($options['parameters'])) {
            $args = array_replace($args, $options['parameters']);
        }

        return $callback($val, ...$args);
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
            'lang' => $this->lang(),
        ]);

        if ($val === null) {
            return '';
        }

        if (!is_scalar($val)) {
            throw new UnexpectedValueException(sprintf(
                'Property Input Value must be a string, received %s',
                (is_object($val) ? get_class($val) : gettype($val))
            ));
        }

        return $this->escapeVal($val);
    }

    /**
     * @return boolean
     */
    public function hasInputVal()
    {
        $val = $this->inputVal();

        return (!empty($val) || is_numeric($val));
    }

    /**
     * Set the hint to the browser for which keyboard to display.
     *
     * @param  string $inputMode The input type.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return self
     */
    public function setInputMode($inputMode)
    {
        if (!is_string($inputMode)) {
            throw new InvalidArgumentException(
                'Property Input Mode must be a string.'
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
     * Determine if the property has an affix.
     *
     * ### Textual `<input>`s only
     *
     * Avoid using `<select>` elements here as they cannot be fully styled in WebKit browsers.
     *
     * Avoid using `<textarea>` elements here as their `rows` attribute will
     * not be respected in some cases.
     *
     * @return boolean
     */
    public function hasInputAffix()
    {
        return ($this->hasInputPrefix() || $this->hasInputSuffix());
    }

    /**
     * Retrieve the control's prefix.
     *
     * @param  mixed $affix Text to display before the control.
     * @return self
     */
    public function setInputPrefix($affix)
    {
        $affix = $this->translator()->translation($affix);

        if ($affix instanceof Translation) {
            $affix->isRendered = false;
        } else {
            $affix = false;
        }

        $this->inputPrefix = $affix;

        return $this;
    }

    /**
     * Determine if the property has a prefix.
     *
     * @return boolean
     */
    public function hasInputPrefix()
    {
        return (bool)$this->inputPrefix();
    }

    /**
     * Retrieve the control's prefix.
     *
     * @return Translation|string|null
     */
    public function inputPrefix()
    {
        if ($this->inputPrefix instanceof Translation) {
            if (isset($this->inputPrefix->isRendered) && $this->inputPrefix->isRendered === false) {
                $this->inputPrefix = $this->renderTranslatableTemplate($this->inputPrefix);
            }

            if ($this->lang()) {
                return $this->inputPrefix[$this->lang()];
            }
        }

        return $this->inputPrefix ?? null;
    }

    /**
     * Retrieve the control's suffix.
     *
     * @param  mixed $affix Text to display after the control.
     * @return self
     */
    public function setInputSuffix($affix)
    {
        $affix = $this->translator()->translation($affix);

        if ($affix instanceof Translation) {
            $affix->isRendered = false;
        } else {
            $affix = false;
        }

        $this->inputSuffix = $affix;

        return $this;
    }

    /**
     * Determine if the property has a suffix.
     *
     * @return boolean
     */
    public function hasInputSuffix()
    {
        return (bool)$this->inputSuffix();
    }

    /**
     * Retrieve the control's suffix.
     *
     * @return Translation|string|null
     */
    public function inputSuffix()
    {
        if ($this->inputSuffix instanceof Translation) {
            if (isset($this->inputSuffix->isRendered) && $this->inputSuffix->isRendered === false) {
                $this->inputSuffix = $this->renderTranslatableTemplate($this->inputSuffix);
            }

            if ($this->lang()) {
                return $this->inputSuffix[$this->lang()];
            }
        }

        return $this->inputSuffix ?? null;
    }

    /**
     * @return boolean
     */
    public function hidden()
    {
        if ($this->p()['l10n']) {
            if ($this->lang() != $this->translator()->getLocale()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param boolean $readOnly The read-only flag.
     * @return self
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
     * @return self
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
     * @return self
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
     * Set the form control's placeholder.
     *
     * A placeholder is a hint to the user of what can be entered
     * in the property control.
     *
     * @param  mixed $placeholder The placeholder attribute.
     * @return self
     */
    public function setPlaceholder($placeholder)
    {
        $placeholder = $this->translator()->translation($placeholder);

        if ($placeholder instanceof Translation) {
            $placeholder->isRendered = false;
        } else {
            $placeholder = false;
        }

        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPlaceholder()
    {
        return (bool)$this->placeholder();
    }

    /**
     * Retrieve the placeholder.
     *
     * @return Translation|string|null
     */
    public function placeholder()
    {
        if ($this->placeholder === null) {
            $metadata = $this->metadata();

            if (isset($metadata['data']['placeholder'])) {
                $this->setPlaceholder($metadata['data']['placeholder']);
            }
        }

        if ($this->placeholder instanceof Translation) {
            if (isset($this->placeholder->isRendered) && $this->placeholder->isRendered === false) {
                $this->placeholder = $this->renderTranslatableTemplate($this->placeholder);
            }

            if ($this->lang()) {
                return $this->placeholder[$this->lang()];
            }
        }

        return $this->placeholder ?? null;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [];
    }

    /**
     * Retrieve the control's {@see self::controlDataForJs() options} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    final public function controlDataForJsAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->controlDataForJs(), $options);
    }

    /**
     * Retrieve the control's {@see self::controlDataForJs() options} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    public function escapedControlDataForJsAsJson()
    {
        return '{{=<% %>=}}'.$this->controlDataForJsAsJson().'<%={{ }}=%>';
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
}
