<?php

namespace Charcoal\Admin\Template\Object;

use \Exception;
use \InvalidArgumentException;

// From `pimple`
use \Pimple\Container;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-ui'
use \Charcoal\Ui\DashboardBuilder;

// From 'charcoal-admin'
use \Charcoal\Admin\AdminTemplate;
use \Charcoal\Admin\Ui\DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;
use \Charcoal\Admin\Widget\SidemenuWidget;

/**
 * Object Edit Template
 */
class EditTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    /**
     * @var SideMenuWidgetInterface $sidemenu
     */
    private $sidemenu;

    /**
     * @var FactoryInterface $widgetFactory
     */
    private $widgetFactory;

    /**
     * @var DashboardBuilder $dashboardBuilder
     */
    private $dashboardBuilder;

    /**
     * @param Container $container DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);

        // Required dependencies.
        $this->setWidgetFactory($container['widget/factory']);
        $this->dashboardBuilder = $container['dashboard/builder'];
    }


    /**
     * @param FactoryInterface $factory The widget factory, to create the dashboard and sidemenu widgets.
     * @return EditTemplate Chainable
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the widget factory was not set before being accessed.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            throw new Exception(
                'Model factory not set'
            );
        }
        return $this->widgetFactory;
    }

    /**
     * @param DashboardBuilder $builder A builder to create customized Dashboard objects.
     * @return CollectionTemplate Chainable
     *
     */
    public function setDashboardBuilder(DashboardBuilder $builder)
    {
        $this->dashboardBuilder = $builder;
        return $this;
    }

    /**
     * @throws Exception If the dashboard builder dependency was not previously set / injected.
     * @return DashboardBuilder
     */
    public function dashboardBuilder()
    {
        if ($this->dashboardBuilder === null) {
            throw new Exception(
                'Dashboard builder was not set.'
            );
        }
        return $this->dashboardBuilder;
    }

    /**
     * @param array $data Optional dashboard data.
     * @return Dashboard
     * @see DashboardContainerTrait::createDashboard()
     */
    public function createDashboard(array $data = null)
    {
        unset($data);
        $dashboardConfig = $this->objEditDashboardConfig();
        $dashboard = $this->dashboardBuilder->build($dashboardConfig);
        return $dashboard;
    }

    /**
     * @return SidemenuWidgetInterface
     */
    public function sidemenu()
    {
        $dashboardConfig = $this->objEditDashboardConfig();

        if (!isset($dashboardConfig['sidemenu'])) {
            return null;
        }

        $sidemenuConfig = $dashboardConfig['sidemenu'];

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';

        if (isset($sidemenuConfig['widget_type'])) {
            $widgetType = $sidemenuConfig['widget_type'];
        } else {
            $widgetType = 'charcoal/admin/widget/sidemenu';
        }

        $sidemenu = $this->widgetFactory()->create($widgetType);

        if (isset($sidemenuConfig['widget_options'])) {
            $sidemenu->setData($sidemenuConfig['widget_options']);
        }

        return $sidemenu;
    }


    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    private function objAdminMetadata()
    {
        $obj = $this->obj();

        $objMetadata     = $obj->metadata();

        $adminMetadata = isset($objMetadata['admin']) ? $objMetadata['admin'] : null;
        if ($adminMetadata === null) {
            throw new Exception(
                sprintf(
                    'The object %s does not have an admin metadata.',
                    get_class($obj)
                )
            );
        }

        return $adminMetadata;
    }

    /**
     * Get the dashboard config for the current object.
     *
     * This method loads the "dashboard config" from the object's admin metadata.
     *
     * @throws Exception If the object's dashboard config can not be loaded.
     * @return array
     */
    private function objEditDashboardConfig()
    {
        $adminMetadata = $this->objAdminMetadata();

        $dashboardIdent  = $this->dashboardIdent();
        $dashboardConfig = $this->dashboardConfig();

        if ($dashboardIdent === false || $dashboardIdent === null || $dashboardIdent === '') {
            $dashboardIdent = filter_input(INPUT_GET, 'dashboard_ident', FILTER_SANITIZE_STRING);
        }

        if ($dashboardIdent === false || $dashboardIdent === null || $dashboardIdent === '') {
            if (!isset($adminMetadata['default_edit_dashboard'])) {
                throw new Exception(
                    sprintf(
                        'No default edit dashboard defined in admin metadata for %s',
                        get_class($this->obj())
                    )
                );
            }

            $dashboardIdent = $adminMetadata['default_edit_dashboard'];
        }

        if (empty($dashboardConfig)) {
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
     * Retrieve the title of the page.
     *
     * @return TranslationString|string|null
     */
    public function title()
    {
        if (isset($this->title)) {
            return $this->title;
        }

        try {
            $config = $this->objEditDashboardConfig();

            if (isset($config['title'])) {
                $this->title = new TranslationString($config['title']);

                return $this->title;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $obj      = $this->obj();
        $objId    = $this->objId();
        $objType  = $this->objType();
        $metadata = $obj->metadata();
        $objLabel = null;

        if (!$objLabel && isset($metadata['admin']['forms'])) {
            $adminMetadata  = $metadata['admin'];
            $formIdent = filter_input(INPUT_GET, 'form_ident', FILTER_SANITIZE_STRING);

            if ($formIdent === false || $formIdent === null || $formIdent === '') {
                $formIdent = (isset($adminMetadata['default_form']) ? $adminMetadata['default_form'] : '');
            }

            if (isset($adminMetadata['forms'][$formIdent]['label'])) {
                $objLabel = $adminMetadata['forms'][$formIdent]['label'];

                if (TranslationString::isTranslatable($objLabel)) {
                    $objLabel = new TranslationString($objLabel);
                }
            }
        }

        if (!$objLabel && isset($metadata['labels']['edit_item'])) {
            $objLabel = $metadata['labels']['edit_item'];

            if (TranslationString::isTranslatable($objLabel)) {
                $objLabel = new TranslationString($objLabel);
            }
        }

        if (!$objLabel && isset($metadata['labels']['edit_model'])) {
            $objLabel = $metadata['labels']['edit_model'];

            if (TranslationString::isTranslatable($objLabel)) {
                $objLabel = new TranslationString($objLabel);
            }
        }

        if (!$objLabel) {
            $objType = (isset($metadata['labels']['singular_name']) ? $metadata['labels']['singular_name'] : null );
            if (TranslationString::isTranslatable($objType)) {
                $objType = new TranslationString($objType);
            }

            if ($objId) {
                $objLabel = new TranslationString([
                    'en' => 'Edit: {{objType}} #{{id}}',
                    'fr' => 'Modifier : {{objType}} #{{id}}'
                ]);
            } else {
                $objLabel = new TranslationString([
                    'en' => 'Create: {{objType}}',
                    'fr' => 'Créer : {{objType}}'
                ]);
            }

            if ($objType) {
                $objLabel = sprintf(str_replace('{{objType}}', '%s', $objLabel), $objType);
            }
        }

        if ($obj && $obj->view()) {
            $this->title = $obj->render((string)$objLabel, $obj);
        } else {
            $this->title = (string)$objLabel;
        }

        return $this->title;
    }
}
