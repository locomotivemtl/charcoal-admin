<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\Input as Input;

class Textarea extends Input
{
    /**
    * @var integer $_cols
    */
    private $_cols;
    /**
    * @var integer $_rows
    */
    private $_rows;

    /**
    * @param array $data
    * @return Textarea Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['cols']) && $data['cols'] !== null) {
            $this->set_cols($data['cols']);
        }
        if (isset($data['rows']) && $data['rows'] !== null) {
            $this->set_rows($data['rows']);
        }

        return $this;
    }

    /**
    * @param integer $cols
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_cols($cols)
    {
        if (!is_integer($cols)) {
            throw new InvalidArgumentException('Accept needs to be a string');
        }
        $this->_cols = $cols;
        return $this;
    }

    /**
    * @return integer
    */
    public function cols()
    {
        return $this->_cols;
    }
    
    /**
    * @param integer $rows
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_rows($rows)
    {
        if (!is_integer($rows)) {
            throw new InvalidArgumentException('Accept needs to be a string');
        }
        $this->_rows = $rows;
        return $this;
    }

    /**
    * @return integer
    */
    public function rows()
    {
        return $this->_rows;
    }
}
