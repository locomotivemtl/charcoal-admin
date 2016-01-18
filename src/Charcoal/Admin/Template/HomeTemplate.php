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

    public function __construct()
    {
        header('Location:object/collection?obj_type=alert/alert');
        die();
    }

    /**
    * @param array $data
    * @return Home Chainable
    */
    public function setData(array $data)
    {
        parent::setData($data);

        $this->setDashboardData($data);

        return $this;
    }

    /**
    * @param array $data Optional
    * @return Dashboard
    */
    public function createDashboard(array $data = null)
    {
        $dashboardIdent = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        $metadata = $this->metadata();

        $dashboard = new Dashboard();
        if (is_array($data)) {
            $dashboard->setData($data);
        } else if (isset($metadata['dashboard'])) {
            $dashboard->setData($metadata['dashboard']);
        }

        return $dashboard;
    }
}
