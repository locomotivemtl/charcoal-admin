<?php

namespace Charcoal\Admin;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Config\AbstractConfig;

// From 'charcoal-app'
use Charcoal\App\Handler\HandlerConfig;
use Charcoal\App\Route\RouteConfig;

/**
 * Admin Config.
 */
class Config extends AbstractConfig
{
    const DEFAULT_BASE_PATH = 'admin';

    /**
     * The base path for the admin module's route group.
     *
     * @var string $basePath
     */
    private $basePath = self::DEFAULT_BASE_PATH;

    /**
     * @var array
     */
    public $routes = [];

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @var array
     */
    public $acl = [];

    /**
     * The default data is defined in a JSON file.
     *
     * @return array
     */
    public function defaults()
    {
        $baseDir = rtrim(realpath(__DIR__.'/../../../'), '/').'/';
        $confDir = $baseDir.'config/';

        $file_content = file_get_contents($confDir.'admin.config.default.json');
        $config = json_decode($file_content, true);

        return $config;
    }

    /**
     * Set the admin module's route group.
     *
     * @param  string $path The admin module base path.
     * @throws InvalidArgumentException If the route group is invalid.
     * @return self
     */
    public function setBasePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path must be a string'
            );
        }

        // Can not be empty
        if ($path == '') {
            throw new InvalidArgumentException(
                'Path can not be empty'
            );
        }

        $this->basePath = $path;

        return $this;
    }

    /**
     * Retrieve the admin module's route group.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Parse the admin module's route configuration.
     *
     * @see    \Charcoal\App\AppConfig::setRoutes() For a similar implementation.
     * @param  array $routes The route configuration structure to set.
     * @return self
     */
    public function setRoutes(array $routes)
    {
        $toIterate = RouteConfig::defaultRouteTypes();
        foreach ($routes as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->routes[$key])) {
                $this->routes[$key] = array_merge($this->routes[$key], $val);
            } else {
                $this->routes[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * Define custom response and error handlers.
     *
     * Charcoal overrides four of Slim's standard handlers:
     *
     * - "notFoundHandler"
     * - "notAllowedHandler"
     * - "errorHandler"
     * - "phpErrorHandler"
     *
     * @param  array $handlers The handlers configuration structure to set.
     * @return self
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = array_fill_keys(HandlerConfig::defaultHandlerTypes(), []);
        $this->handlers['defaults'] = [];

        foreach ($handlers as $handler => $data) {
            $this->handlers[$handler] = array_replace(
                $this->handlers[$handler],
                $data
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function handlers()
    {
        return $this->handlers;
    }
}
