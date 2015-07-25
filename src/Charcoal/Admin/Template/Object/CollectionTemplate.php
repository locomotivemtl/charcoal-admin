<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;


// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
* admin/object/collection template.
*/
class CollectionTemplate extends AdminTemplate implements CollectionContainerInterface, DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;


    /**
    * @param array $data
    * @return Collection Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        $this->set_collection_data($data);
        $this->set_dashboard_data($data);

        return $this;
    }

    /**
    * @param array $data Optional
    * @throws Exception
    * @return Dashboard
    */
    public function create_dashboard(array $data = null)
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();

        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception('No dashboard for object');
        }

        if ($dashboard_ident === null || $dashboard_ident === '') {
            if (!isset($admin_metadata['default_collection_dashboard'])) {
                throw new Exception('No default collection dashboard defined in object admin metadata');
            }
            $dashboard_ident = $admin_metadata['default_collection_dashboard'];
        }
        if ($dashboard_config === null || empty($dashboard_config)) {
            if (!isset($admin_metadata['dashboards']) || !isset($admin_metadata['dashboards'][$dashboard_ident])) {
                throw new Exception('Dashboard config is not defined.');
            }
            $dashboard_config = $admin_metadata['dashboards'][$dashboard_ident];
        }

        $dashboard = new Dashboard();
        if (is_array($data)) {
            $dashboard->set_data($data);
        }
        $dashboard->set_data($dashboard_config);
        return $dashboard;
    }



    public function create_collection_config($config_data = null)
    {

    }

}
