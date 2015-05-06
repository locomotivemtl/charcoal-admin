<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-base`
use \Charcoal\Widget\AbstractWidget as AbstractWidget;

// From `charcoal-base`
use \Charcoal\Widget\WidgetView as WidgetView;

class Widget extends AbstractWidget
{
    private $_type;
    /**
    * @var string $_ident
    */
    private $_ident = '';
    private $_label;
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
    * @throws InvalidArgumentException
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
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
        if (isset($data['show_actions']) && $data['show_actions'] !== null) {
            $this->set_show_actions($data['show_actions']);
        }

        return $this;
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
        return 'LABEL -'.$this->_label;
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

    public function render()
    {
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
