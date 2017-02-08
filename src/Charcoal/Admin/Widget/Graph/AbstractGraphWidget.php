<?php

namespace Charcoal\Admin\Widget\Graph;

// From `charcoal-admin`
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\Graph\GraphWidgetInterface;

/**
 * Base Graph widget
 */
abstract class AbstractGraphWidget extends AdminWidget implements GraphWidgetInterface
{
    /**
     * @var mixed $height
     */
    protected $height = 400;

    /**
     * @var array $colors
     */
    protected $colors;

    /**
     * @param mixed $height The graph height (for CSS).
     * @return GraphWidgetInterface Chainable
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function height()
    {
        return $this->height;
    }

    /**
     * @param string[] $colors The graph colors.
     * @return GraphWidgetInterface Chainable
     */
    public function setColors(array $colors)
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * @return string[]
     */
    public function colors()
    {
        if ($this->colors === null || empty($this->colors)) {
            $this->colors = $this->defaultColors();
        }
        return $this->colors;
    }

    /**
     * @todo Read from widget metadata
     * @return string[]
     */
    public function defaultColors()
    {
        return [
            '#ED5564',
            '#337AB7',
            '#DA70D6',
            '#32CD32',
            '#6495ED',
            '#FF69B4',
            '#BA55D3',
            '#CD5C5C',
            '#FFA500',
            '#40E0D0',
            '#1E90FF',
            '#FF6347',
            '#7B68EE',
            '#00FA9A',
            '#FFD700',
            '#6B8E23',
            '#FF00FF',
            '#3CB371',
            '#B8860B',
            '#30E0E0'
        ];
    }

    /**
     * @return array Categories structure.
     */
    abstract public function categories();

    /**
     * @return string JSONified categories structure.
     */
    public function seriesJson()
    {
        return json_encode($this->series());
    }

    /**
     * @return array Series structure.
     */
    abstract public function series();

    /**
     * @return string JSONified categories structure.
     */
    public function categoriesJson()
    {
        return json_encode($this->categories());
    }
}
