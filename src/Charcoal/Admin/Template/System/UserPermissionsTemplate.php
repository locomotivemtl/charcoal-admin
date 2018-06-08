<?php

namespace Charcoal\Admin\Template\System;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\User;

/**
 * List Admin User Permissions
 */
class UserPermissionsTemplate extends AdminTemplate implements
    CollectionContainerInterface,
    DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

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
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type'
        ], parent::validDataFromRequest());
    }

    /**
     * @return void
     */
    private function createObjTable()
    {
        $obj = $this->modelFactory()->create('charcoal/admin/user/permission');
        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $msg = $this->translator()->translate('Database table created for "{{ objType }}".', [
                '{{ objType }}' => $obj->objType()
            ]);
            $this->addFeedback(
                'notice',
                '<span class="fa fa-asterisk" aria-hidden="true"></span><span>&nbsp; '.$msg.'</span>'
            );
        }
    }

    /**
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        return $this->translator()->translation('Administrator Permissions');
    }

    /**
     * @return mixed
     */
    public function createDashboardConfig()
    {
        return [
            'layout' => [
                'structure' => [
                    [ 'columns' => [ 0 ] ]
                ]
            ],
            'widgets' => [
                'list' => [
                    'type'     => 'charcoal/admin/widget/table',
                    'obj_type' => 'charcoal/admin/user/permission'
                ]
            ]
        ];
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required collection dependencies
        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);

        // Required dashboard dependencies.
        $this->setDashboardBuilder($container['dashboard/builder']);
    }
}
