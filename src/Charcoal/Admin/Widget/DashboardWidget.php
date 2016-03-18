<?php

namespace Charcoal\Admin\Widget;

// Module `charcoal-ui` dependencies
use \Charcoal\Ui\Dashboard\DashboardInterface;
use \Charcoal\Ui\Dashboard\DashboardTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

use \Charcoal\Admin\AdminWidget;

/**
 * The dashboard widget is a simple dashboard interface / layout aware object.
 */
class DashboardWidget extends AdminWidget implements
    DashboardInterface,
    LayoutAwareInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;
}
