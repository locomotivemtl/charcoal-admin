<?php

namespace Charcoal\Admin\Template;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;
use \Charcoal\Admin\AdminTemplate;

/**
 * Object base template, which is simple an object container.
 */
class ObjectTemplate extends AdminTemplate implements ObjectContainerInterface
{
    use ObjectContainerTrait;
}
