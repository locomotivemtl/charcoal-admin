<?php

namespace Charcoal\Admin\Property\Input;

use JsonException;
use RuntimeException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\HtmlProperty;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Static Control Value Property
 *
 * {@todo Note:} This input should be replaced with {@see \Charcoal\Admin\Widget\FormPropertyWidget} modified to support
 * {@see \Charcoal\Admin\Property\AbstractPropertyDisplay} natively.
 */
class ReadonlyInput extends AbstractPropertyInput
{
    const RENDER_TYPE_CONTROL        = 'input';
    const RENDER_TYPE_DISPLAY        = 'display';

    const DEFAULT_MAYBE_INPUT_IS_SERIALIZED = false;
    const DEFAULT_RENDER_TYPE               = self::RENDER_TYPE_CONTROL;
    const DEFAULT_SHOW_AS_CODE_BLOCK        = false;

    /**
     * Whether the input property has a value.
     *
     * @var boolean|null
     */
    protected $hasPropertyVal;

    /**
     * Whether the placeholder text should be shown when the value is empty.
     *
     * @var boolean
     */
    private $showPlaceholder = false;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $propertyDisplayFactory;

    /**
     * @return string
     */
    public function renderVal()
    {
        $type = $this->getRenderType();

        if ($type === self::RENDER_TYPE_DISPLAY) {
            return $this->displayVal();
        }

        return $this->inputVal();
    }

    /**
     * @throws UnexpectedValueException If the value is invalid.
     * @return string
     */
    public function displayVal()
    {
        $property = $this->property();

        if ($this->hasPropertyVal()) {
            $propertyValue = $this->propertyVal();
        } elseif ($this->showPlaceholder()) {
            $propertyValue = $this->placeholder();
            if ($property instanceof HtmlProperty) {
                $propertyValue = html_entity_decode($propertyValue, (ENT_QUOTES | ENT_HTML5));
            }
        } else {
            return '';
        }

        $propertyValue = $this->maybeUnserializeValue($propertyValue);
        $propertyValue = $this->maybeSerializeValue($propertyValue);

        $displayType = $property['displayType'];

        $propertyMetadata = $property->metadata();
        $propertyData     = $property->data();

        if (isset($propertyMetadata['admin'])) {
            $propertyData = array_replace_recursive(
                $propertyMetadata['admin'],
                $propertyData
            );
        }

        $display = $this->getPropertyDisplayFactory()->create($displayType);
        $display->setDisplayType($displayType);
        $display->setProperty($property);
        $display->setData($propertyData);
        $display->setPropertyVal($propertyValue);

        return $this->view()->renderTemplate($displayType, $display);
    }

    /**
     * @throws UnexpectedValueException If the value is invalid.
     * @return string
     */
    public function inputVal()
    {
        $property = $this->property();

        if ($this->hasPropertyVal()) {
            $propertyValue = $this->propertyVal();
        } elseif ($this->showPlaceholder()) {
            $propertyValue = $this->placeholder();
        } else {
            return '';
        }

        $propertyValue = $this->maybeUnserializeValue($propertyValue);
        $propertyValue = $this->maybeSerializeValue($propertyValue);

        $val = $property->inputVal($propertyValue, [
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
    public function hasPropertyVal()
    {
        if ($this->hasPropertyVal === null) {
            $this->hasPropertyVal = parent::hasPropertyVal();
        }

        return $this->hasPropertyVal;
    }

    /**
     * Show/hide the property's placeholder text if the value is empty.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the notes.
     * @return UiItemInterface Chainable
     */
    public function setShowPlaceholder($show)
    {
        $this->showPlaceholder = !!$show;

        return $this;
    }

    /**
     * Determine if the property's placeholder text is displayed if the value is empty.
     *
     * @return boolean
     */
    public function showPlaceholder()
    {
        if ($this->showPlaceholder === false) {
            return false;
        }

        return $this->hasPlaceholder();
    }

    /**
     * Attempt to serialize the given value if property's input is marked as maybe serialized.
     *
     * @param  mixed $input The input to parse.
     * @return mixed
     */
    public function maybeSerializeValue($input)
    {
        if (!is_null($input) && $this->maybeInputIsSerialized()) {
            try {
                if (!is_scalar($input)) {
                    $output = json_encode($input, (JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
                    if (!is_null($output)) {
                        return $output;
                    }
                }
            } catch (JsonException $e) {
                // do nothing
            }
        }

        return $input;
    }

    /**
     * Attempt to unserialize the given value if property's input is marked as maybe serialized.
     *
     * @param  mixed $input The input to parse.
     * @return mixed
     */
    public function maybeUnserializeValue($input)
    {
        if (!is_null($input) && $this->maybeInputIsSerialized()) {
            try {
                $output = json_decode($input, false, 512, JSON_THROW_ON_ERROR);
                if (!is_scalar($output) && !is_null($output)) {
                    return $output;
                }
            } catch (JsonException $e) {
                // do nothing
            }
        }

        return $input;
    }

    /**
     * Determine if the property's input is marked as maybe serialized.
     *
     * @return boolean
     */
    public function maybeInputIsSerialized()
    {
        return $this->getInputOption('maybe_input_is_serialized', static::DEFAULT_MAYBE_INPUT_IS_SERIALIZED);
    }

    /**
     * Determine if the property's layout should be shown as a code block.
     *
     * @return boolean
     */
    public function showAsCodeBlock()
    {
        return $this->getInputOption('show_as_code_block', static::DEFAULT_SHOW_AS_CODE_BLOCK);
    }

    /**
     * Retrieve the property's render type.
     *
     * @return string
     */
    public function getRenderType()
    {
        return $this->getInputOption('render_type', static::DEFAULT_RENDER_TYPE);
    }

    /**
     * Retrieve the default display options.
     *
     * @return array
     */
    public function getDefaultInputOptions()
    {
        return [
            'maybe_input_is_serialized' => static::DEFAULT_MAYBE_INPUT_IS_SERIALIZED,
            'render_type'               => static::DEFAULT_RENDER_TYPE,
            'show_as_code_block'        => static::DEFAULT_SHOW_AS_CODE_BLOCK,
        ];
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setPropertyDisplayFactory($container['property/display/factory']);
    }

    /**
     * Set a property display factory.
     *
     * @param FactoryInterface $factory The property display factory,
     *     to create displayable property values.
     * @return void
     */
    protected function setPropertyDisplayFactory(FactoryInterface $factory)
    {
        $this->propertyDisplayFactory = $factory;
    }

    /**
     * Retrieve the property display factory.
     *
     * @throws RuntimeException If the property display factory was not previously set.
     * @return FactoryInterface
     */
    protected function getPropertyDisplayFactory()
    {
        if (!isset($this->propertyDisplayFactory)) {
            throw new RuntimeException(
                sprintf('Property Display Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->propertyDisplayFactory;
    }
}
