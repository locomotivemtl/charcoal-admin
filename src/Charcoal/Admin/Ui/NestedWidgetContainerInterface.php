<?php

namespace Charcoal\Admin\Ui;

// From 'charcoal-app'
use Charcoal\App\Template\WidgetInterface;

/**
 * Widget Container Interface
 *
 * Implementation, as trait, provided by {@see \Charcoal\Admin\Ui\NestedWidgetContainerTrait}.
 */
interface NestedWidgetContainerInterface
{
    /**
     * Retrieve the nested widget.
     *
     * @return WidgetInterface
     */
    public function widget();

    /**
     * Set the nested widget's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The nested widget options.
     * @return self
     */
    public function setWidgetData(array $settings);

    /**
     * Merge (replacing or adding) nested widget options.
     *
     * @param  array $settings The nested widget options.
     * @return self
     */
    public function mergeWidgetData(array $settings);

    /**
     * Add (or replace) an nested widget option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @return self
     */
    public function addWidgetData($key, $val);

    /**
     * Retrieve the nested widget's options or a single option.
     *
     * @param  string|null $key The option key to lookup.
     * @return mixed
     */
    public function widgetData($key);

    /**
     * Retrieve the nested widget's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function widgetDataAsJson();

    /**
     * Retrieve the nested widget's renderable options.
     *
     * @return array
     */
    public function renderableData();

    /**
     * Set the nested widget's renderable options.
     *
     * @param  array $data The data to render.
     * @return self
     */
    public function setRenderableData(array $data);
}
