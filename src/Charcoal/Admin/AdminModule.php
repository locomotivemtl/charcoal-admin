<?php

namespace Charcoal\Admin;

// Dependencies from PSR-7 (HTTP Messaging)
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Dependency from 'charcoal-app'
use Charcoal\App\Handler\HandlerInterface;
use Charcoal\App\Module\AbstractModule;

use Charcoal\Admin\ServiceProvider\AdminServiceProvider;

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
     *
     * - `charcoal/admin/module` An instance of this module
     *   - Exact type: `\Charcoal\Admin\AdminModule`
     *   - which implements `\Charcoal\Module\ModuleInterface`
     *
     * ## Dependencies
     * - `charcoal/config` Provided by \Charcoal\CharcoalModule
     *
     * @return AdminModule Chainable
     */
    public function setup()
    {
        // Hack: skip if the request does not start with '/admin'
        $container = $this->app()->getContainer();
        if (substr(ltrim($container['request']->getUri()->getPath(), '/'), 0, 5) !== 'admin') {
            return $this;
        }

        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }
        $container->register(new AdminServiceProvider());

        $module = $this;
        $container['charcoal/admin/module'] = function ($c) use ($module) {
            return $module;
        };

        $adminConfig = $container['admin/config'];

        $this->setupMetadataForAdmin();

        $this->setConfig($adminConfig);

        $groupIdent = '/'.trim($adminConfig['base_path'], '/');

        // Add the route group
        $this->app()->group($groupIdent, 'charcoal/admin/module:setupRoutes')
                    ->add('charcoal/admin/module:setupHandlers');

        return $this;
    }

    /**
     * Set up the module's routes and handlers.
     *
     * @return AdminModule Chainable
     */
    public function setupRoutes()
    {
        if ($this->routeManager === null) {
            parent::setupRoutes();

            // Serve the Admin's "Not Found" handler for the Admin's route group.
            $this->app()->any('{catchall:.*}', 'notFoundHandler');
        }

        return $this;
    }

    /**
     * Set up the module's handlers, via group middleware.
     *
     * @param  RequestInterface  $request  A PSR7 request object.
     * @param  ResponseInterface $response A PSR7 response object.
     * @param  callable          $next     The next callable middleware.
     * @return ResponseInterface A PSR7 response object.
     */
    public function setupHandlers(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $container = $this->app()->getContainer();

        /**
         * HTTP 404 (Not Found) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->extend('notFoundHandler', function ($handler, $container) {
            $appConfig = $container['config'];
            $adminConfig = $container['admin/config'];
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.notFound'])) {
                    $config->merge($adminConfig['handlers.notFound']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });

        /**
         * HTTP 405 (Not Allowed) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->extend('notAllowedHandler', function ($handler, $container) {
            $appConfig = $container['config'];
            $adminConfig = $container['admin/config'];
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.notAllowed'])) {
                    $config->merge($adminConfig['handlers.notAllowed']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });

        /**
         * HTTP 500 (Error) handler for PHP 7+ Throwables.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->extend('phpErrorHandler', function ($handler, $container) {
            $appConfig = $container['config'];
            $adminConfig = $container['admin/config'];
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.phpError'])) {
                    $config->merge($adminConfig['handlers.phpError']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });

        /**
         * HTTP 500 (Error) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->extend('errorHandler', function ($handler, $container) {
            $appConfig = $container['config'];
            $adminConfig = $container['admin/config'];
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.error'])) {
                    $config->merge($adminConfig['handlers.error']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });

        /**
         * HTTP 503 (Service Unavailable) handler.
         *
         * This handler is not part of Slim.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->extend('maintenanceHandler', function ($handler, $container) {
            $appConfig = $container['config'];
            $adminConfig = $container['admin/config'];
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.maintenance'])) {
                    $config->merge($adminConfig['handlers.maintenance']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });

        return $next($request, $response);
    }

    /**
     * Add "admin/" to each metadata paths.
     * Disable metadata loader cache for admin, for now.
     *
     * @return void
     */
    private function setupMetadataForAdmin()
    {
        $container = $this->app()->getContainer();

        $container['config']->merge([
            'metadata'=>[
                'cache' => $container['cache/drivers']['memory'],
                'paths'=>array_merge($container['config']['metadata.paths'], array_map(function ($p) {
                    return rtrim($p, '/').'/admin/';
                }, $container['config']['metadata.paths']))
            ]
        ]);
    }
}
