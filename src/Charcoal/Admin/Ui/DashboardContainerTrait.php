<?php

namespace Charcoal\Admin\Ui;

use \InvalidArgumentException as InvalidArgumentException;

trait DashboardContainerTrait
{
    /**
    * @var string $_dashboboard_ident
    */
    protected $_dashboard_ident;
    /**
    * @var mixed $_dashboard_config
    */
    protected $_dashboard_config;
    /**
    * @var Dashboard $_dashboard
    */
    protected $_dashboard;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_data($data = null)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        
        if (isset($data['dashboard_ident'])) {
            $this->set_dashboard_ident($data['dashboard_ident']);
        }
        if (isset($data['dashboard_config'])) {
            $this->set_dashboard_config($data['dashboard_config']);
        }

        return $this;
    }

    /**
    * @param string $dashboard_ident
    * @throws InvalidArgumentException
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_ident($dashboard_ident)
    {
        if (!is_string($dashboard_ident)) {
            throw new InvalidArgumentException('Dashboard ident needs to be a string');
        }
        $this->_dashboard_ident = $dashboard_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function dashboard_ident()
    {
        return $this->_dashboard_ident;
    }

    /**
    * @param mixed $dashboard_config
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_config($dashboard_config)
    {
        $this->_dashboard_config = $dashboard_config;
        return $this;
    }

    /**
    * @return mixed
    */
    public function dashboard_config()
    {
        if ($this->_dashboard_config === null) {
            $this->_dashboard_config = $this->create_dashboard_config();
        }
        return $this->_dashboard_config;
    }

    public function create_dashboard_config($data = null)
    {
        return null;
    }

    /**
    * @param Dashboard $dashboard
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard($dashboard)
    {
        $this->_dashboard = $dashboard;
        return $this;
    }

    /**
    * @return Dashboard
    */
    public function dashboard()
    {
        if ($this->_dashboard === null) {
            $this->_dashboard = $this->create_dashboard();
        }
        return $this->_dashboard;
    }

    /**
    * @param array $data Optional
    * @return Dashboard
    */
    abstract public function create_dashboard(array $data = null);
}
