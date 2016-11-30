<?php

namespace Charcoal\Admin\Tests\Action\Object;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\Object\ExportAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class ExportActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginAction
     */
    private $obj;

    private $container;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->container();
        $this->obj = new ExportAction([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     *
     */
    public function testAuthRequiredIsTrue()
    {
        $this->assertTrue($this->obj->authRequired());
    }

    /**
     *
     */
    public function testRunWithoutObjTypeIs404()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    /**
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseUrl($container);
            $containerProvider->registerAdminConfig($container);
            $containerProvider->registerAuthenticator($container);
            $containerProvider->registerAuthorizer($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
