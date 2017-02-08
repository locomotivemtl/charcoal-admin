<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;
use \Iterator;

// From 'charcoal-ui'
use \Charcoal\Ui\Layout\LayoutInterface;
use \Charcoal\Ui\Layout\LayoutTrait;

// From 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;

/**
 * Layout Widget Controller
 */
class LayoutWidget extends AdminWidget implements LayoutInterface
{
    use LayoutTrait;
}
