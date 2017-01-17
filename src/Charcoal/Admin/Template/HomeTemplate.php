<?php

namespace Charcoal\Admin\Template;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Widget\DashboardWidget;

// Local parent namespace dependencies
use Charcoal\Admin\AdminTemplate;

/**
 * The Home template is a simple Dashboard, loaded from the metadata.
 */
class HomeTemplate extends AdminTemplate implements DashboardContainerInterface
{
    use DashboardContainerTrait;

    /**
     * @param array $data Optional dashboard data.
     * @return Charcoal\Ui\Dashboard\DashboardInterface
     */
    public function createDashboard(array $data = null)
    {
        $dashboardIdent = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        $metadata = $this->metadata();

        $dashboard = new DashboardWidget();
        if (is_array($data)) {
            $dashboard->setData($data);
        } elseif (isset($metadata['dashboard'])) {
            $dashboard->setData($metadata['dashboard']);
        }

        return $dashboard;
    }
}
