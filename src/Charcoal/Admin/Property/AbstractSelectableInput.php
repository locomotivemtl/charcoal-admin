<?php

namespace Charcoal\Admin\Property;

/**
 * Selectable input properties provide an array of choices to choose from.
 */
abstract class AbstractSelectableInput extends AbstractPropertyInput implements
    SelectableInputInterface
{
    /**
     * Retrieve the selectable options.
     *
     * @return Generator|array
     */
    public function choices()
    {
        $choices = $this->p()->choices();

        foreach ($choices as $ident => $choice) {
            $choice = $this->parseChoice($ident, $choice);

            if (!is_array($choice)) {
                continue;
            }

            yield $ident => $choice;
        }
    }

    /**
     * Prepare a single selectable option for output.
     *
     * @param  string|integer $ident  The choice key.
     * @param  array|object   $choice The choice structure.
     * @return array|null
     */
    protected function parseChoice($ident, $choice)
    {
        if (!isset($choice['value'])) {
            $choice['value'] = $ident;
        }

        if (!isset($choice['label'])) {
            $choice['label'] = ucwords(strtolower(str_replace('_', ' ', $ident)));
        }

        if (!isset($choice['title'])) {
            $choice['title'] = $choice['label'];
        }

        $choice['checked']  = $this->isChoiceSelected($ident);
        $choice['selected'] = $choice['checked'];

        return $choice;
    }

    /**
     * Determine if the provided option is a selected value.
     *
     * @param  mixed $choice The choice to check.
     * @return boolean
     */
    public function isChoiceSelected($choice)
    {
        $val = $this->p()->val();

        if ($val === null) {
            return false;
        }

        $val = $this->p()->parseVal($val);

        if (isset($choice['value'])) {
            $choice = $choice['value'];
        }

        if ($this->p()->multiple()) {
            return in_array($choice, $val);
        } else {
            return $choice == $val;
        }
    }
}
