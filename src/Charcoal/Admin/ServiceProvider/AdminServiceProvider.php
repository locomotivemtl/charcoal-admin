<?php

namespace Charcoal\Admin\ServiceProvider;

// Dependencies from 'Pimple'
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// From 'charcoal-config'
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\GenericConfig as Config;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\GenericFactory as Factory;

// Module `charcoal-base` dependencies
use \Charcoal\User\Authenticator;
use \Charcoal\User\Authorizer;

use \Charcoal\Admin\Config as AdminConfig;
use \Charcoal\Admin\Property\PropertyInputInterface;
use \Charcoal\Admin\Property\PropertyDisplayInterface;
use \Charcoal\Admin\Ui\Sidemenu\SidemenuGroupInterface;
use \Charcoal\Admin\Ui\Sidemenu\GenericSidemenuGroup;
use \Charcoal\Admin\User;
use \Charcoal\Admin\User\AuthToken;


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
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param  Container $container The Pimple DI Container.
         * @return ConfigInterface
         */
        $container['admin/config'] = function (Container $container) {
            $appConfig = $container['config'];
            return new AdminConfig($appConfig['admin']);
        };

        $container->extend('admin/config', function (ConfigInterface $adminConfig, Container $container) {
            $adminConfig['elfinder'] = new Config($adminConfig['elfinder']);

            return $adminConfig;
        });

        /**
         * @param  Container $container The Pimple DI Container.
         * @return ConfigInterface
         */
        $container['elfinder/config'] = function (Container $container) {
            return $container['admin/config']['elfinder'];
        };

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);
        $this->registerUtilities($container);


        // Register Access-Control-List (acl)
        $container->register(new AclServiceProvider());
    }

    /**
     * Registers the authenticator object in `admin/authenticator`.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerAuthenticator(Container $container)
    {
        /**
         * @param Container $container The Pimple DI Container.
         * @return Authenticator
         */
        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => User::class,
                'user_factory'  => $container['model/factory'],
                'token_type'    => AuthToken::class,
                'token_factory' => $container['model/factory']
            ]);
        };
    }

    /**
     * Registers the authorizer object in `admin/authorizer`.
     *
     * @param Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerAuthorizer(Container $container)
    {
        /**
         * @param Container $container The Pimple DI container.
         * @return Authorizer
         */
        $container['admin/authorizer'] = function (Container $container) {
            return new Authorizer([
                'logger'    => $container['logger'],
                'acl'       => $container['admin/acl'],
                'resource'  => 'admin'
            ]);
        };
    }

    /**
     * Registers the admin factories.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerUtilities(Container $container)
    {
        /**
         * @param Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/input/factory'] = function (Container $container) {
            return new Factory([
                'base_class' => PropertyInputInterface::class,
                'arguments' => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Input'
                ]
            ]);
        };

        /**
         * @param Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/display/factory'] = function (Container $container) {
            return new Factory([
                'base_class' => PropertyDisplayInterface::class,
                'arguments' => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Display'
                ]
            ]);
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['sidemenu/group/factory'] = function(Container $container) {
            return new Factory([
                'base_class'    => SidemenuGroupInterface::class,
                'default_class' => GenericSidemenuGroup::class,
                'arguments'     => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'view'           => $container['view'],
                    'layout_builder' => $container['layout/builder']
                ]],
                'resolver_options' => [
                    'suffix' => 'SidemenuGroup'
                ]
            ]);
        };
    }
}