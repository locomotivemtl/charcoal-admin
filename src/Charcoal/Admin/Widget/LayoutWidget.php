<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;
use \Iterator;


use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\LayoutInterface;
use \Charcoal\Admin\Ui\LayoutTrait;

/**
 * Layout Widget Controller
 */
class LayoutWidget extends AdminWidget implements LayoutInterface
{
    use LayoutTrait;
}
