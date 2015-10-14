<?php
namespace Charcoal\Admin\Widget;

// From `PHP`
use \InvalidArgumentException;

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
    public function set_height($height)
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
    * @param array $colors
    * @throws InvalidArgumentException
    * @return Graph Chainable
    */
    public function set_colors($colors)
    {
        if (!is_array($colors)) {
            throw new InvalidArgumentException(
                'Colors must be an array'
            );
        }
        $this->colors = $colors;
        return $this;
    }

    /**
    *
    */
    public function colors()
    {
        if ($this->colors === null || empty($this->colors)) {
            $this->colors = $this->default_colors();
        }
        return $this->colors;
    }

    /**
    * @todo Read from widget metadata
    */
    public function default_colors()
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
