<?php

namespace Charcoal\Admin\Widget;


// From `charcoal-base`
use \Charcoal\Widget\WidgetFactory;
use \Charcoal\Widget\WidgetInterface;

use \Charcoal\Admin\Ui\DashboardInterface;
use \Charcoal\Admin\Ui\DashboardTrait;
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\LayoutWidget;

/**
*
*/
class DashboardWidget extends AdminWidget implements DashboardInterface
{
    use DashboardTrait;

    /**
    * > DashboardTrait > create_layout()
    *
    * @param array|null $data
    * @return LayoutInterface
    */
    public function createLayout(array $data = null)
    {
        $layout = new LayoutWidget([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $layout->setData($data);
        }
        return $layout;
    }
}
