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
    * @param array $data
    * @throws InvalidArgumentException
    * @return DashboardContainerInterface Chainable
    */
    public function setDashboardData($data = null)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        if (isset($data['dashboardIdent'])) {
            $this->setDashboardIdent($data['dashboardIdent']);
        }
        if (isset($data['dashboardConfig'])) {
            $this->setDashboardConfig($data['dashboardConfig']);
        }

        return $this;
    }

    /**
    * @param string $dashboardIdent
    * @throws InvalidArgumentException
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
    * @param mixed $dashboardConfig
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

    public function createDashboardConfig($data = null)
    {
        return null;
    }

    /**
    * @param Dashboard $dashboard
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
    * @param array $data Optional
    * @return Dashboard
    */
    abstract public function createDashboard(array $data = null);
}
