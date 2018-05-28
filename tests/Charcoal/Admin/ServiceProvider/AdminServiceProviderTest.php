<?php

namespace Charcoal\Tests\Admin\ServiceProvider;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\ServiceProvider\AdminServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AdminServiceProviderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new AdminServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['admin/config']));
        $this->assertTrue(isset($container['elfinder/config']));
        $this->assertTrue(isset($container['admin/authorizer']));
        $this->assertTrue(isset($container['admin/authenticator']));
    }
}
