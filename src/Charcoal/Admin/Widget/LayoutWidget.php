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

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->set_data($data);
        }
    }

}
