<?php

namespace Charcoal\Admin\Template;

interface DashboardContainerInterface
{
    /**
    * @param array $data
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_data($data = null);

    /**
    * @param string $dashboard_ident
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_ident($dashboard_ident);

    /**
    * @return string
    */
    public function dashboard_ident();

    /**
    * @param mixed $dashboard_config
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard_config($dashboard_config);

    /**
    * @return mixed
    */
    public function dashboard_config();

    /**
    * @param array $data
    * @return mixed
    */
    public function create_dashboard_config($data = null);

    /**
    * @param mixed $dashboard
    * @return DashboardContainerInterface Chainable
    */
    public function set_dashboard($dashboard);

    /**
    * @return Dashboard
    */
    public function dashboard();

    /**
    * @param array $data
    * @return Dashboard
    */
    public function create_dashboard($data = null);

}
