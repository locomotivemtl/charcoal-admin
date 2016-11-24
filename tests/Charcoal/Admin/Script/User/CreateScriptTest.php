<?php

namespace Charcoal\Admin\Tests\Script\User;

use \PDO;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Source\DatabaseSource;

use \Charcoal\Admin\Script\User\CreateScript;

/**
 *
 */
class CreateScriptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private $obj;

    private function getContainer()
    {
        $container = new Container();
        $container['logger'] = function (Container $container) {
            return new NullLogger();
        };
        $container['cache'] = function (Container $container) {
            return new VoidCachePool();
        };
        $container['database'] = function (Container $container) {
            return new PDO('sqlite::memory:');
        };
        $container['metadata/loader'] = function (Container $container) {
            return new MetadataLoader([
                'logger'    => $container['logger'],
                'cache'     => $container['cache'],
                'base_path' => realpath(__DIR__.'/../../../../../'),
                'paths'     => ['metadata']
            ]);
        };
        $container['model/factory'] = function (Container $container) {
            return new Factory([
                'arguments' => [[
                    'logger'            => $container['logger'],
                    'metadata_loader'   => $container['metadata/loader'],
                    'property_factory'  => $container['property/factory'],
                    'source_factory'    => $container['source/factory'],
                    'container'         => $container
                ]]
            ]);
        };
        $container['property/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'prefix'    => '\Charcoal\Property\\',
                    'suffix'    => 'Property'
                ],
                'arguments' => [[
                    'logger'    => $container['logger'],
                    'database'  => $container['database'],
                    'container' => $container
                ]]
            ]);
        };
        $container['source/factory'] = function (Container $container) {
            return new Factory([
                'map' => [
                    'database' => DatabaseSource::class
                ],
                'arguments' => [[
                    'logger' => $container['logger'],
                    'pdo'    => $container['database']
                ]]
            ]);
        };
        return $container;
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        $this->obj = new CreateScript([
            'logger' => $this->container['logger'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }

    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('username', $args);
        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    public function testArguments()
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('username', $args);
        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    private function numAdminUsersInSource()
    {
        $source = $this->container['model/factory']->create('charcoal/admin/user')->source();
        $source->createTable();

        $table = $source->table();
        $q = 'select count(`email`) as num from `'.$table.'`';
        $req = $this->container['database']->query($q);
        return $req->fetchColumn(0);
    }

    public function testInvoke()
    {
        // Ensure that no admin user exists in test database
        $this->assertEquals(0, $this->numAdminUsersInSource());

        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        $obj = $this->obj;
        $ret = $obj($request, $response);

        $this->assertSame($ret, $response);

        // Ensure one user was created in database
        $this->assertEquals(1, $this->numAdminUsersInSource());
    }

    public function testInvokeWithArguments()
    {
        global $argv;

        $argv = [];
        $argv[] = 'vendor/bin/charcoal';

        $argv[] = '--username';
        $argv[] = 'foo';

        $argv[] = '-e';
        $argv[] = 'foo@example.com';

        $argv[] = '-p';
        $argv[] = '[Foo]{bar}123';

        $argv[] = '-r';
        $argv[] = 'admin';

        // Ensure that no admin user exists in test database
        $this->assertEquals(0, $this->numAdminUsersInSource());

        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        $obj = $this->obj;
        $ret = $obj($request, $response);

        $this->assertSame($ret, $response);

        // Ensure one user was created in database
        $this->assertEquals(1, $this->numAdminUsersInSource());

        $created = $this->container['model/factory']->create('charcoal/admin/user')->load('foo');
        $this->assertEquals('foo@example.com', $created['email']);
        $this->assertEquals(['admin'], $created['roles']);
    }

    public function testRun()
    {
        // Ensure that no admin user exists in test database
        $this->assertEquals(0, $this->numAdminUsersInSource());

        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        $ret = $this->obj->run($request, $response);

        $this->assertSame($ret, $response);

        // Ensure one user was created in database
        $this->assertEquals(1, $this->numAdminUsersInSource());
    }
}
