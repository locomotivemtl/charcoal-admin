<?php

namespace Charcoal\Admin\Property\Input;

/**
 * Radio Color Input Property
 */
class RadioColorInput extends RadioInput
{
    /**
     * Prepare a single tickable option for output.
     *
     * @see    CheckboxColorInput::parseChocie() For similar features.
     * @param  string|integer $ident  The choice key.
     * @param  array|object   $choice The choice structure.
     * @return array|null
     */
    protected function parseChoice($ident, $choice)
    {
        $choice = parent::parseChoice($ident, $choice);

        if (!isset($choice['color'])) {
            $choice['color'] = $choice['value'];
        }

        if (!isset($choice['show_label'])) {
            $choice['show_label'] = false;
        }

        return $choice;
    }
}
