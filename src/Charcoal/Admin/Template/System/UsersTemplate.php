<?php

namespace Charcoal\Admin\Template\System;

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
 * List Admin Users
 */
class UsersTemplate extends AdminTemplate implements
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
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        return $this->translator()->translation('Administrators');
    }

    /**
     * @return mixed
     */
    public function createDashboardConfig()
    {
        return [
            'layout' => [
                'structure' => [
                    [
                        'columns' => [ 0 ]
                    ]
                ]
            ],
            'widgets' => [
                'list' => [
                    'type'     => 'charcoal/support/admin/widget/table',
                    'obj_type' => 'charcoal/admin/user'
                ]
            ]
        ];
    }
}
