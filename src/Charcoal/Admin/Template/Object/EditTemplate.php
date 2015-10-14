<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Module `charcoal-base` dependencies
use \Charcoal\Widget\WidgetFactory as WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Template\ObjectTemplate as ObjectTemplate;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;
use \Charcoal\Admin\Widget\DashboardWidget as DashboardWidget;


class EditTemplate extends ObjectTemplate implements DashboardContainerInterface, ObjectContainerInterface
{
    use DashboardContainerTrait;
    //use ObjectContainerTrait;

    /**
    * @param array $data Optional
    * @throws Exception
    * @return Dashboard
    */
    public function create_dashboard(array $data = null)
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();


        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception('No dashboard for object');
        }

        if ($dashboard_ident === null || $dashboard_ident === '') {
            if (!isset($admin_metadata['default_edit_dashboard'])) {
                throw new Exception('No default edit dashboard defined in object admin metadata');
            }
            $dashboard_ident = $admin_metadata['default_edit_dashboard'];
        }
        if ($dashboard_config === null || empty($dashboard_config)) {
            if (!isset($admin_metadata['dashboards']) || !isset($admin_metadata['dashboards'][$dashboard_ident])) {
                throw new Exception('Dashboard config is not defined.');
            }
            $dashboard_config = $admin_metadata['dashboards'][$dashboard_ident];
        }

        $dashboard = new DashboardWidget();
        if (is_array($data)) {
            $dashboard->set_data($data);
        }
        $dashboard->set_data($dashboard_config);

        return $dashboard;
    }
}
