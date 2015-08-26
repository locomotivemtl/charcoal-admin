<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Charcoal;

// From `charcoal-base`
use \Charcoal\Widget\WidgetFactory;
use \Charcoal\Widget\WidgetInterface;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\LayoutWidget;

/**
*
*/
class DashboardWidget extends AdminWidget
{
    /**
    * @var LayoutWidget $_layout
    */
    public $_layout;
    /**
    * @var array $_widgets
    */
    public $_widgets;

    /**
    * @param array $data
    * @return Dashboard Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['layout']) && $data['layout'] !== null) {
            $this->set_layout($data['layout']);
        }
        if (isset($data['widgets']) && $data['widgets'] !== null) {
            $this->set_widgets($data['widgets']);
        }

        return $this;
    }

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return Dashboard Chainable
    */
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->_layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget();
            $l->set_data($layout);
            $this->_layout = $l;
        } else {
            throw new InvalidArgumentException('LayoutWidget must be a LayoutWidget object or an array');
        }
        return $this;
    }

    /**
    * @return LayoutWidget
    */
    public function layout()
    {
        return $this->_layout;
    }

    /**
    * @param array $widgets
    * @throws InvalidArgumentException
    * @return Dashboard Chainable
    */
    public function set_widgets($widgets)
    {
        if (!is_array($widgets)) {
            throw new InvalidArgumentException('Widgets must be an array');
        }
        foreach ($widgets as $widget_ident => $widget) {
            $this->add_widget($widget_ident, $widget);
        }
        return $this;
    }

    /**
    * @param string $widget_ident
    * @param WidgetInterface|array $widget
    * @throws InvalidArgumentException
    */
    public function add_widget($widget_ident, $widget)
    {
        if (!is_string($widget_ident)) {
            throw new InvalidArgumentException('Widget ident needs to be a string');
        }

        if (($widget instanceof WidgetInterface)) {
            $this->_widgets[$widget_ident] = $widget;
        } else if (is_array($widget)) {
            if (!isset($widget['ident'])) {
                $widget['ident'] = $widget_ident;
            }
            //var_dump($widget);
            $widget_type = isset($widget['type']) ? $widget['type'] : null;
            $w = WidgetFactory::instance()->create($widget_type);
            $w->set_data($widget);
            $this->_widgets[$widget_ident] = $w;
        } else {
            throw new InvalidArgumentException('Invalid Widget');
        }
    }

    /**
    * Widgets generator
    */
    public function widgets()
    {
        //var_dump($this->_widgets);
        if ($this->_widgets === null) {
            yield null;
        } else {
            foreach ($this->_widgets as $widget) {
                if ($widget->active() === false) {
;
                    continue;
                }
                Charcoal::logger()->debug(sprintf('Yield widget %s', $widget->type()));
                $GLOBALS['widget_template'] = $widget->type();
                yield $widget->ident() => $widget;
            }
        }
    }
}
