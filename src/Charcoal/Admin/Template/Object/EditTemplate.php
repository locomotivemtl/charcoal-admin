<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Module `charcoal-base` dependencies
use \Charcoal\App\Template\WidgetFactory as WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Template\ObjectTemplate;
use \Charcoal\Admin\Ui\DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;
use \Charcoal\Admin\Widget\DashboardWidget;
use \Charcoal\Admin\Widget\SidemenuWidget;

class EditTemplate extends ObjectTemplate implements DashboardContainerInterface, ObjectContainerInterface
{
    use DashboardContainerTrait;
    //use ObjectContainerTrait;

    private $sidemenu;

    /**
    * @param array $data Optional
    * @throws Exception
    * @return Dashboard
    * @see DashboardContainerTrait::create_dashboard()
    */
    public function create_dashboard(array $data = null)
    {
        $dashboard_config = $this->obj_edit_dashboard_config();

        $dashboard = new DashboardWidget([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $dashboard->set_data($data);
        }
        $dashboard->set_data($dashboard_config);

        return $dashboard;
    }

    /**
    * @return SidemenuWidgetInterface
    */
    public function sidemenu()
    {
        $dashboard_config = $this->obj_edit_dashboard_config();;
        if (!isset($dashboard_config['sidemenu'])) {
            return null;
        }

        $sidemenu_config = $dashboard_config['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';
        $widget_factory = new WidgetFactory();
        $widget_type = isset($sidemenu_config['widget_type']) ? $sidemenu_config['widget_type'] : 'charcoal/admin/widget/sidemenu';
        $sidemenu = $widget_factory->create($widget_type, [
            'logger'=>$this->logger
        ]);
        return $sidemenu;
    }

    private function obj_edit_dashboard_config()
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();

        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception(
                'No dashboard for object'
            );
        }

        if ($dashboard_ident === null || $dashboard_ident === '') {
            if (!isset($admin_metadata['default_edit_dashboard'])) {
                throw new Exception(
                    'No default edit dashboard defined in object admin metadata'
                );
            }
            $dashboard_ident = $admin_metadata['default_edit_dashboard'];
        }
        if ($dashboard_config === null || empty($dashboard_config)) {
            if (!isset($admin_metadata['dashboards']) || !isset($admin_metadata['dashboards'][$dashboard_ident])) {
                throw new Exception(
                    'Dashboard config is not defined.'
                );
            }
            $dashboard_config = $admin_metadata['dashboards'][$dashboard_ident];
        }

        return $dashboard_config;
    }
}
