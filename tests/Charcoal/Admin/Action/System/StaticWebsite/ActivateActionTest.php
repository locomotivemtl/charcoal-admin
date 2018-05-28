<?php

namespace Charcoal\Tests\Admin\Action\System\StaticWebsite;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\System\StaticWebsite\ActivateAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class ActivateActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     *
     * @var ActivateAction
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
        $container = $this->container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->obj = new ActivateAction([
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
    public function testRun()
    {
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
