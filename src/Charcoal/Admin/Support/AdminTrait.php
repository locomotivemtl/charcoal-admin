<?php

namespace Charcoal\Admin\Support;

// From 'charcoal-app'
use Charcoal\App\AppConfig;

// From 'charcoal-admin'
use Charcoal\Admin\Config as AdminConfig;

/**
 * Admin Support Trait
 */
trait AdminTrait
{
    /**
     * Store a reference to the admin configuration.
     *
     * @var AdminConfig
     */
    protected $adminConfig;

    /**
     * Store a reference to the application configuration.
     *
     * @var AppConfig
     */
    protected $appConfig;

    /**
     * Whether the debug mode is enabled.
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * Set application debug mode.
     *
     * @param  boolean $debug The debug flag.
     * @return void
     */
    protected function setDebug($debug)
    {
        $this->debug = !!$debug;
    }

    /**
     * Retrieve the application debug mode.
     *
     * @return boolean
     */
    public function debug()
    {
        return $this->debug;
    }

    /**
     * Set the admin's configset.
     *
     * @param  AdminConfig $config A configset.
     * @return void
     */
    protected function setAdminConfig(AdminConfig $config)
    {
        $this->adminConfig = $config;
    }

    /**
     * Retrieve the admin's configset.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed|AdminConfig
     */
    protected function adminConfig($key = null, $default = null)
    {
        if ($key) {
            if (isset($this->adminConfig[$key])) {
                return $this->adminConfig[$key];
            } else {
                if (!is_string($default) && is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            }
        }

        return $this->adminConfig;
    }

    /**
     * Set the application's configset.
     *
     * @param  AppConfig $config A configset.
     * @return void
     */
    protected function setAppConfig(AppConfig $config)
    {
        $this->appConfig = $config;
    }

    /**
     * Retrieve the application's configset.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed|AppConfig
     */
    protected function appConfig($key = null, $default = null)
    {
        if ($key) {
            if (isset($this->appConfig[$key])) {
                return $this->appConfig[$key];
            } else {
                if (!is_string($default) && is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            }
        }

        return $this->appConfig;
    }

    /**
     * Retrieve a value from the API configset.
     *
     * Looks up the admin module first, the application second.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed
     */
    protected function apiConfig($key, $default = null)
    {
        $key = 'apis.'.$key;

        if (isset($this->adminConfig[$key])) {
            return $this->adminConfig[$key];
        } elseif (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        } elseif (!is_string($default) && is_callable($default)) {
            return $default();
        } else {
            return $default;
        }
    }
}
