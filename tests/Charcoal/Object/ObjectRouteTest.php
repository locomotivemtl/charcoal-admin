<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class ObjectRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var ObjectRoute
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

        $this->obj = $container['model/factory']->create(ObjectRoute::class);
    }

    public function testDefaults()
    {
        $this->assertNull($this->obj->id());
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'id' => 42,
            'creation_date' => 'today',
            'last_modification_date' => 'today',
            'lang' => 'es',
            'slug' => 'foobar',
            'route_obj_type' => 'foo',
            'route_obj_id' => 3,
            'route_template' => 'baz'
        ]);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(42, $this->obj->id());
        $this->assertEquals(new DateTime('today'), $this->obj->creationDate());
        $this->assertEquals(new DateTime('today'), $this->obj->lastModificationDate());
        $this->assertEquals('es', $this->obj->lang());
        $this->assertEquals('foobar', $this->obj->slug());
        $this->assertEquals('foo', $this->obj->routeObjType());
        $this->assertEquals(3, $this->obj->routeObjId());
        $this->assertEquals('baz', $this->obj->routeTemplate());
    }

    public function testSetId()
    {
        $ret = $this->obj->setId(3);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(3, $this->obj->id());

        $this->obj['id'] = 42;
        $this->assertEquals(42, $this->obj->id());

        $this->obj->set('id', 10);
        $this->assertEquals(10, $this->obj['id']);
    }

    public function testSetCreationDate()
    {
        $this->assertNull($this->obj->creationDate());
    }

    public function testLastModificationDate()
    {
    }

    public function testLang()
    {
    }

    public function testSetSlug()
    {
        $this->assertNull($this->obj->slug());
        $ret = $this->obj->setSlug('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->slug());

        $this->obj['slug'] = 'foobar';
        $this->assertEquals('foobar', $this->obj->slug());

        $this->obj->set('slug', 'bar');
        $this->assertEquals('bar', $this->obj->slug());

        $this->obj['slug'] = null;
        $this->assertNull($this->obj->slug());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setSlug(false);
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
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);
            $containerProvider->registerModelCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
