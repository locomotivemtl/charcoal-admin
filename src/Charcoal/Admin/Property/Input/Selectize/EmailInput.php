<?php

namespace Charcoal\Admin\Property\Input\Selectize;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\SelectizeInput;

/**
 * Email Input Selectize
 */
class EmailInput extends SelectizeInput
{
    /**
     * @return string
     */
    public function inputClass()
    {
        $parentClass = parent::inputClass();

        $class = $parentClass.' selectize-email';

        return $class;
    }

    /**
     * Retrieve the default object-to-choice data map.
     *
     * @return array
     */
    public function defaultChoiceObjMap()
    {
        $choiceObjMap = parent::defaultChoiceObjMap();

        $choiceObjMap['email'] = 'email';

        return $choiceObjMap;
    }
}
