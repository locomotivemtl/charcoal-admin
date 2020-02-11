<?php

namespace Charcoal\Tests\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextareaInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class TextareaInputTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var TextareaInput
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new TextareaInput([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
        ]);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'cols'=>42,
            'rows'=>84
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());
        $this->assertEquals(84, $obj->rows());
    }

    /**
     * @return void
     */
    public function testSetCols()
    {
        $obj = $this->obj;
        $ret = $obj->setCols(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());

        $this->expectException('\InvalidArgumentException');
        $obj->setCols('foo');
    }

    /**
     * @return void
     */
    public function testSetRows()
    {
        $obj = $this->obj;
        $ret = $obj->setRows(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->rows());

        $this->expectException('\InvalidArgumentException');
        $obj->setRows('foo');
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
            $containerProvider->registerInputDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
