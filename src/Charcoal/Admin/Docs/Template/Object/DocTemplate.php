<?php

namespace Charcoal\Admin\Docs\Template\Object;

use Exception;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-ui'
use Charcoal\Ui\DashboardBuilder;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;
use Charcoal\Admin\Widget\SidemenuWidget;

/**
 * Object Edit Template
 */
class DocTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

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
     * @return array
     */
    protected function createDashboardConfig()
    {
        return $this->objDocDashboardConfig();
    }

    /**
     * Retrieve the sidemenu.
     *
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        if ($this->sidemenu === null) {
            $dashboardConfig = $this->objDocDashboardConfig();

            if (isset($dashboardConfig['sidemenu'])) {
                $this->sidemenu = $this->createSidemenu($dashboardConfig['sidemenu']);
            } else {
                $this->sidemenu = $this->createSidemenu();
            }
        }

        return $this->sidemenu;
    }

    /**
     * Retrieve the header menu.
     *
     * @return array
     */
    public function headerMenu()
    {
        if ($this->headerMenu === null) {
            $dashboardConfig = $this->objDocDashboardConfig();

            if (isset($dashboardConfig['sidemenu'])) {
                $this->headerMenu = $this->createHeaderMenu($dashboardConfig['sidemenu']);
            } else {
                $this->headerMenu = $this->createHeaderMenu();
            }
        }

        return $this->headerMenu;
    }

    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    private function objAdminMetadata()
    {
        $obj = $this->obj();

        $objMetadata = $obj->metadata();

        $adminMetadata = isset($objMetadata['admin']) ? $objMetadata['admin'] : null;
        if ($adminMetadata === null) {
            throw new Exception(sprintf(
                'The object %s does not have an admin metadata.',
                get_class($obj)
            ));
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
    private function objDocDashboardConfig()
    {
        $adminMetadata = $this->objAdminMetadata();
        $dashboardIdent = $this->dashboardIdent();

        if (empty($dashboardIdent)) {
            $dashboardIdent = filter_input(INPUT_GET, 'dashboard_ident', FILTER_SANITIZE_STRING);
        }

        if (empty($dashboardIdent)) {
            if (isset($adminMetadata['default_doc_dashboard'])) {
                $dashboardIdent = $adminMetadata['default_doc_dashboard'];
            }
        }

        $overrideType = false;

        if (empty($dashboardIdent)) {
            if (!isset($adminMetadata['default_edit_dashboard'])) {
                throw new Exception(sprintf(
                    'No default doc dashboard defined in admin metadata for %s',
                    get_class($this->obj())
                ));
            }
            $overrideType = true;
            $dashboardIdent = $adminMetadata['default_edit_dashboard'];
        }

        if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
            throw new Exception(
                'Dashboard config is not defined.'
            );
        }

        $dashboardConfig = $adminMetadata['dashboards'][$dashboardIdent];

        if ($overrideType) {
            $widgets = $dashboardConfig['widgets'];
            foreach ($widgets as $ident => $widget) {
                $dashboardConfig['widgets'][$ident]['type'] = 'charcoal/admin/widget/doc';
                $dashboardConfig['widgets'][$ident]['show_header'] = true;
                $dashboardConfig['widgets'][$ident]['show_title'] = true;
            }
        }

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
            $config = $this->objDocDashboardConfig();

            if (isset($config['title'])) {
                $this->title = $this->translator()->translation($config['title']);

                return $this->title;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $obj = $this->obj();
        $metadata = $obj->metadata();
        $objLabel = null;

        if (!$objLabel && isset($metadata['admin']['forms'])) {
            $adminMetadata = $metadata['admin'];

            $formIdent = filter_input(INPUT_GET, 'form_ident', FILTER_SANITIZE_STRING);
            if (!$formIdent) {
                $formIdent = (isset($adminMetadata['default_form']) ? $adminMetadata['default_form'] : '');
            }

            if (isset($adminMetadata['forms'][$formIdent]['label'])) {
                $objLabel = $this->translator()->translation($adminMetadata['forms'][$formIdent]['label']);
            }
        }

        if (!$objLabel) {
            $objType = (isset($metadata['labels']['singular_name']) ?
                $this->translator()->translation($metadata['labels']['singular_name']) : null);

            $objLabel = $this->translator()->translation('Documentation: {{ objType }}');

            if ($objType) {
                $objLabel = strtr($objLabel, [
                    '{{ objType }}' => $objType
                ]);
            }
        }

        if ($obj->view()) {
            $this->title = $obj->render((string)$objLabel, $obj);
        } else {
            $this->title = (string)$objLabel;
        }

        return $this->title;
    }
}
