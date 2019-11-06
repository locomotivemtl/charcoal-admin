<?php

namespace Charcoal\Admin\Template\Object;

use Exception;

// From psr-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Object Create Template
 */
class CreateTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    /**
     * @param RequestInterface $request PSR-7 HTTP Server Request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        $ret = parent::init($request);

        if ($this->obj()->id()) {
            $path = str_replace('object/create', 'object/edit', $request->getUri()->getPath());
            header('Location: '.(string)$request->getUri()->withPath($path));
            die();
        }
        return $ret;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type',
            'obj_id'
        ], parent::validDataFromRequest());
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
                $objType  = $this->objType();
                $metadata = $obj->metadata();

                if (!$title && isset($metadata['admin']['forms'])) {
                    $adminMetadata = $metadata['admin'];

                    $formIdent = filter_input(INPUT_GET, 'form_ident', FILTER_SANITIZE_STRING);
                    if (!$formIdent) {
                        if (isset($adminMetadata['defaultForm'])) {
                            $fomIdent = $adminMetadata['defaultForm'];
                        } elseif (isset($adminMetadata['default_form'])) {
                            $formIdent = $adminMetadata['default_form'];
                        } else {
                            $formIdent = '';
                        }
                    }

                    if (isset($adminMetadata['forms'][$formIdent]['label'])) {
                        $title = $translator->translation($adminMetadata['forms'][$formIdent]['label']);
                    }
                }

                if (!$title && isset($metadata['labels']['new_item'])) {
                    $title = $translator->translation($metadata['labels']['new_item']);
                }

                if (!$title && isset($metadata['labels']['new_model'])) {
                    $title = $translator->translation($metadata['labels']['new_model']);
                }


                if (!$title) {
                    $objType = (isset($metadata['labels']['singular_name'])
                        ? $translator->translation($metadata['labels']['singular_name'])
                        : null);

                    if (!empty($_GET['clone_id'])) {
                        $title = sprintf(
                            $translator->translation('Create: {{ objType }} from ID ""%s""'),
                            $_GET['clone_id']
                        );
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

            $this->title = $title;
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

            $this->subtitle = $title;
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
            if (isset($adminMetadata['defaultCreateDashboard'])) {
                $dashboardIdent = $adminMetadata['defaultCreateDashboard'];
            } elseif (isset($adminMetadata['default_create_dashboard'])) {
                 $dashboardIdent = $adminMetadata['default_create_dashboard'];
            } else {
                throw new Exception(sprintf(
                    'Can not show object creation dashboard: No default create dashboard defined in admin metadata for %s',
                    get_class($this->obj())
                ));
            }
        }

        if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
            throw new Exception(
                sprintf('Can not show object creation dashboard: Dashboard config is not defined for "%s".', $dashboardIdent)
            );
        }

        $dashboardConfig = $adminMetadata['dashboards'][$dashboardIdent];

        return $dashboardConfig;
    }


    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    protected function objAdminMetadata()
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
