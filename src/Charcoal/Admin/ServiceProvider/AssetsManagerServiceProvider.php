<?php

namespace Charcoal\Admin\ServiceProvider;

// from pimple
use Charcoal\Admin\AssetsConfig;
use Charcoal\Admin\Mustache\AssetsHelpers;
use Charcoal\Admin\Service\AssetsBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Assetic\AssetManager;

/**
 * Class AssetsManagerServiceProvider
 */
class AssetsManagerServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerAssetsManager($container);
        $this->registerMustacheHelpersServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container)
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = function () {
                return [];
            };
        }

        /**
         * Translation helpers for Mustache.
         *
         * @param Container $container Pimple DI container.
         * @return AssetsHelpers
         */
        $container['view/mustache/helpers/assets-manager'] = function (Container $container) {
            return new AssetsHelpers([
                'assets' => $container['assets']
            ]);
        };

        /**
         * Extend global helpers for the Mustache Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container) {
            return array_merge(
                $helpers,
                $container['view/mustache/helpers/assets-manager']->toArray()
            );
        });
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerAssetsManager(Container $container)
    {
        $container['assets/config'] = function (Container $container) {
            $config = $container['view/config']->get('assets');

            return new AssetsConfig($config);
        };

        $container['assets/builder'] = function () {
            return new AssetsBuilder();
        };

        /**
         * @param Container $container Pimple DI container.
         * @return AssetManager
         */
        $container['assets'] = function (Container $container) {
            $assetsBuilder = $container['assets/builder'];
            $assetsConfig = $container['assets/config'];

            return $assetsBuilder($assetsConfig);
        };
    }
}
