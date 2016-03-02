<?php

namespace Charcoal\Admin;

use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig;

/**
 * Admin Config.
 */
class Config extends AbstractConfig
{
    const DEFAULT_BASE_PATH = 'admin';

    /**
     * @var string $_basePath
     */
    private $basePath = self::DEFAULT_BASE_PATH;

    /**
     * The default data is defined in a JSON file.
     *
     * @return array
     */
    public function defaults()
    {
        $file_content = file_get_contents(realpath(__DIR__.'/../../../config').'/admin.config.default.json');
        $config = json_decode($file_content, true);
        return $config;
    }

    /**
     * @param string $path The admin module base path.
     * @throws InvalidArgumentException
     * @return Config Chainable
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
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $path The admin module base path.
     * @throws InvalidArgumentException
     * @return Config Chainable
     */
    public function setRoutes($routes)
    {
        if (!isset($this->routes)) {
            $this->routes = [];
        }

        $toIterate = [ 'templates', 'actions', 'scripts' ];
        foreach ($routes as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->routes[$key])) {
                $this->routes[$key] = array_merge($this->routes[$key], $val);
            } else {
                $this->routes[$key] = $val;
            }
        }

        return $this;
    }
}
