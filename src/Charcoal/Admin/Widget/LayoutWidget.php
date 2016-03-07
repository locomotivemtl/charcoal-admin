<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;
use \Iterator;

// Dependencies from `charcoal-ui`
use \Charcoal\Ui\Layout\LayoutInterface;
use \Charcoal\Ui\Layout\LayoutTrait;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminWidget;


/**
 * Layout Widget Controller
 */
class LayoutWidget extends AdminWidget implements LayoutInterface
{
    use LayoutTrait;
}
