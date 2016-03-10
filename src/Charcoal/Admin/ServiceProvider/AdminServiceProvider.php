<?php

namespace Charcoal\Admin\ServiceProvider;

// Dependencies from 'Pimple'
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Dependency from `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

// Local Dependencies
use \Charcoal\Admin\AdminModule;
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
        /*$this->registerModule($container);
        $this->registerHandlerServices($container);
        $this->registerRequestControllerServices($container);*/
    }

    /**
     * Registers the module.
     *
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerModule(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return RouteFactory
         */
        $container['charcoal/admin/module'] = function(Container $container) {
            return new AdminModule([
                'logger' => $container['logger'],
                'config' => $container['charcoal/admin/config'],
                'app'    => $this->app()
            ]);
        };

        /**
         * @param Container $container A container instance.
         * @return RouteFactory
         */
        $container['charcoal/admin/config'] = function(Container $container) {
            $config = new AdminConfig();

            if ($container['config']->has('admin')) {
                $config->merge($container['config']->get('admin'));
            }

            return $config;
        };
    }

    /**
     * Registers handlers for the module.
     *
     * @todo   Implement Admin-specific error handlers.
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerHandlerServices(Container $container)
    {
    }

    /**
     * Registers request controllers for the module.
     *
     * @param  Container $container The DI container.
     * @return void
     */
    protected function registerRequestControllerServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return WidgetFactory
         */
        $container['admin/widget/factory'] = function (Container $container) {
            $widgetFactory = new WidgetFactory();

            return $widgetFactory;
        };
    }
}
