<?php

namespace Charcoal\Admin\Tests\Action;

// From PHPUnit
use PHPUnit_Framework_TestCase;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\LogoutAction;

use Charcoal\Admin\Tests\ContainerProvider;
use Charcoal\Admin\Tests\Mock\UserProviderTrait;

/**
 *
 */
class LogoutActionTest extends PHPUnit_Framework_TestCase
{
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

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testAuthRequiredIsTrue()
    {
        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertTrue($res);
    }

    public function testRunWithUnauthenticatedUser()
    {
        $this->createUser('foo');

        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithAuthenticatedUser()
    {
        $user = $this->createUser('foo');
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
