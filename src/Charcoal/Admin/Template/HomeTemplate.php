<?php

namespace Charcoal\Admin\Template;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Layout as Layout;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
* The Home template is a simple Dashboard, loaded from the metadata.
*/
class HomeTemplate extends AdminTemplate implements DashboardContainerInterface
{
    use DashboardContainerTrait;

    /**
    * @param array $data
    * @return Home Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        $this->set_dashboard_data($data);

        return $this;
    }

    /**
    * @param array $data Optional
    * @return Dashboard
    */
    public function create_dashboard(array $data = null)
    {
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();

        $metadata = $this->metadata();

        $dashboard = new Dashboard();
        if (is_array($data)) {
            $dashboard->set_data($data);
        } else if (isset($metadata['dashboard'])) {
            $dashboard->set_data($metadata['dashboard']);
        }

        return $dashboard;
    }
}
