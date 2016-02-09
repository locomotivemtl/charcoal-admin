<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractPropertyInput;

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
