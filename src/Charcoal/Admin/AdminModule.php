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
 * Charcoal Administration Module
 */
class AdminModule extends AbstractModule
{
    /**
     * Charcoal Administration Setup.
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
     * @return AdminModule
     */
    public function setup()
    {
        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }

        $container = $this->app()->getContainer();

        $module = $this;
        $container['charcoal/admin/module'] = function ($c) use ($module) {
            return $module;
        };

        $container['charcoal/admin/config'] = function ($c) {
            $config = new AdminConfig();

            if ($c['config']->has('admin')) {
                $config->merge($c['config']->get('admin'));
            }

            return $config;
        };

        $config = $container['charcoal/admin/config'];

        $this->setConfig($config);

        $groupIdent = '/'.trim($config['base_path'], '/');

        if (isset($config['routes']['default_view'])) {
            $this->app()->get(
                $groupIdent,
                function (RequestInterface $request, ResponseInterface $response) use ($groupIdent, $config) {
                    return $response->withRedirect(
                        $groupIdent.'/'.ltrim($config['routes']['default_view'], '/'),
                        303
                    );
                }
            );
        }
        $this->app()->group($groupIdent, 'charcoal/admin/module:setupRoutes');

        return $this;
    }
}
