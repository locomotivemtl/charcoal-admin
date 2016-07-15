<?php

namespace Charcoal\Admin\ServiceProvider;

// Dependencies from 'Pimple'
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\GenericFactory as Factory;

// Modeul `charcoal-base` dependencies
use \Charcoal\User\Authenticator;

use \Charcoal\Admin\Config as AdminConfig;

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
        /**
         * @param Container $container Pimple DI Container.
         * @return \Charcoal\Config\ConfigInterface
         */
        $container['admin/config'] = function (Container $container) {
            $appConfig = $container['config'];
            return new AdminConfig($appConfig['admin']);
        };

        $this->registerAuthenticator($container);
        $this->registerUtilities($container);


        // Register Access-Control-List (acl)
        $container->register(new AclServiceProvider());
    }

    /**
     * Registers the authenticator object.
     *
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerAuthenticator(Container $container)
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return \Charcoal\User\Authenticator
         */
        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => 'charcoal/admin/user',
                'user_factory'  => $container['model/factory'],
                'token_type'    => 'charcoal/admin/object/auth-token',
                'token_factory' => $container['model/factory']
            ]);
        };
    }

    /**
     * Registers the admin factories.
     *
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerUtilities(Container $container)
    {
        /**
         * @param Container $container Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/input/factory'] = function (Container $container) {
            return new Factory([
                'base_class' => '\Charcoal\Admin\Property\PropertyInputInterface',
                'arguments' => [[
                    'logger'            => $container['logger'],
                    'metadata_loader'   => $container['metadata/loader']
                ]],
                'resolver_options' => [
                    'suffix' => 'Input'
                ]
            ]);
        };

        /**
         * @param Container $container Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/display/factory'] = function (Container $container) {
            return new Factory([
                'base_class' => '\Charcoal\Admin\Property\PropertyDisplayInterface',
                'arguments' => [[
                    'logger'            => $container['logger'],
                    'metadata_loader'   => $container['metadata/loader']
                ]],
                'resolver_options' => [
                    'suffix' => 'Display'
                ]
            ]);
        };
    }
}
