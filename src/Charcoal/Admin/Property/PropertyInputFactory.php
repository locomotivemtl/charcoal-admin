<?php

namespace Charcoal\Admin\Property;

use \Exception as Exception;

use \Charcoal\Admin\Property\Input as Input;

// From `charcoal-core`
use \Charcoal\Core\IdentFactory as IdentFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

/**
*
*/
class PropertyInputFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    public function __construct(array $data = null)
    {
        $this->set_base_class('\Charcoal\Admin\Property\PropertyInputInterface');
    }

    /**
    * IdentFactory > factory_class()
    *
    * @param string
    * @return string
    */
    public function prepare_classname($class)
    {
        return $class.'Input';
    }
}
