<?php

namespace Charcoal\Admin\Template;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;
use \Charcoal\Admin\Widget\SidemenuWidget;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

use \Charcoal\App\Template\WidgetFactory;

class ObjectTemplate extends AdminTemplate implements ObjectContainerInterface
{
    use ObjectContainerTrait;


}
