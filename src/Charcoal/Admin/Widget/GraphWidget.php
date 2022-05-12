<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 * Simple Graph Widget
 */
class GraphWidget extends AdminWidget
{
    /**
     * @var mixed $height
     */
    protected $height = '400px';

    /**
     * @var boolean $showAsCard
     */
    protected $showAsCard = false;

    /**
     * @var array $graphOptions
     */
    protected $graphOptions;

    /**
     * @param  mixed $height The graph height (for CSS).
     * @return self
     */
    public function setHeight($height)
    {
        if (is_numeric($height)) {
            $height .= 'px';
        }

        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param  boolean $show The show as card flag.
     * @return self
     */
    public function setShowAsCard($show)
    {
        $this->showAsCard = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowAsCard()
    {
        return $this->showAsCard;
    }

    /**
     * Set the input options.
     *
     * @param  array $options Optional property input settings.
     * @return self
     */
    public function setGraphOptions(array $options)
    {
        $this->graphOptions = array_merge($this->getDefaultGraphOptions(), $options);

        return $this;
    }

    /**
     * Retrieve the input option value.
     *
     * @param  string $key     The input option key.
     * @param  mixed  $default The fallback input option.
     * @return mixed
     */
    public function getGraphOption($key, $default = null)
    {
        $options = $this->getGraphOptions();

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Retrieve the input options.
     *
     * @return array
     */
    public function getGraphOptions()
    {
        if ($this->graphOptions === null) {
            $this->setGraphOptions([]);
        }

        return $this->graphOptions;
    }

    /**
     * Retrieve the default display options.
     *
     * @return array
     */
    public function getDefaultGraphOptions()
    {
        return [];
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'graph_options' => $this->getGraphOptions(),
        ];
    }
}
