<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

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
     * Retrieve the {@see self::propertyVal() value} serialized for an input field.
     *
     * The {@see \Charcoal\Property\StructureProperty::inputVal()} and
     * {@see \Charcoal\Property\AbstractProperty::inputVal()} methods
     * pretty-print the serialized object which works for `<textarea>`
     * elements but does not work well for `<input>` elements.
     *
     * @return string
     */
    public function inputVal()
    {
        $value = $this->propertyVal();
        if (!is_scalar($value)) {
            return json_encode($value);
        }

        return parent::inputVal();
    }

    /**
     * Retrieve the {@see self::propertyVal() value} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function jsonVal()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->propertyVal(), $options);
    }

    /**
     * Retrieve the {@see self::propertyVal() value} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    public function escapedJsonVal()
    {
        return '{{=<% %>=}}'.$this->jsonVal().'<%={{ }}=%>';
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
            $this->mapOptions = array_replace_recursive($this->mapOptions, $settings);
        } else {
            $this->mapOptions = array_replace_recursive($this->defaultMapOptions(), $settings);
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
        return [
            'api_key' => $this->apiKey(),
        ];
    }

    /**
     * Retrieve the map widget's {@see self::mapOptions() options} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function mapOptionsAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->mapOptions(), $options);
    }

    /**
     * Retrieve the map widget's {@see self::mapOptions() options} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    public function escapedMapOptionsAsJson()
    {
        return '{{=<% %>=}}'.$this->mapOptionsAsJson().'<%={{ }}=%>';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        if (isset($container['admin/config']['apis.google.map.key'])) {
            $this->setApiKey($container['admin/config']['apis.google.map.key']);
        } elseif (isset($container['config']['apis.google.map.key'])) {
            $this->setApiKey($container['config']['apis.google.map.key']);
        }

        /**
         * @todo Make this configurable
         */
        if (isset($appConfig['alert.map.map'])) {
            $this->setMapOptions($appConfig['alert.map']);
        }
    }
}
