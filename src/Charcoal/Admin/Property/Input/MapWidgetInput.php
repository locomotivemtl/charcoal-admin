<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
*
*/
class MapWidgetInput extends AbstractPropertyInput
{
    private $map_options = [];
    /**
    * @param array $data
    * @return Text Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        return $this;
    }

    public function set_map_options($map_options)
    {
        $this->map_options = $map_options;
    }

    public function map_options()
    {
        return json_encode($this->map_options, true);
    }

    public function test()
    {
        return 'qqchose';
    }

}
