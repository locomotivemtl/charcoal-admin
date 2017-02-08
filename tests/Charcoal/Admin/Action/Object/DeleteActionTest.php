<?php

namespace Charcoal\Admin\Tests\Action\Object;

// From PHPUnit
use \PHPUnit_Framework_TestCase;

// From Pimple
use \Pimple\Container;

// From Slim
use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

// From 'charcoal-admin'
use \Charcoal\Admin\Action\Object\DeleteAction;

use \Charcoal\Admin\Tests\ContainerProvider;
use \Charcoal\Admin\Tests\Mock\UserProviderTrait;

/**
 *
 */
class DeleteActionTest extends PHPUnit_Framework_TestCase
{
    use UserProviderTrait;

    /**
     * Tested Class.
     *
     * @var DeleteAction
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->container();
        $this->obj = new DeleteAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsTrue()
    {
        $this->assertTrue($this->obj->authRequired());
    }

    public function testRunWithoutObjTypeIs400()
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutObjIdIs400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithInvalidObject()
    {
        $objId = 'foobar';
        $user  = $this->createUser($objId);
        $this->assertTrue($this->userExists($objId));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id=bazqux'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);

        $this->assertTrue($this->userExists($objId));
    }

    public function testRunWithObjectDelete()
    {
        $objId = 'foobar';
        $user = $this->createUser($objId);
        $this->assertTrue($this->userExists($objId));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id='.$objId
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertTrue($results['success']);

        $this->assertFalse($this->userExists($objId));
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminServices($container);
            $containerProvider->registerCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
