<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

/**
 *
 */
class SelectInput extends AbstractPropertyInput
{
    // ...

    public function choices()
    {
        $choices = $this->p()->choices();
        foreach ($choices as $choiceIdent => $choice) {
            if (!isset($choice['value'])) {
                $choice['value'] = $choiceIdent;
            }
            if (!isset($choice['label'])) {
                $choice['label'] = ucwords(strtolower(str_replace('_', ' ', $choiceIdent)));
            }
            if (!isset($choice['title'])) {
                $choice['title'] = $choice['label'];
            }
            $choice['selected'] = $this->isChoiceSelected($choiceIdent);

            yield $choice;
        }
    }

        /**
         * @return boolean
         */
    public function isChoiceSelected($c)
    {
        $val = $this->p()->val();
        if ($val === null) {
            return false;
        }
        if ($this->p()->multiple()) {
            return in_array($c, $val);
        } else {
            return $c == $val;
        }
    }
}
