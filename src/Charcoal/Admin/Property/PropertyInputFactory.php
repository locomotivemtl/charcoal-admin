<?php

namespace Charcoal\Admin\Property;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * Property Input Factory.
 */
class PropertyInputFactory extends ResolverFactory
{

    /**
     * @return string
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
