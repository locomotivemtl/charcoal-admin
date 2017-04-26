<?php

namespace Charcoal\Admin\Property\Input\Selectize;

use Charcoal\Admin\Property\Input\SelectizeInput;

/**
 * Class ListInput
 */
class ListInput extends SelectizeInput
{
    /**
     * @return string
     */
    public function inputClass()
    {
        $parentClass = parent::inputClass();

        $class = $parentClass.' selectize-list';

        return $class;
    }
}
