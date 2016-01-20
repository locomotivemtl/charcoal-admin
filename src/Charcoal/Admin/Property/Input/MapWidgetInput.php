<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 *
 */
class MapWidgetInput extends AbstractPropertyInput
{
    /**
     *
     */
    private $mapOptions = [];

    /**
     * @param array $mapOptions
     */
    public function setMapOptions(array $mapOptions)
    {
        $this->mapOptions = $mapOptions;
    }

    /**
     * Get the map options as JSON-encoded string.
     *
     * @return string
     */
    public function mapOptions()
    {
        return json_encode($this->mapOptions, true);
    }
}
