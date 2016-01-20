<?php

namespace Charcoal\Admin\Ui;

/**
 *
 */
interface DashboardInterface
{

    /**
     * @param LayoutInterface|array
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout);

    /**
     * @return LayoutInterface
     */
    public function layout();

    /**
     * @param array $widgets
     * @return DashboardInterface Chainable
     */
    public function setWidgets($widgets);

    /**
     * @param string                $widget_ident
     * @param WidgetInterface|array $widget
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget);

    /**
     * Widgets generator
     */
    public function widgets();

    /**
     * @return boolean
     */
    public function hasWidgets();

    /**
     * @return integer
     */
    public function numWidgets();
}
