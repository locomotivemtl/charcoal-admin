<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Single-Line Text Input Property
 */
class TextInput extends AbstractPropertyInput
{
    /**
     * @var integer $size
     */
    private $size = 0;

    /**
     * The minimum number of characters allowed.
     *
     * Note:
     * - In Unicode code points.
     * - If zero or a negative value is specified, the length is ignored.
     *
     * @var integer
     */
    private $minLength = 0;

    /**
     * The maximum number of characters allowed.
     *
     * Note:
     * - In UTF-16 code units.
     * - If it is not specified, the control allows an unlimited number of characters.
     * - If zero or a negative value is specified, the length is ignored.
     *
     * @var integer
     */
    private $maxLength = 0;

    /**
     * @var string $pattern
     */
    private $pattern = '';

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'text';
    }

    /**
     * Retrieve the value for the input form control.
     *
     * Note: line-breaks are automatically removed from the input value.
     *
     * @see    AbstractPropertyInput::inputVal()
     * @return string
     */
    public function inputVal()
    {
        return preg_replace('~[\n\r]~', '', parent::inputVal());
    }

    /**
     * @param  integer $minLength The min length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMinLength($minLength)
    {
        if (!is_numeric($minLength)) {
            throw new InvalidArgumentException(
                'Minimum length needs to be an integer'
            );
        }

        $this->minLength = (int)$minLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function minLength()
    {
        return $this->minLength;
    }

    /**
     * @param  integer $maxLength The max length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMaxLength($maxLength)
    {
        if (!is_numeric($maxLength)) {
            throw new InvalidArgumentException(
                'Maximum length needs to be an integer'
            );
        }

        $this->maxLength = (int)$maxLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function maxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param  integer $size The text size.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setSize($size)
    {
        if (!is_numeric($size)) {
            throw new InvalidArgumentException(
                'Size needs to be an integer'
            );
        }
        $this->size = (int)$size;
        return $this;
    }

    /**
     * @return integer
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * @param  string $pattern The pattern.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return Text Chainable
     */
    public function setPattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'Pattern needs to be a string'
            );
        }
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [
            // Text Control
            'size'               => $this->size(),
            'min_length'         => $this->minLength(),
            'max_length'         => $this->maxLength(),

            // Base Control
            'input_name'         => $this->inputName(),
            'input_val'          => $this->inputVal(),

            // Base Property
            'readOnly'           => $this->readOnly(),
            'required'           => $this->required(),
            'l10n'               => $this->property()->l10n(),
            'multiple'           => $this->multiple(),
            'multiple_separator' => $this->property()->multipleSeparator(),
            'multiple_options'   => $this->property()->multipleOptions(),
        ];
    }
}
