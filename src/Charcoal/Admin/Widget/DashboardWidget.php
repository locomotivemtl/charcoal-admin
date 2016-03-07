<?php

namespace Charcoal\Admin\Widget;

// From `charcoal-base`
use \Charcoal\Widget\WidgetFactory;
use \Charcoal\Widget\WidgetInterface;

use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

use \Charcoal\Admin\Ui\DashboardInterface;
use \Charcoal\Admin\Ui\DashboardTrait;
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\LayoutWidget;

/**
 *
 */
class DashboardWidget extends AdminWidget implements DashboardInterface, LayoutAwareInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;
}
