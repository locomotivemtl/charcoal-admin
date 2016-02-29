<?php

namespace Charcoal\Admin\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

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

    private $widgetFactory;

    /**
     * @param LayoutWidget|array
     * @throws InvalidArgumentException
     * @return Dashboard Chainable
     */
    public function setLayout($layout)
    {
        if (($layout instanceof LayoutInterface)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = $this->createLayout($layout);
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
    abstract public function createLayout(array $data = null);

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
    public function setWidgets(array $widgets)
    {
        foreach ($widgets as $widgetIdent => $widget) {
            $this->addWidget($widgetIdent, $widget);
        }
        return $this;
    }

    /**
     * @param string                $widgetIdent
     * @param WidgetInterface|array $widget
     * @throws InvalidArgumentException
     */
    public function addWidget($widgetIdent, $widget)
    {
        if (!is_string($widgetIdent)) {
            throw new InvalidArgumentException(
                'Widget ident needs to be a string'
            );
        }

        if (($widget instanceof WidgetInterface)) {
            $this->widgets[$widgetIdent] = $widget;
        } else if (is_array($widget)) {
            if (!isset($widget['ident'])) {
                $widget['ident'] = $widgetIdent;
            }

            $w = $this->createWidget($widget);
            $this->widgets[$widgetIdent] = $w;
        } else {
            throw new InvalidArgumentException(
                'Invalid Widget'
            );
        }
    }

    /**
     * Safe Widget Factory getter.
     * Create a new factory if none was set / injected.
     *
     * @return WidgetFactory
     */
    private function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            $this->widgetFactory = new WidgetFactory();
        }
        return $this->widgetFactory;
    }

    /**
     * @param array $data
     * @return WidgetInterface
     */
    public function createWidget(array $data = null)
    {
        if(isset($data['controller'])) {
            $widgetType = $data['controller'];
        } elseif (isset($data['type'])) {
            $widgetType  = $data['type'];
        } else {
            $widgetType = null;
        }

        $this->logger->debug('Creating a new widget: '.$widgetType, $data);
        $widget = $this->widgetFactory()->create($widgetType, [
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $widget->setData($data);
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
                    continue;
                }
                $this->logger->debug(sprintf('Yield widget %s', $widget->type()));
                $GLOBALS['widget_template'] = $widget->type();
                yield $widget->ident() => $widget;
            }
        }
    }

    /**
     * @return boolean
     */
    public function hasWidgets()
    {
        return (count($this->widgets) > 0);
    }

    /**
     * @return integer
     */
    public function numWidgets()
    {
        return count($this->widgets);
    }
}
