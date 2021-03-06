<?php

namespace Charcoal\Admin\Ui;

/**
 *
 */
interface DashboardContainerInterface
{
    /**
     * @param string $dashboardIdent The dashboard identifier.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardIdent($dashboardIdent);

    /**
     * @return string
     */
    public function dashboardIdent();

    /**
     * @param mixed $dashboardConfig The dashboard configuration.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardConfig($dashboardConfig);

    /**
     * @return mixed
     */
    public function dashboardConfig();

    /**
     * @return Dashboard
     */
    public function dashboard();
}
