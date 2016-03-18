<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

use \Charcoal\Translation\TranslationString;
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Text Input (Base property input).
 */
class TextInput extends AbstractPropertyInput
{
    /**
     * @var integer $size
     */
    private $size = 0;

    /**
     * @var integer $minLength
     */
    private $minLength = 0;
    /**
     * @var integer $maxLength
     */
    private $maxLength = 0;


    /**
     * @var string $pattern
     */
    private $pattern = '';

    /**
     * @var TranslationStringInterface $placeholder
     */
    private $placeholder;


    /**
     * @param integer $minLength The min length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMinLength($minLength)
    {
        if (!is_numeric($minLength)) {
            throw new InvalidArgumentException(
                'Min length needs to be an integer'
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
     * @param integer $maxLength The max length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMaxLength($maxLength)
    {
        if (!is_numeric($maxLength)) {
            throw new InvalidArgumentException(
                'Max length needs to be an integer'
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
     * @param integer $size The text size.
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
     * @param string $pattern The pattern.
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
     * @param mixed $placeholder The placeholder attribute.
     * @return Text Chainable
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = new TranslationString($placeholder);
        return $this;
    }

    /**
     * @todo   [mcaskill: 2016-03-04] Move this to a trait `HasInputPlaceholder`
     * @return string
     */
    public function placeholder()
    {
        return $this->placeholder;
    }
}
