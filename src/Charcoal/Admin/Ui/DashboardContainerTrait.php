<?php

namespace Charcoal\Admin\Ui;

use \InvalidArgumentException as InvalidArgumentException;

trait DashboardContainerTrait
{
    /**
     * @var string $dashboboardIdent
     */
    protected $dashboardIdent;
    /**
     * @var mixed $dashboardConfig
     */
    protected $dashboardConfig;
    /**
     * @var Dashboard $dashboard
     */
    protected $dashboard;

    /**
     * @param string $dashboardIdent The dashboard identifier.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardIdent($dashboardIdent)
    {
        if (!is_string($dashboardIdent)) {
            throw new InvalidArgumentException(
                'Dashboard ident needs to be a string'
            );
        }
        $this->dashboardIdent = $dashboardIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function dashboardIdent()
    {
        return $this->dashboardIdent;
    }

    /**
     * @param mixed $dashboardConfig The dasboard configuration.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboardConfig($dashboardConfig)
    {
        $this->dashboardConfig = $dashboardConfig;
        return $this;
    }

    /**
     * @return mixed
     */
    public function dashboardConfig()
    {
        if ($this->dashboardConfig === null) {
            $this->dashboardConfig = $this->createDashboardConfig();
        }
        return $this->dashboardConfig;
    }

    /**
     * @param array $data Optional dashboard config.
     * @return null
     */
    public function createDashboardConfig(array $data = null)
    {
        return null;
    }

    /**
     * @param mixed $dashboard The dashboard to set.
     * @return DashboardContainerInterface Chainable
     */
    public function setDashboard($dashboard)
    {
        $this->dashboard = $dashboard;
        return $this;
    }

    /**
     * @return Dashboard
     */
    public function dashboard()
    {
        if ($this->dashboard === null) {
            $this->dashboard = $this->createDashboard();
        }
        return $this->dashboard;
    }

    /**
     * @param array $data Optional dashboard data.
     * @return Dashboard
     */
    abstract public function createDashboard(array $data = null);
}
