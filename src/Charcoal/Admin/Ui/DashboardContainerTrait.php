<?php

namespace Charcoal\Admin\Ui;

use Exception;
use InvalidArgumentException;

// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\DashboardBuilder;
use Charcoal\Ui\Dashboard\DashboardInterface;

/**
 * Implements Charcoal\Admin\Ui\DashboardContainerInterface
 */
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
     * @var DashboardBuilder $dashboardBuilder
     */
    private $dashboardBuilder;

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
     * @return array
     */
    abstract protected function createDashboardConfig();

    /**
     * @return DashboardInterface
     */
    public function dashboard()
    {
        if ($this->dashboard === null) {
            $this->dashboard = $this->createDashboard();
        }
        return $this->dashboard;
    }

    /**
     * @return DashboardInterface
     */
    protected function createDashboard()
    {
        $dashboardConfig = $this->createDashboardConfig();
        $dashboard       = $this->dashboardBuilder->build($dashboardConfig);

        return $dashboard;
    }

    /**
     * @param DashboardBuilder $builder The builder, to create customized dashboard objects.
     * @return void
     *
     */
    protected function setDashboardBuilder(DashboardBuilder $builder)
    {
        $this->dashboardBuilder = $builder;
    }

    /**
     * @throws Exception If the dashboard builder dependency was not previously set / injected.
     * @return DashboardBuilder
     */
    protected function dashboardBuilder()
    {
        if ($this->dashboardBuilder === null) {
            throw new Exception(
                'Dashboard builder was not set.'
            );
        }
        return $this->dashboardBuilder;
    }
}
