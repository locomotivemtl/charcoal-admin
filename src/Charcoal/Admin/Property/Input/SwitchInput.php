<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
*
*/
class SwitchInput extends AbstractPropertyInput
{
    /**
    * @return boolean
    */
    public function checked()
    {
        return !!$this->p()->val();
    }

    /**
    * @return int
    */
    public function value()
    {
        return ( $this->p()->val() ) ? : 0;
    }
}
