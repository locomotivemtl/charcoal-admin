<?php

namespace Charcoal\Admin\Widget;

use \Pimple\Container;

// Module `charcoal-ui` dependencies
use \Charcoal\Ui\Dashboard\DashboardInterface;
use \Charcoal\Ui\Dashboard\DashboardTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;
use \Charcoal\Ui\UiItemTrait;
use \Charcoal\Ui\UiItemInterface;

use \Charcoal\Admin\AdminWidget;

/**
 * The dashboard widget is a simple dashboard interface / layout aware object.
 */
class DashboardWidget extends AdminWidget implements
    DashboardInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;
    use UiItemTrait;

    /**
     * @param array|\ArrayAccess $data Dependencies.
     */
    public function __construct($data)
    {
        parent::__construct($data);

        // Set up layout builder (to fulfill LayoutAware Interface)
        if (isset($data['layout_builder'])) {
            $this->setLayoutBuilder($data['layout_builder']);
        }

    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        // Fill LayoutAwareInterface dependencies
        $this->setLayoutBuilder($container['layout/builder']);
    }
}
