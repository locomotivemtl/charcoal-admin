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

        return $val;
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
