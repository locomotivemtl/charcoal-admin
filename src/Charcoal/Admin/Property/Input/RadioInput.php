<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
 *
 */
class RadioInput extends AbstractPropertyInput
{
    /**
     * @return array
     */
    public function choices()
    {
        return $this->p()->choices();
    }
}
