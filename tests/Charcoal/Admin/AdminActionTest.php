<?php

namespace Charcoal\Admin\Tests;

use ReflectionClass;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class AdminActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var AdminAction
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

        $this->obj = $this->getMockForAbstractClass(AdminAction::class, [[
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader']
        ]]);
        $this->obj->setDependencies($container);

        $this->obj->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([]);
    }

    public function testInit()
    {
        $request = $this->getMock(RequestInterface::class);
        //$this->obj->init($request);
    }

    /**
     * Asserts that success behaves as expected.
     * (Actually test base admin action).
     * - false by default
     * - setSuccess is chainable
     * - setSuccess can be called with non-boolean (0 or 1, for example) values
     * - success can be set by ArrayAccess
     * - success can be set with get()
     * - success can be accessed by ArrayAccess
     */
    public function testSuccess()
    {
        $this->assertFalse($this->obj->success());
        $ret = $this->obj->setSuccess(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->success());

        $this->obj->setSuccess(0);
        $this->assertFalse($this->obj->success());

        $this->obj['success'] = true;
        $this->assertTrue($this->obj->success());

        $this->obj->set('success', false);
        $this->assertFalse($this->obj['success']);
    }

    public function testFeedback()
    {
        $this->assertFalse($this->obj->hasFeedbacks());
        $this->assertEquals([], $this->obj->feedbacks());
        $this->assertEquals(0, $this->obj->numFeedbacks());

        $ret = $this->obj->addFeedback('error', 'Message');
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->hasFeedbacks());
        $this->assertEquals([[
            'level'   => 'error',
            'msg'     => 'Message',
            'message' => 'Message'
        ]], $this->obj->feedbacks());
        $this->assertEquals(1, $this->obj->numFeedbacks());
    }

    public function testAdminUrl()
    {
        $this->assertEquals('/admin/', $this->obj->adminUrl());
    }

    public function testBaseUrl()
    {
        $this->assertEquals('/', $this->obj->baseUrl());
        $ret = $this->obj->setBaseUrl('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar/', $this->obj->baseUrl());
    }

    public function testAuthRequiredIsTrue()
    {
        $foo = self::getMethod($this->obj, 'authRequired');
        $res = $foo->invoke($this->obj);
        $this->assertTrue($res);
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
