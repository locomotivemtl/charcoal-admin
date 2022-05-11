<?php

namespace Charcoal\Tests\Admin;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AdminTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     *
     * @var AdminTemplate
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
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new AdminTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $this->assertNull($this->obj->ident());
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($this->obj, $ret);
        $this->assertEquals('foobar', $this->obj->ident());
    }

    /**
     * @return void
     */
    public function testSetLabel()
    {
        $this->assertNull($this->obj->label());
        $ret = $this->obj->setLabel('foobar');
        $this->assertSame($this->obj, $ret);
        $this->assertEquals('foobar', (string)$this->obj->label());
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
            $containerProvider->registerTemplateDependencies($container);

            $container['widget/factory'] = $this->createMock('\Charcoal\Factory\FactoryInterface');

            $this->container = $container;
        }

        return $this->container;
    }
}
