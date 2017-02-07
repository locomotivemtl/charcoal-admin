<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevision;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class ObjectRevisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var ObjectRevision
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

        $this->obj = $container['model/factory']->create(ObjectRevision::class);
    }

    public function testSetObjType()
    {
        $this->assertNull($this->obj->targetType());
        $ret = $this->obj->setTargetType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->targetType());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTargetType(false);
    }

    public function testSetObjId()
    {
        $this->assertNull($this->obj->targetId());
        $ret = $this->obj->setTargetId(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->targetId());
    }

    public function testSetRevNum()
    {
        $this->assertNull($this->obj->revNum());
        $ret = $this->obj->setRevNum(66);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(66, $this->obj->revNum());

        $this->obj->setRevNum('42');
        $this->assertEquals(42, $this->obj->revNum());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRevNum([]);
    }

    public function testSetRevTs()
    {
        $obj = $this->obj;
        $this->assertNull($obj->revTs());
        $ret = $obj->setRevTs('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->revTs());

        $obj->setRevTs(null);
        $this->assertNull($obj->revTs());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setRevTs(false);
    }

    public function testSetRevUser()
    {
        $this->assertNull($this->obj->revUser());
        $ret = $this->obj->setRevUser('me');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('me', $this->obj->revUser());

        $this->obj->setRevUser(null);
        $this->assertNull($this->obj->revUser());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRevUser(false);
    }

    public function testSetDataPrev()
    {
        $this->assertNull($this->obj->dataPrev());
        $ret = $this->obj->setDataPrev(['foo'=>1]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>1], $this->obj->dataPrev());

        $this->assertEquals(['bar'], $this->obj->setDataPrev('["bar"]')->dataPrev());
        $this->assertEquals([], $this->obj->setDataPrev(null)->dataPrev());
    }

    public function testSetDataObj()
    {
        $this->assertNull($this->obj->dataObj());
        $ret = $this->obj->setDataObj(['foo'=>1]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>1], $this->obj->dataObj());

        $this->assertEquals(['bar'], $this->obj->setDataObj('["bar"]')->dataObj());
        $this->assertEquals([], $this->obj->setDataObj(null)->dataObj());
    }

    public function testSetDataDiff()
    {
        $this->assertNull($this->obj->dataDiff());
        $ret = $this->obj->setDataDiff(['foo'=>1]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>1], $this->obj->dataDiff());

        $this->assertEquals(['bar'], $this->obj->setDataDiff('["bar"]')->dataDiff());
        $this->assertEquals([], $this->obj->setDataDiff(null)->dataDiff());
    }

    public function testCreateDiff()
    {
        $this->assertEquals([], $this->obj->createDiff([], []));
        $ret = $this->obj->createDiff(['foo'=>1], ['foo'=>2]);
        $this->assertEquals([['foo'=>1],['foo'=>2]], $ret);

        $ret = $this->obj->createDiff(['foo'=>1], ['foo'=>1]);
        $this->assertEquals([], $ret);


        $this->obj->setDataPrev(['foo'=>1, 'bar'=>1, 'baz'=>1]);
        $this->obj->setDataObj(['foo'=>1, 'bar'=>42]);
        $ret = $this->obj->createDiff();

        $this->assertEquals([['bar'=>1, 'baz'=>1], ['bar'=>42]], $ret);
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

            $this->container = $container;
        }

        return $this->container;
    }
}
