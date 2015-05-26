<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\Input as Input;

class Checkbox extends Input
{
    public function checked()
    {
        return true;
    }
}
