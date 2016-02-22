<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

use \Pimple\Container;

// Module `charcoal-base` dependencies
use \Charcoal\App\Template\WidgetFactory as WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Template\ObjectTemplate;
use \Charcoal\Admin\Ui\DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;
use \Charcoal\Admin\Widget\DashboardWidget;
use \Charcoal\Admin\Widget\SidemenuWidget;

/**
 * Object Edit Template
 */
class EditTemplate extends ObjectTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    //use ObjectContainerTrait;

    private $sidemenu;

    /**
     * @var WidgetFactory $widgetFactory
     */
    private $widgetFactory;

    /**
     * @param Container $container
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);
    }


    /**
     * @param WidgetFactory $factory The widget factory, to create the dashboard and sidemenu widgets.
     */
    public function setWidgetFactory(WidgetFactory $factory)
    {
        $this->widgetFactory = $factory;
        return $this;
    }

    /**
     * Safe Widget Factory getter.
     * Create the widget factory if it was not preiously injected / set.
     *
     * @return WidgetFactory
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            $this->widgetFactory = new WidgetFactory();
        }
        return $this->widgetFactory;
    }

    /**
     * @param array $data Optional
     * @throws Exception
     * @return Dashboard
     * @see DashboardContainerTrait::createDashboard()
     */
    public function createDashboard(array $data = null)
    {
        $dashboardConfig = $this->objEditDashboardConfig();

        $dashboard = $this->widgetFactory()->create('charcoal/admin/widget/dashboard', [
            'logger' => $this->logger
        ]);
        if ($data !== null) {
            $dashboard->setData($data);
        }
        $dashboard->setData($dashboardConfig);

        return $dashboard;
    }

    /**
     * @return SidemenuWidgetInterface
     */
    public function sidemenu()
    {
        $dashboardConfig = $this->objEditDashboardConfig();
        ;
        if (!isset($dashboardConfig['sidemenu'])) {
            return null;
        }

        $sidemenuConfig = $dashboardConfig['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';
        $widget_type = isset($sidemenuConfig['widget_type']) ? $sidemenuConfig['widget_type'] : 'charcoal/admin/widget/sidemenu';
        $sidemenu = $this->widgetFactory()->create($widget_type, [
            'logger'=>$this->logger
        ]);
        return $sidemenu;
    }

    private function objEditDashboardConfig()
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $dashboardIdent = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception(
                'No dashboard for object'
            );
        }

        if ($dashboardIdent === null || $dashboardIdent === '') {
            if (!isset($admin_metadata['default_edit_dashboard'])) {
                throw new Exception(
                    'No default edit dashboard defined in object admin metadata'
                );
            }
            $dashboardIdent = $admin_metadata['default_edit_dashboard'];
        }
        if ($dashboardConfig === null || empty($dashboardConfig)) {
            if (!isset($admin_metadata['dashboards']) || !isset($admin_metadata['dashboards'][$dashboardIdent])) {
                throw new Exception(
                    'Dashboard config is not defined.'
                );
            }
            $dashboardConfig = $admin_metadata['dashboards'][$dashboardIdent];
        }

        return $dashboardConfig;
    }
}
