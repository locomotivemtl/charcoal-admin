<?php

namespace Charcoal\Admin\Template\System;

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
use Charcoal\Admin\User;

/**
 *
 */
class UserPermissionsTemplate extends AdminTemplate implements
    CollectionContainerInterface,
    DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

    /**
     * @param Container $container Pimple DI Container.
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
        $obj = $this->modelFactory()->create('charcoal/admin/user/permission');
        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $this->addFeedback('success', 'A new table was created for object.');
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
            'layout'=>[
                'structure'=>[[
                    'columns' => [0]
                ]]
            ],
            'widgets'=>[
                'list'=>[
                    'type'=>'charcoal/support/admin/widget/table',
                    'obj_type'=>'charcoal/admin/user/permission'
                ]
            ]
        ];
    }
}
