<?php

namespace Charcoal\Admin\Property\Input;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Property\AbstractPropertyInput;

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
        return !!$this->propertyVal();
    }

    /**
     * @return integer
     */
    public function value()
    {
        return $this->propertyVal() ? 1 : 0;
    }
}
