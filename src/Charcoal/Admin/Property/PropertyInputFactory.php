<?php

namespace Charcoal\Admin\Property;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
*
*/
class PropertyInputFactory extends ResolverFactory
{

    /**
    * @param array $data
    */
    public function baseClass()
    {
        return '\Charcoal\Admin\Property\PropertyInputInterface';
    }

    /**
    * @return string
    */
    public function resolverSuffix()
    {
        return 'Input';
    }
}
