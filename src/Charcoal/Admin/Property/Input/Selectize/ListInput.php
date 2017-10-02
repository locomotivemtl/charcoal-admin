<?php

namespace Charcoal\Admin\Property\Input\Selectize;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\SelectizeInput;

/**
 * Listable Input Selectize
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
