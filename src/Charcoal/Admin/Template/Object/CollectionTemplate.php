<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
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

        $obj = $this->proto();
        if ($obj->source()->table_exists() === false) {
            $obj->source()->create_table();
            $this->add_feedback('success', 'A new table was created for object.');
        }

    }

    /**
    * @param array $data Optional
    * @throws Exception
    * @return Dashboard
    * @see DashboardContainerTrait::create_dashboard()
    */
    public function create_dashboard(array $data = null)
    {
        $dashboard_config = $this->obj_collection_dashboard_config();

        $dashboard = new Dashboard([
            'logger'=>$this->logger()
        ]);
        if ($data !== null) {
            $dashboard->set_data($data);
        }
        $dashboard->set_data($dashboard_config);

        return $dashboard;
    }

    /**
    * @return SidemenuWidgetInterface
    */
    public function sidemenu()
    {
        $dashboard_config = $this->obj_collection_dashboard_config();;
        if (!isset($dashboard_config['sidemenu'])) {
            return null;
        }

        $sidemenu_config = $dashboard_config['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';
        $widget_factory = new WidgetFactory();
        $widget_type = isset($sidemenu_config['widget_type']) ? $sidemenu_config['widget_type'] : 'charcoal/admin/widget/sidemenu';
        $sidemenu = $widget_factory->create($widget_type, [
            'logger'=>$this->logger()
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
    public function search_widget()
    {
        $factory = new WidgetFactory();
        $widget = $factory->create('charcoal/admin/widget/search');
        $widget->set_obj_type( $this->obj_type() );

        $obj = $this->proto();
        $metadata = $obj->metadata();

        $admin_metadata = $metadata['admin'];
        $lists = $admin_metadata['lists'];

        $list_ident = ( isset($admin_metadata['default_search_list']) ) ? $admin_metadata['default_search_list'] : '';

        if (!$list_ident) {
            $list_ident = ( isset($admin_metadata['default_list']) ) ? $admin_metadata['default_list'] : '';
        }

        if (!$list_ident) {
            $list_ident = 'default';
        }

        // Note that if the ident doesn't match a list,
        // it will return basicly every properties of the object
        $widget->set_collection_ident( $list_ident );
        return $widget;
    }

    private function obj_collection_dashboard_config()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();

        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($admin_metadata === null) {
            throw new Exception(
                'No dashboard for object (no admin metadata).'
            );
        }

        if ($dashboard_ident === null || $dashboard_ident === '') {
            if (!isset($admin_metadata['default_collection_dashboard'])) {
                throw new Exception(
                    'No default collection dashboard defined in object admin metadata.'
                );
            }
            $dashboard_ident = $admin_metadata['default_collection_dashboard'];
        }
        if ($dashboard_config === null || empty($dashboard_config)) {
            if (!isset($admin_metadata['dashboards']) || !isset($admin_metadata['dashboards'][$dashboard_ident])) {
                throw new Exception(
                    'Dashboard config is not defined.'
                );
            }
            $dashboard_config = $admin_metadata['dashboards'][$dashboard_ident];
        }

        return $dashboard_config;
    }

}
