<?php

namespace Charcoal\Tests\Admin\ServiceProvider;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\Admin\ServiceProvider\AclServiceProvider;

/**
 *
 */
class AclServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new AclServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['admin/acl']));
    }
}
