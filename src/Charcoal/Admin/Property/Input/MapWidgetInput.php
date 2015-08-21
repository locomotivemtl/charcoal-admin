<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
*
*/
class MapWidgetInput extends AbstractPropertyInput
{

    /**
    * @param array $data
    * @return Text Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        return $this;
    }

    public function test()
    {
        return 'qqchose';
    }

}
