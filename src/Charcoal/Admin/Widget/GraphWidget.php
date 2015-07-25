<?php
namespace Charcoal\Admin\Widget;

// From `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-admin`
use \Charcoal\Admin\AdminWidget as AdminWidget;

/**
*
*/
class GraphWidget extends AdminWidget
{
    /**
    * @var mixed $_height
    */
    protected $_height = 400;
    /**
    * @var array $_colors
    */
    protected $_colors;

    /**
    * @param array $data
    * @return Graph Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['height']) && $data['height'] !== null) {
            $this->set_height($data['height']);
        }
        if (isset($data['colors']) && $data['colors'] !== null) {
            $this->set_colors($data['colors']);
        }
        return $this;
    }

    /**
    * @param mixed $height
    * @return Graph Chainable
    */
    public function set_height($height)
    {
        $this->_height = $height;
        return $this;
    }

    /**
    * @return mixed
    */
    public function height()
    {
        return $this->_height;
    }

    /**
    * @param array $colors
    * @throws InvalidArgumentException
    * @return Graph Chainable
    */
    public function set_colors($colors)
    {
        if (!is_array($colors)) {
            throw new InvalidArgumentException('Colors must be an array');
        }
        $this->_colors = $colors;
        return $this;
    }

    /**
    *
    */
    public function colors()
    {
        if ($this->_colors === null || empty($this->_colors)) {
            $this->_colors = $this->default_colors();
        }
        return $this->_colors;
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
