<?php

namespace Charcoal\Tests\Admin\Script\User;

use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-admin'
use Charcoal\Admin\Script\User\CreateScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class CreateScriptTest extends AbstractTestCase
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

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);
        return $container;
    }

    /**
     * @return void
     */
    public function setUp()
    {
        $this->container = $this->getContainer();

        $this->obj = new CreateScript([
            'logger'        => $this->container['logger'],
            'climate'       => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],
            'container'     => $this->container,
        ]);
    }

    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    /**
     * @return void
     */
    public function testArguments()
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    /**
     * @return integer
     */
    private function numAdminUsersInSource()
    {
        $source = $this->container['model/factory']->create('charcoal/admin/user')->source();
        $source->createTable();

        $table = $source->table();
        $q = 'select count(`email`) as num from `'.$table.'`';
        $req = $this->container['database']->query($q);
        return $req->fetchColumn(0);
    }

    // public function testInvoke()
    // {
    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $obj = $this->obj;
    //     $ret = $obj($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());
    // }

    // public function testInvokeWithArguments()
    // {
    //     global $argv;

    //     $argv = [];
    //     $argv[] = 'vendor/bin/charcoal';

    //     $argv[] = '-e';
    //     $argv[] = 'foo@example.com';

    //     $argv[] = '-p';
    //     $argv[] = '[Foo]{bar}123';

    //     $argv[] = '-r';
    //     $argv[] = 'admin';

    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $obj = $this->obj;
    //     $ret = $obj($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());

    //     $created = $this->container['model/factory']->create('charcoal/admin/user')->load('foo');
    //     $this->assertEquals('foo@example.com', $created['email']);
    //     $this->assertEquals(['admin'], $created['roles']);
    // }

    // public function testRun()
    // {
    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $ret = $this->obj->run($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());
    // }
}
