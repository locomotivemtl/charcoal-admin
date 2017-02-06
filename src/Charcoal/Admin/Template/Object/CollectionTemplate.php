<?php

namespace Charcoal\Admin\Template\Object;

use Exception;
use InvalidArgumentException;

// From PSR-7 (http messaging)
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;


// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Widget\SearchWidget;

/**
 * Object collection template (table with a list of objects).
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
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required collection dependencies
        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);

        // Required dashboard dependencies.
        $this->setWidgetFactory($container['widget/factory']);
        $this->setDashboardBuilder($container['dashboard/builder']);
    }

    /**
     * @param RequestInterface $request PSR-7 request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        parent::init($request);
        $this->createObjTable();

        return true;
    }

    /**
     * @return void
     */
    private function createObjTable()
    {
        $obj = $this->proto();
        if (!$obj) {
            return;
        }
        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $this->addFeedback('success', 'A new table was created for object.');
        }
    }

    /**
     * @return array
     */
    protected function createDashboardConfig()
    {
        return $this->objCollectionDashboardConfig();
    }

    /**
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        $dashboardConfig = $this->dashboardConfig();

        if (isset($dashboardConfig['sidemenu'])) {
            return $this->createSidemenu($dashboardConfig['sidemenu']);
        } else {
            return $this->createSidemenu();
        }
    }

    /**
     * Sets the search widget accodingly
     * Uses the "default_search_list" ident that should point
     * on ident in the "lists"
     *
     * @return widget
     */
    public function searchWidget()
    {
        $widget = $this->widgetFactory()->create(SearchWidget::class);
        $widget->setObjType($this->objType());

        $listIdent = $this->metadataListIdent();

        // Note that if the ident doesn't match a list,
        // it will return basicly every properties of the object
        $widget->setCollectionIdent($listIdent);

        return $widget;
    }

    /**
     * @return string
     */
    private function metadataListIdent()
    {
        $adminMetadata = $this->objAdminMetadata();

        if (isset($adminMetadata['default_search_list'])) {
            $listIdent = $adminMetadata['default_search_list'];
        } elseif (isset($adminMetadata['default_list'])) {
            $listIdent = $adminMetadata['default_list'];
        } else {
            $listIdent = 'default';
        }

        return $listIdent;
    }

    /**
     * @throws Exception If no default collection is defined.
     * @return string
     */
    private function metadataDashboardIdent()
    {

        $dashboardIdent = filter_input(INPUT_GET, 'dashboard_ident', FILTER_SANITIZE_STRING);
        if ($dashboardIdent) {
            return $dashboardIdent;
        }

        $adminMetadata = $this->objAdminMetadata();
        if (!isset($adminMetadata['default_collection_dashboard'])) {
            throw new Exception(
                sprintf(
                    'No default collection dashboard defined in admin metadata for %s.',
                    get_class($this->proto())
                )
            );
        }

        return $adminMetadata['default_collection_dashboard'];
    }

    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    private function objAdminMetadata()
    {
        $obj = $this->proto();

        $objMetadata = $obj->metadata();

        $adminMetadata = isset($objMetadata['admin']) ? $objMetadata['admin'] : [];

        return $adminMetadata;
    }

    /**
     * @throws Exception If the dashboard config can not be loaded.
     * @return array
     */
    private function objCollectionDashboardConfig()
    {
        $adminMetadata = $this->objAdminMetadata();

        $dashboardIdent = $this->dashboardIdent();
        if (!$dashboardIdent) {
            $dashboardIdent = $this->metadataDashboardIdent();
        }

        if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
            throw new Exception(
                'Dashboard config is not defined.'
            );
        }

        $dashboardConfig = $adminMetadata['dashboards'][$dashboardIdent];

        return $dashboardConfig;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        if (isset($this->title)) {
            return $this->title;
        }

        try {
            $config = $this->objCollectionDashboardConfig();

            if (isset($config['title'])) {
                $this->title = $this->translator()->translation($config['title']);
                return $this->title;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $model    = $this->proto();
        $metadata = $model->metadata();
        $objLabel = null;

        if (!$objLabel && isset($metadata['admin']['lists'])) {
            $adminMetadata = $metadata['admin'];
            $listIdent     = filter_input(INPUT_GET, 'collection_ident', FILTER_SANITIZE_STRING);

            if ($listIdent === false || $listIdent === null || $listIdent === '') {
                $listIdent = (isset($adminMetadata['default_list']) ? $adminMetadata['default_list'] : '');
            }

            if (isset($adminMetadata['lists'][$listIdent]['label'])) {
                $objLabel = $this->translator()->translation($adminMetadata['lists'][$listIdent]['label']);
            }
        }

        if (!$objLabel && isset($metadata['labels']['all_items'])) {
            $objLabel = $this->translator()->translation($metadata['labels']['all_items']);
        }

        if (!$objLabel) {
            $objType = (isset($metadata['labels']['name']) ? $this->translator()->translation($metadata['labels']['name']) : null);

            $objLabel = $this->translator()->translation([
                'en' => 'List: {{objType}}',
                'fr' => 'Liste : {{objType}}'
            ]);

            if ($objType) {
                $objLabel = sprintf(str_replace('{{objType}}', '%s', $objLabel), $objType);
            }
        }

        if ($model && $model->view()) {
            $this->title = $model->render((string)$objLabel, $model);
        } else {
            $this->title = (string)$objLabel;
        }

        return $this->title;
    }
}
