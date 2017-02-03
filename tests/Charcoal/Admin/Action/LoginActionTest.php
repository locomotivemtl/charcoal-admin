<?php

namespace Charcoal\Admin\Tests\Action;

use \PHPUnit_Framework_TestCase;


use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\LoginAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class LoginActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginAction
     */
    private $obj;

    /**
     * @var Container
     */
    private $container;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->container();
        $this->obj = new LoginAction([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsFalse()
    {
        $this->assertFalse($this->obj->authRequired());
    }

    /**
     * Assert that
     */
    public function testRunWithoutParamsIs404()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());
    }

    /**
     * Assert that
     */
    public function testRunWithInvalidCredentials()
    {
        $this->createUser('foo', 'foobar');

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'username=test&password=test123'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertFalse($this->obj->success());
        $this->assertEquals(403, $res->getStatusCode());
    }

    private function createUser($username, $password, $email = 'info@example.com')
    {
        $container = $this->container();
        $userProto = $container['model/factory']->create(User::class);
        $userProto->setData([
            'username'  => $username,
            'password'  => $password,
            'email'     => $email
        ]);
        $userProto->save();
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
            $containerProvider->registerCollectionLoader($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
