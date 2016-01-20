<?php

namespace Charcoal\Admin\Ui;

interface DashboardContainerInterface
{
    /**
     * @param array $data
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardData($data = null);

    /**
     * @param string $dashboardIdent
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardIdent($dashboardIdent);

    /**
     * @return string
     */
    public function dashboardIdent();

    /**
     * @param mixed $dashboardConfig
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardConfig($dashboardConfig);

    /**
     * @return mixed
     */
    public function dashboardConfig();

    /**
     * @param array $data
     * @return mixed
     */
    public function createDashboardConfig($data = null);

    /**
     * @param mixed $dashboard
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboard($dashboard);

    /**
     * @return Dashboard
     */
    public function dashboard();

    /**
     * @param array $data Optional
     * @return Dashboard
     */
    public function createDashboard(array $data = null);
}
