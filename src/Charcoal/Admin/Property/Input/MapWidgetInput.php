<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-admin'
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 *
 */
class MapWidgetInput extends AbstractPropertyInput
{
    /**
     * The API key for the mapping service.
     *
     * @var array
     */
    private $apiKey;

    /**
     * Settings for the map widget.
     *
     * @var array
     */
    private $mapOptions;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $appConfig = $container['config'];

        if (isset($appConfig['google.console.api_key'])) {
            $this->setApiKey($appConfig['google.console.api_key']);
        } elseif (isset($appConfig['apis.google.map.key'])) {
            $this->setApiKey($appConfig['apis.google.map.key']);
        }

        if (isset($appConfig['alert.map.map'])) {
            $this->setMapOptions($appConfig['alert.map.map']);
        }
    }

    /**
     * Sets the API key for the mapping service.
     *
     * @param  string $key An API key.
     * @return self
     */
    public function setApiKey($key)
    {
        $this->apiKey = $key;

        return $this;
    }

    /**
     * Retrieve API key for the mapping service.
     *
     * @return string
     */
    public function apiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set the map widget's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The map widget options.
     * @return MapWidgetInput Chainable
     */
    public function setMapOptions(array $settings)
    {
        if (isset($settings['api_key'])) {
            $this->setApiKey($settings['api_key']);
        }

        if ($this->mapOptions) {
            $this->mapOptions = array_merge($this->mapOptions, $settings);
        } else {
            $this->mapOptions = array_merge($this->defaultMapOptions(), $settings);
        }
        return $this;
    }

    /**
     * Merge (replacing or adding) map widget options.
     *
     * @param  array $settings The map widget options.
     * @return MapWidgetInput Chainable
     */
    public function mergeMapOptions(array $settings)
    {
        if (isset($settings['api_key'])) {
            $this->setApiKey($settings['api_key']);
        }

        $this->mapOptions = array_merge($this->mapOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an map widget option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return MapWidgetInput Chainable
     */
    public function addMapOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->mapOptions === null) {
            $this->mapOptions();
        }

        $this->mapOptions[$key] = $val;

        if ($key === 'api_key') {
            $this->setApiKey($val);
        }

        return $this;
    }

    /**
     * Retrieve the map widget's options.
     *
     * @return array
     */
    public function mapOptions()
    {
        if ($this->mapOptions === null) {
            $this->mapOptions = $this->defaultMapOptions();
        }
        return $this->mapOptions;
    }

    /**
     * Retrieve the default map widget options.
     *
     * @return array
     */
    public function defaultMapOptions()
    {
        return [ 'api_key' => $this->apiKey() ];
    }

    /**
     * Retrieve the map widget's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function mapOptionsAsJson()
    {
        return json_encode($this->mapOptions());
    }
}
