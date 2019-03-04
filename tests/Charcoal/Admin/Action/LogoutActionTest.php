<?php

namespace Charcoal\Tests\Admin\Action;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\LogoutAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class LogoutActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     *
     * @var LogoutAction
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
     *
     * @return void
     */
    public function setUp()
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();
        $this->obj = new LogoutAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsTrue()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     * @return void
     */
    public function testRunWithUnauthenticatedUser()
    {
        $this->createUser('foo@bar.com');

        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithAuthenticatedUser()
    {
        $user = $this->createUser('foo@bar.com');
        $user->login();

        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertTrue($results['success']);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
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
