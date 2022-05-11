<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormGroupWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class FormGroupWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerDashboardBuilder($container);
        $containerProvider->registerAuthorizer($container);
        $containerProvider->registerAuthenticator($container);


        $container['form/input/builder'] = $this->createMock(\Charcoal\Ui\FormInput\FormInputBuilder::class, '');

        $container['authorizer'] = $container['admin/authorizer'];
        $container['authenticator'] = $container['admin/authenticator'];

        $this->obj = new FormGroupWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormGroupWidget::class, $this->obj);
    }
}
