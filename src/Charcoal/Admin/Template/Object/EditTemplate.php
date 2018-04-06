<?php

namespace Charcoal\Admin\Template\Object;

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
class EditTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type', 'obj_id'
        ], parent::validDataFromRequest());
    }

    /**
     * Retrieve the sidemenu.
     *
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        if ($this->sidemenu === null) {
            $dashboardConfig = $this->dashboardConfig();

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
            $dashboardConfig = $this->dashboardConfig();

            if (isset($dashboardConfig['sidemenu'])) {
                $this->headerMenu = $this->createHeaderMenu($dashboardConfig['sidemenu']);
            } else {
                $this->headerMenu = $this->createHeaderMenu();
            }
        }

        return $this->headerMenu;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        if ($this->title === null) {
            $title = null;

            $translator = $this->translator();

            try {
                $config = $this->dashboardConfig();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                $config = [];
            }

            if (isset($config['title'])) {
                $title = $translator->translation($config['title']);
            } else {
                $obj      = $this->obj();
                $objId    = $this->objId();
                $objType  = $this->objType();
                $metadata = $obj->metadata();

                if (!$title && isset($metadata['admin']['forms'])) {
                    $adminMetadata = $metadata['admin'];

                    $formIdent = filter_input(INPUT_GET, 'form_ident', FILTER_SANITIZE_STRING);
                    if (!$formIdent) {
                        $formIdent = (isset($adminMetadata['default_form']) ? $adminMetadata['default_form'] : '');
                    }

                    if (isset($adminMetadata['forms'][$formIdent]['label'])) {
                        $title = $translator->translation($adminMetadata['forms'][$formIdent]['label']);
                    }
                }

                if ($objId) {
                    if (!$title && isset($metadata['labels']['edit_item'])) {
                        $title = $translator->translation($metadata['labels']['edit_item']);
                    }

                    if (!$title && isset($metadata['labels']['edit_model'])) {
                        $title = $translator->translation($metadata['labels']['edit_model']);
                    }
                } else {
                    if (!$title && isset($metadata['labels']['new_item'])) {
                        $title = $translator->translation($metadata['labels']['new_item']);
                    }

                    if (!$title && isset($metadata['labels']['new_model'])) {
                        $title = $translator->translation($metadata['labels']['new_model']);
                    }
                }

                if (!$title) {
                    $objType = (isset($metadata['labels']['singular_name'])
                                ? $translator->translation($metadata['labels']['singular_name'])
                                : null);

                    if ($objId) {
                        $title = $translator->translation('Edit: {{ objType }} #{{ id }}');
                    } else {
                        $title = $translator->translation('Create: {{ objType }}');
                    }

                    if ($objType) {
                        $title = strtr($title, [
                            '{{ objType }}' => $objType
                        ]);
                    }
                }
            }

            $this->title = $this->renderTitle($title);
        }

        return $this->title;
    }

    /**
     * Retrieve the page's sub-title.
     *
     * @return Translation|string|null
     */
    public function subtitle()
    {
        if ($this->subtitle === null) {
            try {
                $config = $this->dashboardConfig();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                $config = [];
            }

            if (isset($config['subtitle'])) {
                $title = $this->translator()->translation($config['subtitle']);
            } else {
                $title = '';
            }

            $this->subtitle = $this->renderTitle($title);
        }

        return $this->subtitle;
    }

    /**
     * @param Container $container DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);

        // Required dependencies.
        $this->dashboardBuilder = $container['dashboard/builder'];
    }

    /**
     * @throws Exception If the object's dashboard config can not be loaded.
     * @return array
     */
    protected function createDashboardConfig()
    {
        $adminMetadata  = $this->objAdminMetadata();
        $dashboardIdent = $this->dashboardIdent();

        if (empty($dashboardIdent)) {
            $dashboardIdent = filter_input(INPUT_GET, 'dashboard_ident', FILTER_SANITIZE_STRING);
        }

        if (empty($dashboardIdent)) {
            if (!$this->objId()) {
                if (!isset($adminMetadata['default_create_dashboard'])) {
                    throw new Exception(sprintf(
                        'No default create dashboard defined in admin metadata for %s',
                        get_class($this->obj())
                    ));
                }

                $dashboardIdent = $adminMetadata['default_create_dashboard'];
            } else {
                if (!isset($adminMetadata['default_edit_dashboard'])) {
                    throw new Exception(sprintf(
                        'No default edit dashboard defined in admin metadata for %s',
                        get_class($this->obj())
                    ));
                }

                $dashboardIdent = $adminMetadata['default_edit_dashboard'];
            }
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
     * Retrieve the page's sub-title.
     *
     * @param  mixed $title The title to render.
     * @return string|null
     */
    protected function renderTitle($title)
    {
        $obj = $this->obj();
        if ($obj->view()) {
            return $obj->render((string)$title, $obj);
        } else {
            return (string)$title;
        }
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
}
