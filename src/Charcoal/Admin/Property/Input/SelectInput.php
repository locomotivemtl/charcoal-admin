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
        foreach($choices as $choice_ident=>$choice) {
            if(!isset($choice['value'])) {
                $choice['value'] = $choice_ident;
            }
            if(!isset($choice['label'])) {
                $choice['label'] = ucwords(strtolower(str_replace('_', ' ', $choice_ident)));
            }
            if(!isset($choice['title'])) {
                $choice['title'] = $choice['label'];
            }
            $choice['selected'] = $this->is_choice_selected($choice_ident);

            yield $choice;
        }
    }

        /**
    * @return boolean
    */
    public function is_choice_selected($c)
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
