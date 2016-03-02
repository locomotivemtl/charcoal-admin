<?php

namespace Charcoal\Admin;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from 'charcoal-app'
use \Charcoal\App\Module\AbstractModule;

// Intra-module ('charcoal-admin') dependencies
use \Charcoal\Admin\Config as AdminConfig;

/**
 * The base class for the `admin` Module
 */
class AdminModule extends AbstractModule
{
    /**
     * Charcoal admin setup.
     *
     * This module is bound to the `/admin` URL.
     *
     * ## Provides
     * - `charcoal/admin/module` An instance of this module
     *   - Exact type: `\Charcoal\Admin\AdminModule`
     *   - which implements `\Charcoal\Module\ModuleInterface`
     * - `charcoal/admin/config`
     * - `
     *
     * ## Dependencies
     * - `charcoal/config` Provided by \Charcoal\CharcoalModule
     *
     * @param \Slim\App $app
     * @return void
     */
    public function setup()
    {
        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }

        $container = $this->app()->getContainer();
        $container['charcoal/admin/module'] = function($c) {
            return new AdminModule([
                'logger' => $c['logger'],
                'config' => $c['charcoal/admin/config'],
                'app'    => $this->app()
            ]);
        };

        $container['charcoal/admin/config'] = function($c) {
            $config = new AdminConfig();

            if ($c['config']->has('admin')) {
                $config->merge($c['config']->get('admin'));
            }

            return $config;
        };

        $adminConfig = $container['charcoal/admin/config'];
        $adminPath   = '/'.trim($adminConfig->basePath(), '/');

        if (isset($adminConfig['routes']['default_view'])) {
            $this->app()->get(
                $adminPath,
                function (RequestInterface $request, ResponseInterface $response) use ($adminPath, $adminConfig) {
                    return $response->withRedirect($adminPath . '/' . ltrim($adminConfig['routes']['default_view'], '/'), 303);
                }
            );
        }
        $this->app()->group($adminPath, 'charcoal/admin/module:setupRoutes');
    }

    public function createConfig(array $data = null)
    {
        $container = $this->app()->getContainer();
        $appConfig = $container->get('config');
        $config    = new AdminConfig($data);

        if ($appConfig->has('admin')) {
            $config->merge($appConfig->get('admin'));
        }

        return $config;
    }
}
