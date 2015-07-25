<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-base`
use \Charcoal\Widget\AbstractWidget as AbstractWidget;

// From `charcoal-base`
use \Charcoal\Widget\WidgetView as WidgetView;

/**
*
*/
class AdminWidget extends AbstractWidget
{
    public $_widget_id;

    /**
    * @var string $_type
    */
    private $_type;
    /**
    * @var string $_ident
    */
    private $_ident = '';
    /**
    * @var mixed $_label
    */
    private $_label;
    /**
    * @var string $_lang
    */
    private $_lang;
    /**
    * @var bool $_show_label
    */
    private $_show_label;
    /**
    * @var bool $_show_actions
    */
    private $_show_actions;

    /**
    * @var array $data
    * @return Widget Chainable
    */
    public function set_data(array $data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        //var_dump($data);
        if (isset($data['widget_id']) && $data['widget_id'] !== null) {
            $this->set_widget_id($data['widget_id']);
        }
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['label']) && $data['label'] !== null) {
            $this->set_label($data['label']);
        }
        if (isset($data['show_label']) && $data['show_label'] !== null) {
            $this->set_show_label($data['show_label']);
        }
        if (isset($data['show_actions']) && $data['show_actions'] !== null) {
            $this->set_show_actions($data['show_actions']);
        }

        return $this;
    }

    public function set_widget_id($widget_id)
    {
        $this->_widget_id = $widget_id;
        return $this;
    }

    public function widget_id()
    {
        if (!$this->_widget_id) {
            $this->_widget_id = 'widget_'.uniqid();
        }
        return $this->_widget_id;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return Widget Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Template ident must be a string');
        }
        $this->_type = $type;
        return $this;
    }

    /**
    * @return string
    */
    public function type()
    {
        return $this->_type;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException if the ident is not a string
    * @return Widget (Chainable)
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->_ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->_ident;
    }

    public function set_label($label)
    {
        $this->_label = $label;
        return $this;
    }

    /**
    * @return string
    */
    public function label()
    {
        if ($this->_label === null) {
            $this->_label = ucwords(str_replace(['_', '.', '/'], ' ', $this->ident()));
        }
        return $this->_label;
    }

    public function actions()
    {
        return [];
    }

    /**
    * @param boolean show
    * @throws InvalidArgumentException
    * @return Widget Chainable
    */
    public function set_show_actions($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show actions must be a boolean');
        }
        $this->_show_actions = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_actions()
    {
        if ($this->_show_actions !== false) {
            return (count($this->actions()) > 0);
        } else {
            return false;
        }
    }

    /**
    * @param boolean show
    * @throws InvalidArgumentException
    * @return Widget Chainable
    */
    public function set_show_label($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show actions must be a boolean');
        }
        $this->_show_label = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_label()
    {
        if ($this->_show_label !== false) {
            return ($this->label() == '');
        } else {
            return false;
        }
    }

    public function render($template = null)
    {
        unset($template);
        $view = new WidgetView();
        $view->set_context($this);
        $content = $view->render_template($this->ident());
        return $content;
    }

    public function layout()
    {
        return true;
    }
}
