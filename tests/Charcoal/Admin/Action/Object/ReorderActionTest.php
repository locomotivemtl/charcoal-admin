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

// From 'charcoal-core'
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\Collection;

// From 'charcoal-admin'
use \Charcoal\Admin\Action\Object\ReorderAction;
use \Charcoal\Admin\Tests\ContainerProvider;
use \Charcoal\Admin\Tests\Mock\SortableModel as Model;

/**
 *
 */
class ReorderActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * The primary model to test with.
     *
     * @var string
     */
    private $model = Model::class;

    /**
     * Store the tested instance.
     *
     * @var ReorderAction
     */
    private $action;

    /**
     * Store the object collection loader.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

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
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->action = new ReorderAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function setUpObjects()
    {
        $container = $this->container();

        $model  = $container['model/factory']->create($this->model);
        $source = $model->source();

        if (!$source->tableExists()) {
            $source->createTable();
        }

        $objs = [
            [ 'id' => 'foo', 'position' => 1 ],
            [ 'id' => 'bar', 'position' => 2 ],
            [ 'id' => 'baz', 'position' => 3 ],
            [ 'id' => 'qux', 'position' => 4 ],
        ];
        foreach ($objs as $obj) {
            $model->setData($obj)->save();
        }

        // Test initial order from data-source.
        $objs = $this->getObjects();
        $this->assertEquals([ 'foo', 'bar', 'baz', 'qux' ], $objs->keys());

        return $objs;
    }

    public function getObjects()
    {
        if ($this->collectionLoader === null) {
            $container = $this->container();

            $loader = new CollectionLoader([
                'logger'     => $container['logger'],
                'factory'    => $container['model/factory'],
                'model'      => $this->model,
                'collection' => Collection::class
            ]);
            $loader->addOrder('position');

            $this->collectionLoader = $loader;
        }

        return $this->collectionLoader->load();
    }

    /**
     *
     */
    public function testAuthRequiredIsTrue()
    {
        $this->assertTrue($this->action->authRequired());
    }

    /**
     * @dataProvider runRequestProvider
     *
     * @param integer $status  An HTTP status code.
     * @param string  $success Whether the action was successful.
     * @param array   $mock    The request parameters to test.
     */
    public function testRun($status, $success, array $mock)
    {
        if ($status === 200) {
            $this->setUpObjects();
        }

        $request  = Request::createFromEnvironment(Environment::mock($mock));
        $response = new Response();

        $response = $this->action->run($request, $response);
        $this->assertEquals($status, $response->getStatusCode());

        $results = $this->action->results();
        $this->assertEquals($success, $results['success']);

        if ($status === 200) {
            $keys = $this->getObjects()->keys();
            $this->assertEquals([ 'baz', 'bar', 'qux', 'foo' ], $keys);
        }
    }

    public function runRequestProvider()
    {
        return [
            [ 400, false, [] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$this->model ] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$this->model.'&order_property=5' ] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$this->model.'&order_property=foobar' ] ],
            [ 500, false, [ 'QUERY_STRING' => 'obj_type='.$this->model.'&obj_orders[]=xyzzy&obj_orders[]=qwerty' ] ],
            [ 200, true,  [ 'QUERY_STRING' => 'obj_type='.$this->model.'&obj_orders[]=baz&obj_orders[]=bar&obj_orders[]=qux&obj_orders[]=foo' ] ],
        ];
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
