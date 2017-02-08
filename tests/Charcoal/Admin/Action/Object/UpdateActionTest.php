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
use \Charcoal\Admin\Action\Object\UpdateAction;
use \Charcoal\Admin\User;

use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class UpdateActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var UpdateAction
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
        $this->obj = new UpdateAction([
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

            $this->container = $container;
        }

        return $this->container;
    }
}
