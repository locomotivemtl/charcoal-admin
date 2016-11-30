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

use \Charcoal\Admin\Script\User\ResetPasswordScript;

use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class ResetPasswordScriptTest extends PHPUnit_Framework_TestCase
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
        $containerProvider = new ContainerProvider();
        $containerProvider->registerModelFactory($container);
        $containerProvider->registerClimate($container);
        return $container;
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        $this->obj = new ResetPasswordScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }

    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('username', $args);
        $this->assertArrayHasKey('password', $args);
    }

    public function testArguments()
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('username', $args);
        $this->assertArrayHasKey('password', $args);
    }

    public function testInvokeWithArguments()
    {
        global $argv;

        $argv = [];
        $argv[] = 'vendor/bin/charcoal';

        $argv[] = '--username';
        $argv[] = 'foobar';

        $argv[] = '--password';
        $argv[] = '[Foo]{bar}123';

        $model = $this->container['model/factory']->create('charcoal/admin/user');
        $source = $model->source();
        $source->createTable();

        $model->setData([
            'username' => 'foobar',
            'password' => 'BarFoo123',
            'email' => 'foobar@example.com'
        ]);
        $model->setRevisionEnabled(false);
        $model->save();

        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        $obj = $this->obj;
        $ret = $obj($request, $response);

        $this->assertSame($ret, $response);
    }
}
