<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\UserData;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class UserDataTest extends AbstractTestCase
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
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(UserData::class);
    }

    /**
     * @return void
     */
    public function testDefaults()
    {
        $this->assertNull($this->obj['ip']);
        $this->assertNull($this->obj['lang']);
        $this->assertNull($this->obj['ts']);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $ret = $this->obj->setData([
            'ip'=>'192.168.1.1',
            'lang'=>'fr',
            'ts'=>'2015-01-01 15:05:20'
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(ip2long('192.168.1.1'), $this->obj['ip']);
        $this->assertEquals('fr', $this->obj['lang']);
        $expected = new DateTime('2015-01-01 15:05:20');
        $this->assertEquals($expected, $this->obj['ts']);
    }

    /**
     * @return void
     */
    public function testSetIp()
    {
        $this->obj = $this->obj;
        $ret = $this->obj->setIp('1.1.1.1');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(ip2long('1.1.1.1'), $this->obj['ip']);

        $this->obj->setIp(2349255);
        $this->assertEquals(2349255, $this->obj['ip']);
    }

    /**
     * @return void
     */
    public function testSetLang()
    {
        $this->obj = $this->obj;
        $ret = $this->obj->setLang('en');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('en', $this->obj['lang']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setLang(false);
    }

    /**
     * @return void
     */
    public function testSetTs()
    {
        $this->obj = $this->obj;
        $ret = $this->obj->setTs('July 1st, 2014');
        $this->assertSame($ret, $this->obj);
        $expected = new DateTime('July 1st, 2014');
        $this->assertEquals($expected, $this->obj['ts']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setTs(false);
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
