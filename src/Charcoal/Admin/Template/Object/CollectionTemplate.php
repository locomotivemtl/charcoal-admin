<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait;
use \Charcoal\Admin\Ui\DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait;
use \Charcoal\Admin\Widget\DashboardWidget as Dashboard;



// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
* admin/object/collection template.
*/
class CollectionTemplate extends AdminTemplate implements CollectionContainerInterface, DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

    private $sidemenu;

    public function __construct(array $data = null)
    {
        parent::__construct($data);

        $this->setData($data);

        $obj = $this->proto();
        if (!$obj) {
            return;
        }
        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $this->add_feedback('success', 'A new table was created for object.');
        }

    }

    /**
    * @param array $data Optional
    * @throws Exception
    * @return Dashboard
    * @see DashboardContainerTrait::createDashboard()
    */
    public function createDashboard(array $data = null)
    {
        $dashboardConfig = $this->objCollectionDashboardConfig();

        $dashboard = new Dashboard([
            'logger'=>$this->logger
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
        $dashboardConfig = $this->objCollectionDashboardConfig();
;
        if (!isset($dashboardConfig['sidemenu'])) {
            return null;
        }

        $sidemenuConfig = $dashboardConfig['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';
        $widgetFactory = new WidgetFactory();
        $widgetType = isset($sidemenuConfig['widget_type']) ? $sidemenuConfig['widget_type'] : 'charcoal/admin/widget/sidemenu';
        $sidemenu = $widgetFactory->create($widgetType, [
            'logger'=>$this->logger
        ]);
        return $sidemenu;
    }

    /**
    * Sets the search widget accodingly
    * Uses the "default_search_list" ident that should point
    * on ident in the "lists"
    *
    * @see charcoal/admin/widget/search
    * @return widget
    */
    public function searchWidget()
    {
        $factory = new WidgetFactory();
        $widget = $factory->create('charcoal/admin/widget/search', [
            'logger'=>$this->logger
        ]);
        $widget->set_objType( $this->objType() );

        $obj = $this->proto();
        $metadata = $obj->metadata();

        $admin_metadata = $metadata['admin'];
        $lists = $admin_metadata['lists'];

        $listIdent = ( isset($admin_metadata['default_search_list']) ) ? $admin_metadata['default_search_list'] : '';

        if (!$listIdent) {
            $listIdent = ( isset($admin_metadata['default_list']) ) ? $admin_metadata['default_list'] : '';
        }

        if (!$listIdent) {
            $listIdent = 'default';
        }

        // Note that if the ident doesn't match a list,
        // it will return basicly every properties of the object
        $widget->setCollectionIdent( $listIdent );
        return $widget;
    }

    private function objCollectionDashboardConfig()
    {
        $metadata = $obj->metadata();
        $dashboardIdent = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception(
                'No dashboard for object (no admin metadata).'
            );
        }

        if ($dashboardIdent === null || $dashboardIdent === '') {
            if (!isset($admin_metadata['defaultCollectionDashboard'])) {
                throw new Exception(
                    'No default collection dashboard defined in object admin metadata.'
                );
            }
            $dashboardIdent = $admin_metadata['default_collection_d ashboard'];
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
