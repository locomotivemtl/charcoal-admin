<?php

namespace Charcoal\Admin\Template\Object;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

use \Pimple\Container;

use \Charcoal\Translation\TranslationString;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait;
use \Charcoal\Admin\Ui\DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait;
use \Charcoal\Admin\Widget\DashboardWidget as Dashboard;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;

/**
 * admin/object/collection template.
 */
class CollectionTemplate extends AdminTemplate implements
    CollectionContainerInterface,
    DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

    /**
     * @var SidemenuWidgetInterface $sidemenu
     */
    private $sidemenu;

    /**
     * @var WidgetFactory $widgetFactory
     */
    private $widgetFactory;

    public function __construct(array $data = null)
    {
        parent::__construct($data);

        // $this->setData($data);

        $obj = $this->proto();
        if (!$obj) {
            return;
        }
        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $this->addFeedback('success', 'A new table was created for object.');
        }

    }

    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Optional CollectionContainerInterface depeendencies
        if ($container['model/factory']) {
            $this->setModelFactory($container['model/factory']);
        }

        if ($container['model/collection/loader']) {
            $this->setCollectionLoader($container['model/colletion/loader']);
        }
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
        $dashboardConfig = $this->objCollectionDashboardConfig();

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
        $dashboardConfig = $this->objCollectionDashboardConfig();
        ;
        if (!isset($dashboardConfig['sidemenu'])) {
            return null;
        }

        $sidemenuConfig = $dashboardConfig['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';
        $widgetType = isset($sidemenuConfig['widget_type']) ? $sidemenuConfig['widget_type'] : 'charcoal/admin/widget/sidemenu';
        $sidemenu = $this->widgetFactory()->create($widgetType, [
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
        $widget = $this->widgetFactory()->create('charcoal/admin/widget/search', [
            'logger'=>$this->logger
        ]);
        $widget->setObjType($this->objType());

        $obj = $this->proto();
        $metadata = $obj->metadata();

        $adminMetadata = $metadata['admin'];
        $lists = $adminMetadata['lists'];

        $listIdent = ( isset($adminMetadata['default_search_list']) ) ? $adminMetadata['default_search_list'] : '';

        if (!$listIdent) {
            $listIdent = ( isset($adminMetadata['default_list']) ) ? $adminMetadata['default_list'] : '';
        }

        if (!$listIdent) {
            $listIdent = 'default';
        }

        // Note that if the ident doesn't match a list,
        // it will return basicly every properties of the object
        $widget->setCollectionIdent($listIdent);
        return $widget;
    }

    /**
     * @return array
     */
    private function objCollectionDashboardConfig()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();

        $dashboardIdent = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        if ($adminMetadata === null) {
            throw new Exception(
                'No dashboard for object (no admin metadata).'
            );
        }

        if ($dashboardIdent === null || $dashboardIdent === '') {
            if (!isset($adminMetadata['default_collection_dashboard'])) {
                throw new Exception(
                    'No default collection dashboard defined in object admin metadata.'
                );
            }
            $dashboardIdent = $adminMetadata['default_collection_dashboard'];
        }
        if ($dashboardConfig === null || empty($dashboardConfig)) {
            if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
                throw new Exception(
                    'Dashboard config is not defined.'
                );
            }
            $dashboardConfig = $adminMetadata['dashboards'][$dashboardIdent];
        }

        return $dashboardConfig;
    }

    /**
     * @return string|TranslationString
     */
    public function title()
    {
        $config = $this->objCollectionDashboardConfig();

        if (isset($config['title'])) {
            return new TranslationString($config['title']);
        } else {
            $obj      = $this->proto();
            $objLabel = $this->objType();
            $metadata = $obj->metadata();

            if (isset($metadata['admin'])) {
                $metadata    = $metadata['admin'];
                $formIdent   = ( isset($metadata['default_list']) ? $metadata['default_list'] : '' );
                $objFormData = ( isset($metadata['lists'][$formIdent]) ? $metadata['lists'][$formIdent] : [] );

                if (isset($objFormData['label'])) {
                    $objLabel = new TranslationString($objFormData['label']);
                }
            }

            $this->title = sprintf('List: %s', $objLabel);
        }

        return $this->title;
    }
}
