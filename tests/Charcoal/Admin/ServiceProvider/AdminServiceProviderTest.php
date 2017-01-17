<?php

namespace Charcoal\Tests\Admin\ServiceProvider;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\Admin\ServiceProvider\AdminServiceProvider;

/**
 *
 */
class AdminServiceProviderTest extends PHPUnit_Framework_TestCase
{
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
