<?php

namespace Charcoal\Admin\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;
use \Charcoal\App\Template\WidgetInterface;

// Local namespace dependencies
use \Charcoal\Admin\Ui\LayoutInterface;

/**
*
*/
trait DashboardTrait
{
    /**
    * @var LayoutWidget $layout
    */
    private $layout;

    /**
    * @var array $widgets
    */
    private $widgets;

    private $widget_factory;

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return Dashboard Chainable
    */
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutInterface)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = $this->create_layout($layout);
            $this->layout = $l;
        } else {
            throw new InvalidArgumentException(
                'LayoutWidget must be a LayoutWidget object or an array'
            );
        }
        return $this;
    }

    /**
    * @param array|null $data
    * @return LayoutInterface
    */
    abstract public function create_layout(array $data = null);

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

            $w = $this->create_widget($widget);
            $this->widgets[$widget_ident] = $w;
        } else {
            throw new InvalidArgumentException(
                'Invalid Widget'
            );
        }
    }

    private function widget_factory()
    {
        if($this->widget_factory === null) {
            $this->widget_factory = new WidgetFactory();
        }
        return $this->widget_factory;
    }

    /**
    * @param array $data
    * @return WidgetInterface
    */
    public function create_widget(array $data = null)
    {
        $widget_type = isset($data['type']) ? $data['type'] : null;

        $this->logger()->debug('Creating a new widget: '.$data['type'], $data);

        $widget = $this->widget_factory()->create($widget_type, [
            'logger'=>$this->logger()
        ]);
        if ($data !== null) {
            $widget->set_data($data);
        }
        return $widget;
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

    /**
    * @return boolean
    */
    public function has_widgets()
    {
        return (count($this->widgets) > 0);
    }

    /**
    * @return integer
    */
    public function num_widgets()
    {
        return count($this->widgets);
    }
}
