<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\UserData;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class UserDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var UserData
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

        $this->obj = $container['model/factory']->create(UserData::class);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf(UserData::class, $obj);

        $this->assertSame(null, $obj->ip());
        $this->assertSame(null, $obj->lang());
        $this->assertSame(null, $obj->ts());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'ip'=>'192.168.1.1',
            'lang'=>'fr',
            'ts'=>'2015-01-01 15:05:20'
            ]
        );
        $this->assertSame($ret, $obj);
        $this->assertEquals(ip2long('192.168.1.1'), $obj->ip());
        $this->assertEquals('fr', $obj->lang());
        $expected = new DateTime('2015-01-01 15:05:20');
        $this->assertEquals($expected, $obj->ts());
    }

    public function testSetIp()
    {
        $obj = $this->obj;
        $ret = $obj->setIp('1.1.1.1');
        $this->assertSame($ret, $obj);
        $this->assertEquals(ip2long('1.1.1.1'), $obj->ip());

        $obj->setIp(2349255);
        $this->assertEquals(2349255, $obj->ip());
    }

    public function testSetLang()
    {
        $obj = $this->obj;
        $ret = $obj->setLang('en');
        $this->assertSame($ret, $obj);
        $this->assertEquals('en', $obj->lang());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLang(false);
    }

    public function testSetTs()
    {
        $obj = $this->obj;
        $ret = $obj->setTs('July 1st, 2014');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('July 1st, 2014');
        $this->assertEquals($expected, $obj->ts());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setTs(false);
    }

    public function testPreSave()
    {
        $obj = $this->obj
        ;
        $this->assertSame(null, $obj->ip());
        $this->assertSame(null, $obj->origin());
        $this->assertSame(null, $obj->lang());
        $this->assertSame(null, $obj->ts());

        $obj->preSave();

        $this->assertSame(null, $obj->ip());
        $this->assertSame($obj->resolveOrigin(), $obj->origin());
        $this->assertSame(null, $obj->lang());
        $this->assertNotSame(null, $obj->ts());
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
