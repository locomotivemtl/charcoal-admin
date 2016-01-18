<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
*
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
    * @var string $placeholder
    */
    private $placeholder = '';


    /**
    * @param integer $minLength
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function setMinLength($minLength)
    {
        if (!is_integer($minLength)) {
            throw new InvalidArgumentException('Min length needs to be an integer');
        }
        $this->minLength = $minLength;
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
    * @param integer $maxLength
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function setMaxLength($maxLength)
    {
        if (!is_integer($maxLength)) {
            throw new InvalidArgumentException('Max length needs to be an integer');
        }
        $this->maxLength = $maxLength;
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
    * @param integer $size
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function setSize($size)
    {
        if (!is_integer($size)) {
            throw new InvalidArgumentException('Size needs to be an integer');
        }
        $this->size = $size;
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
    * @param string $pattern
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function setPattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Pattern needs to be a string');
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
    * @param string $placeholder
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function setPlaceholder($placeholder)
    {
        if (!is_string($placeholder)) {
            throw new InvalidArgumentException('Accept needs to be a string');
        }
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
    * @return string
    */
    public function placeholder()
    {
        return $this->placeholder;
    }
}
