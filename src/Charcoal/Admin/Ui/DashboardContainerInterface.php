<?php

namespace Charcoal\Admin\Ui;

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
     * @param array $data Optional config data.
     * @return mixed
     */
    public function createDashboardConfig(array $data = null);

    /**
     * @param mixed $dashboard The dashboard.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboard($dashboard);

    /**
     * @return Dashboard
     */
    public function dashboard();

    /**
     * @param array $data Optional dashboard data.
     * @return Dashboard
     */
    public function createDashboard(array $data = null);
}
