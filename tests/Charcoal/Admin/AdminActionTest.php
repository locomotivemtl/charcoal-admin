<?php

namespace Charcoal\Tests\Admin;

use ReflectionClass;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AdminActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

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
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $this->getMockForAbstractClass(AdminAction::class, [[
            'logger'    => $container['logger'],
            'container' => $container
        ]]);
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
     *
     * @return void
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

    /**
     * @return void
     */
    public function testFeedback()
    {
        $this->assertFalse($this->obj->hasFeedbacks());
        $this->assertEquals([], $this->obj->feedbacks());
        $this->assertEquals(0, $this->obj->numFeedbacks());

        $entryId = $this->obj->addFeedback('error', 'Message');
        $entries = $this->obj->feedbacks();
        $entry   = reset($entries);

        $this->assertArraySubset([ 'id'      => $entryId  ], $entry);
        $this->assertArraySubset([ 'type'    => 'danger'  ], $entry);
        $this->assertArraySubset([ 'level'   => 'error'   ], $entry);
        $this->assertArraySubset([ 'message' => 'Message' ], $entry);

        $this->assertTrue($this->obj->hasFeedbacks());
        $this->assertEquals(1, $this->obj->numFeedbacks());
    }

    /**
     * @return void
     */
    public function testAdminUrl()
    {
        $this->assertEquals('/admin/', $this->obj->adminUrl());
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
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerActionDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
