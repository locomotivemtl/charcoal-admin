<?php

namespace Charcoal\Admin\Tests\ActionObject;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\Object\UpdateAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class UpdateActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginAction
     */
    private $obj;

    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerAdminConfig($container);
        $containerProvider->registerAuthenticator($container);
        $containerProvider->registerAuthorizer($container);

        $this->obj = new UpdateAction([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

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
}
