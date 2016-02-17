<?php
namespace Charcoal\Admin\Widget;

// From `charcoal-admin`
use \Charcoal\Admin\AdminWidget;

/**
 * Base Graph widget
 */
class GraphWidget extends AdminWidget
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
     * @param mixed $height
     * @return Graph Chainable
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
     * @param string[] $colors
     * @return Graph Chainable
     */
    public function setColors(array $colors)
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     *
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
     */
    public function defaultColors()
    {
        return [
            '#f0ad4e',
            '#337ab7',
            '#da70d6',
            '#32cd32',
            '#6495ed',
            '#ff69b4',
            '#ba55d3',
            '#cd5c5c',
            '#ffa500',
            '#40e0d0',
            '#1e90ff',
            '#ff6347',
            '#7b68ee',
            '#00fa9a',
            '#ffd700',
            '#6b8e23',
            '#ff00ff',
            '#3cb371',
            '#b8860b',
            '#30e0e0'
        ];
    }
}
