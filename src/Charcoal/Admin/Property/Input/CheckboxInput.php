<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
*
*/
class CheckboxInput extends AbstractPropertyInput
{
    /**
    * @return boolean
    */
    public function checked()
    {
        return true;
    }
}
