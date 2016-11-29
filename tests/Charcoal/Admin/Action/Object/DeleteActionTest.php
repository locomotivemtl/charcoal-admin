<?php

namespace Charcoal\Admin\Tests\Action\Object;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\Object\DeleteAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class DeleteActionTest extends PHPUnit_Framework_TestCase
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
        $this->obj = new DeleteAction([
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
     *
     */
    public function testRunWithoutObjIdIs404()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    /**
     *
     */
    public function testRunWithInvalidObject()
    {
        $objId = 'bar';
        $user = $this->createUser($objId);
        $this->assertTrue($this->userExists($objId));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id=Invalid'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);

        $this->assertTrue($this->userExists($objId));
    }

    /**
     *
     */
    public function testRunWithObjectDelete()
    {
        $objId = 'foo';
        $user = $this->createUser($objId);
        $this->assertTrue($this->userExists($objId));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id='.$objId
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(200, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertTrue($res['success']);

        $this->assertFalse($this->userExists($objId));
    }

    /**
     * @return User
     */
    private function createUser($username, $password = 'password', $email = 'info@example.com')
    {
        // Create User Table
        $container = $this->container();

        $userProto = $container['model/factory']->create(User::class);
        $userProto->setData([
            'username'  => $username,
            'password'  => $password,
            'email'     => $email
        ]);
        $userProto->save();
        return $userProto;
    }

    private function userExists($username)
    {
        $container = $this->container();

        $userProto = $container['model/factory']->create(User::class);
        $userProto->load($username);

        return !!$userProto->id();
    }

    /**
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminConfig($container);
            $containerProvider->registerAuthenticator($container);
            $containerProvider->registerAuthorizer($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
