<?php

namespace Charcoal\Admin\Template;

use \Charcoal\Admin\Template as Template;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Layout as Layout;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;

/**
* The Home template is a simple Dashboard, loaded from the metadata.
*/
class Home extends Template implements DashboardContainerInterface
{
    use DashboardContainerTrait;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return Edit Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        parent::set_data($data);

        $this->set_dashboard_data($data);

        return $this;
    }

    /**
    * @return Dashboard
    */
    public function create_dashboard($data = null)
    {
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();

        $metadata = $this->metadata();

        $dashboard = new Dashboard();
        if ($data !== null) {
            $dashboard->set_data($data);
        } else if (isset($metadata['dashboard'])) {
            $dashboard->set_data($metadata['dashboard']);
        }

        return $dashboard;
    }
}
