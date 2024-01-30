<?php

namespace Charcoal\Admin\Property;

use InvalidArgumentException;
use UnexpectedValueException;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractProperty;
use Charcoal\Admin\Property\PropertyDisplayInterface;

/**
 * Base Admin model property display
 */
abstract class AbstractPropertyDisplay extends AbstractProperty implements
    PropertyDisplayInterface
{
    const DEFAULT_DISPLAY_TYPE = 'charcoal/admin/property/display/text';

    /**
     * @var string
     */
    protected $displayType;

    /**
     * @var string $displayId
     */
    protected $displayId;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var string $displayClass
     */
    protected $displayClass;

    /**
     * @var array $displayOptions
     */
    protected $displayOptions;

    /**
     * @var array|null
     */
    protected $displayEscapeOptions;

    /**
     * Set the model property instance.
     *
     * Reset the display name when the property changes.
     *
     * @param  PropertyInterface $property The property.
     * @return self
     */
    public function setProperty(PropertyInterface $property)
    {
        parent::setProperty($property);
        $this->displayName = null;

        return $this;
    }

    /**
     * @param  string $displayType The display type.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
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
            $this->displayType = static::DEFAULT_DISPLAY_TYPE;
        }

        return $this->displayType;
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
            $this->displayId = $this->generateDisplayId();
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
     * Set the display options.
     *
     * @param  array $options Optional property display settings.
     * @return self
     */
    public function setDisplayOptions(array $options)
    {
        $this->displayOptions = array_merge($this->getDefaultDisplayOptions(), $options);

        return $this;
    }

    /**
     * Retrieve the display option value.
     *
     * @param  string $key     The display option key.
     * @param  mixed  $default The fallback display option.
     * @return mixed
     */
    public function getDisplayOption($key, $default = null)
    {
        $options = $this->getDisplayOptions();

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Retrieve the display options.
     *
     * @return array
     */
    public function getDisplayOptions()
    {
        return $this->displayOptions;
    }

    /**
     * Retrieve the default display options.
     *
     * @return array
     */
    public function getDefaultDisplayOptions()
    {
        return [];
    }

    /**
     * Sets the escape callback.
     *
     * Alias of {@see self::setDisplayEscapeOptions()}.
     *
     * @param  mixed $escape The escape options.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return self
     */
    public function setDisplayEscape($escape)
    {
        $this->setDisplayEscapeOptions($escape);

        return $this;
    }

    /**
     * Retrieves the current escape callback.
     *
     * @return callable|null
     */
    public function getDisplayEscape()
    {
        return ($this->getDisplayEscapeOptions()['function'] ?? null);
    }

    /**
     * Sets the escape options.
     *
     * @param  mixed $escape The escape options.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return self
     */
    public function setDisplayEscapeOptions($escape)
    {
        $this->displayEscapeOptions = $this->parseEscapeOptions($escape);

        return $this;
    }

    /**
     * Retrieves the current escape options.
     *
     * @return array|null
     */
    public function getDisplayEscapeOptions()
    {
        return $this->displayEscapeOptions;
    }

    /**
     * Escapes the given value according to display escape options.
     *
     * @param  string $val     The value to escape.
     * @param  array  $options Optional escape options.
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
            $escape = $this->getDisplayEscapeOptions();

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
     * @throws UnexpectedValueException If the value is not a scalar.
     * @return string
     */
    public function displayVal()
    {
        $prop = $this->p();
        $val  = $prop->displayVal($this->propertyVal(), [
            'lang' => $this->lang(),
        ]);

        if ($val === null) {
            return '';
        }

        if (!is_scalar($val)) {
            throw new UnexpectedValueException(sprintf(
                'Property Display Value must be a string, received %s',
                (is_object($val) ? get_class($val) : gettype($val))
            ));
        }

        return $this->escapeVal($val);
    }

    /**
     * @return boolean
     */
    public function hasDisplayVal()
    {
        $val = $this->displayVal();

        return (!empty($val) || is_numeric($val));
    }

    /**
     * Generate a unique display ID.
     *
     * @return string
     */
    protected function generateDisplayId()
    {
        return 'display_'.uniqid();
    }
}
