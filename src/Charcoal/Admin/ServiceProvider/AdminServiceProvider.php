<?php

namespace Charcoal\Admin\ServiceProvider;

// Dependencies from 'Pimple'
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Local Dependencies

use \Charcoal\Admin\AdminModule;
use \Charcoal\Admin\Config as AdminConfig;
use \Charcoal\Admin\Property\PropertyInputFactory;
use \Charcoal\Admin\Property\PropertyDisplayFactory;

/**
 * Charcoal Administration Service Provider
 *
 * ## Services
 *
 * - Module
 * - Config
 * - Widget Factory
 */
class AdminServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container The DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerUtilities($container);
    }

    /**
     * Registers the admin factories
     *
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerUtilities(Container $container)
    {
        $container['property/input/factory'] = function (Container $container) {
            $propertyInputFactory = new PropertyInputFactory();
            $propertyInputFactory->setArguments([
                'logger'            => $container['logger'],
                'metadata_loader'   => $container['metadata/loader']
            ]);
            return $propertyInputFactory;
        };

        $container['property/display/factory'] = function (Container $container) {
            $propertyInputFactory = new PropertyDisplayFactory();
            $propertyInputFactory->setArguments([
                'logger'            => $container['logger'],
                'metadata_loader'   => $container['metadata/loader']
            ]);
            return $propertyDisplayFactory;
        };
    }
}
