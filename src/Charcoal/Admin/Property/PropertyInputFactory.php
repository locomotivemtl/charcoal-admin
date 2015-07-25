<?php

namespace Charcoal\Admin\Property;

use \Exception as Exception;

use \Charcoal\Admin\Property\Input as Input;

// From `charcoal-core`
use \Charcoal\Core\AbstractFactory as AbstractFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

/**
*
*/
class PropertyInputFactory extends AbstractFactory
{
    /**
    * @param array $data
    */
    public function __construct(array $data = null)
    {
        $this->set_factory_mode(AbstractFactory::MODE_IDENT);
        $this->set_base_class('\Charcoal\Admin\Property\PropertyInputInterface');

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * AbstractFactory > factory_class()
    *
    * @param string
    * @return string
    */
    public function factory_class($class)
    {
        return $class.'Input';
    }
}
