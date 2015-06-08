<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\Input as Input;

/**
* Number Property Input Type
*/
class Number extends Input
{
     /**
    * @var integer $min
    */
    private $_min = 0;
    /**
    * @var integer $_max
    */
    private $_max = 0;

    /**
    * @var float $_step
    */
    private $_step = 0;

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
        if (isset($data['min']) && $data['min'] !== null) {
            $this->set_min($data['min']);
        }
        if (isset($data['max']) && $data['max'] !== null) {
            $this->set_max($data['max']);
        }
        if (isset($data['step']) && $data['step'] !== null) {
            $this->set_step($data['step']);
        }
        

        return $this;
    }

       /**
    * @param integer $min
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_min($min)
    {
        if (!is_integer($min)) {
            throw new InvalidArgumentException('Min length needs to be an integer');
        }
        $this->_min = $min;
        return $this;
    }

    /**
    * @return integer
    */
    public function min()
    {
        return $this->_min;
    }

    /**
    * @param integer $max
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_max($max)
    {
        if (!is_integer($max)) {
            throw new InvalidArgumentException('Max length needs to be an integer');
        }
        $this->_max = $max;
        return $this;
    }

    /**
    * @return integer
    */
    public function max()
    {
        return $this->_max;
    }

    /**
    * @param integer $step
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_step($step)
    {
        if (!is_float($step)) {
            throw new InvalidArgumentException('Size needs to be an integer');
        }
        $this->_step = $step;
        return $this;
    }

    /**
    * @return integer
    */
    public function step()
    {
        return $this->_step;
    }
}
