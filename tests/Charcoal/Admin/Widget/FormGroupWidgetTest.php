<?php

namespace Charcoal\Admin\Tests\Widget;

use PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use Pimple\Container;

use \Charcoal\Admin\Widget\FormGroupWidget;

use Charcoal\Admin\Tests\ContainerProvider;

class FormGroupWidgetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
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

    public function testConstructor()
    {
        $this->assertInstanceOf(FormGroupWidget::class, $this->obj);
    }
}
