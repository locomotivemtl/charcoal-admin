<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class ObjectRouteTest extends AbstractTestCase
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
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(ObjectRoute::class);
    }

    /**
     * @return void
     */
    public function testDefaults()
    {
        $this->assertNull($this->obj['id']);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $ret = $this->obj->setData([
            'id' => 42,
            'creationDate' => 'today',
            'last_modification_date' => 'today',
            'lang' => 'es',
            'slug' => 'foobar',
            'route_obj_type' => 'foo',
            'route_obj_id' => 3,
            'route_template' => 'baz'
        ]);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(42, $this->obj['id']);

        $expected = new DateTime('today');
        $this->assertEquals($expected, $this->obj->getCreationDate());
        $this->assertEquals($expected, $this->obj->getLastModificationDate());

        $this->assertEquals('es', $this->obj->getLang());
        $this->assertEquals('foobar', $this->obj->getSlug());
        $this->assertEquals('foo', $this->obj->getRouteObjType());
        $this->assertEquals(3, $this->obj->getRouteObjId());
        $this->assertEquals('baz', $this->obj->getRouteTemplate());
    }

    /**
     * @return void
     */
    public function testSetId()
    {
        $ret = $this->obj->setId(3);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(3, $this->obj['id']);

        $this->obj['id'] = 42;
        $this->assertEquals(42, $this->obj['id']);

        $this->obj->set('id', 10);
        $this->assertEquals(10, $this->obj['id']);
    }

    /**
     * @return void
     */
    public function testSetCreationDate()
    {
        $this->assertNull($this->obj->getCreationDate());
    }

    /**
     * @return void
     */
    public function testLastModificationDate()
    {
    }

    /**
     * @return void
     */
    public function testLang()
    {
    }

    /**
     * @return void
     */
    public function testSetSlug()
    {
        $this->assertNull($this->obj['slug']);
        $ret = $this->obj->setSlug('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['slug']);

        $this->obj['slug'] = 'foobar';
        $this->assertEquals('foobar', $this->obj['slug']);

        $this->obj->set('slug', 'bar');
        $this->assertEquals('bar', $this->obj['slug']);

        $this->obj['slug'] = null;
        $this->assertNull($this->obj['slug']);

        $this->expectException('\InvalidArgumentException');
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
