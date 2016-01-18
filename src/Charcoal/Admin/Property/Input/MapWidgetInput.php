<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
*
*/
class MapWidgetInput extends AbstractPropertyInput
{
    private $mapOptions = [];


    public function setMapOptions($mapOptions)
    {
        $this->mapOptions = $mapOptions;
    }

    public function mapOptions()
    {
        return json_encode($this->mapOptions, true);
    }

    public function test()
    {
        return 'qqchose';
    }

}
