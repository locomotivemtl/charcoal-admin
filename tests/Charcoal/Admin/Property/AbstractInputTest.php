<?php

namespace Charcoal\Tests\Admin\Property;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AbstractInputTest extends AbstractTestCase
{
    /**
     * @var AbstractPropertyInput
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerLogger($container);
        $containerProvider->registerMetadataLoader($container);

        $this->obj = $this->getMockForAbstractClass(AbstractPropertyInput::class, [
            [
                'logger' => $container['logger'],
                'metadata_loader' => $container['metadata/loader']
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'ident'=>'foo',
            'required'=>true,
            'disabled'=>true,
            'readOnly'=>true
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());
        $this->assertTrue($obj->required());
        $this->assertTrue($obj->disabled());
        $this->assertTrue($obj->readOnly());
    }
}
