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
    * @var LayoutWidget $$layout
    */
    public $layout;
    /**
    * @var array $widgets
    */
    public $widgets;

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return Dashboard Chainable
    */
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget([
                'logger'=>$this->logger()
            ]);
            $l->set_data($layout);
            $this->layout = $l;
        } else {
            throw new InvalidArgumentException(
                'LayoutWidget must be a LayoutWidget object or an array'
            );
        }
        return $this;
    }

    /**
    * @return LayoutWidget
    */
    public function layout()
    {
        return $this->layout;
    }

    /**
    * @param array $widgets
    * @throws InvalidArgumentException
    * @return Dashboard Chainable
    */
    public function set_widgets($widgets)
    {
        if (!is_array($widgets)) {
            throw new InvalidArgumentException(
                'Widgets must be an array'
            );
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
            throw new InvalidArgumentException(
                'Widget ident needs to be a string'
            );
        }

        if (($widget instanceof WidgetInterface)) {
            $this->widgets[$widget_ident] = $widget;
        } else if (is_array($widget)) {
            if (!isset($widget['ident'])) {
                $widget['ident'] = $widget_ident;
            }

            $widget_type = isset($widget['type']) ? $widget['type'] : null;
            $w = WidgetFactory::instance()->create($widget_type, [
                'logger'=>$this->logger()
            ]);
            $w->set_data($widget);
            $this->widgets[$widget_ident] = $w;
        } else {
            throw new InvalidArgumentException(
                'Invalid Widget'
            );
        }
    }

    /**
    * Widgets generator
    */
    public function widgets()
    {
        if ($this->widgets === null) {
            yield null;
        } else {
            foreach ($this->widgets as $widget) {
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
