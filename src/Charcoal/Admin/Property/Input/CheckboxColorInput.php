<?php

namespace Charcoal\Admin\Property\Input;

/**
 * Checkbox Color Input Property
 */
class CheckboxColorInput extends CheckboxInput
{
    /**
     * Prepare a single tickable option for output.
     *
     * @see    RadioColorInput::parseChocie() For similar features.
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
