<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractSelectableInput;

/**
 * Select Options Input Property
 *
 * > The HTML _select_ (`<select>`) element represents a control that presents a menu of options.
 * — {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/select}
 */
class SelectInput extends AbstractSelectableInput
{
    /**
     * Retrieve the selectable options.
     *
     * @todo [^1]: With PHP7 we can simply do `yield from $choices;`.
     * @return Generator|array
     */
    public function choices()
    {
        if ($this->p()->allowNull() && !$this->p()->multiple()) {
            $prepend = [
                'value'   => '',
                'label'   => $this->placeholder(),
                'title'   => $this->placeholder(),
                'subtext' => ''
            ];

            yield $prepend;
        }

        $choices = parent::choices();

        /* Pass along the Generator from the parent method [^1] */
        foreach ($choices as $choice) {
            yield $choice;
        }
    }
}
