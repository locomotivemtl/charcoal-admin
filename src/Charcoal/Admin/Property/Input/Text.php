<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\Input as Input;

/**
* Text Property Input Type
*/
class Text extends Input
{
    /**
    * @var integer $_size
    */
    private $_size = 0;

    /**
    * @var integer $min_length
    */
    private $_min_length = 0;
    /**
    * @var integer $_max_length
    */
    private $_max_length = 0;
    

    /**
    * @var string $_pattern
    */
    private $_pattern = '';

    /**
    * @var string $_placeholder
    */
    private $_placeholder = '';

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        parent::set_data($data);
        if (isset($data['size']) && $data['size'] !== null) {
            $this->set_size($data['size']);
        }
        if (isset($data['min_length']) && $data['min_length'] !== null) {
            $this->set_min_length($data['min_length']);
        }
        if (isset($data['max_length']) && $data['max_length'] !== null) {
            $this->set_max_length($data['max_length']);
        }
        if (isset($data['pattern']) && $data['pattern'] !== null) {
            $this->set_pattern($data['pattern']);
        }
        if (isset($data['placeholder']) && $data['placeholder'] !== null) {
            $this->set_placeholder($data['placeholder']);
        }

        return $this;
    }

    /**
    * @param integer $min_length
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_min_length($min_length)
    {
        if (!is_integer($min_length)) {
            throw new InvalidArgumentException('Min length needs to be an integer');
        }
        $this->_min_length = $min_length;
        return $this;
    }

    /**
    * @return integer
    */
    public function min_length()
    {
        return $this->_min_length;
    }

    /**
    * @param integer $max_length
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_max_length($max_length)
    {
        if (!is_integer($max_length)) {
            throw new InvalidArgumentException('Max length needs to be an integer');
        }
        $this->_max_length = $max_length;
        return $this;
    }

    /**
    * @return integer
    */
    public function max_length()
    {
        return $this->_max_length;
    }

    /**
    * @param integer $size
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_size($size)
    {
        if (!is_integer($size)) {
            throw new InvalidArgumentException('Size needs to be an integer');
        }
        $this->_size = $size;
        return $this;
    }

    /**
    * @return integer
    */
    public function size()
    {
        return $this->_size;
    }

        /**
    * @param string $pattern
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_pattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Pattern needs to be a string');
        }
        $this->_pattern = $pattern;
        return $this;
    }

    /**
    * @return string
    */
    public function pattern()
    {
        return $this->_pattern;
    }

    /**
    * @param string $placeholder
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_placeholder($placeholder)
    {
        if (!is_string($placeholder)) {
            throw new InvalidArgumentException('Accept needs to be a string');
        }
        $this->_placeholder = $placeholder;
        return $this;
    }

    /**
    * @return string
    */
    public function placeholder()
    {
        return $this->_placeholder;
    }
}
