<?php

namespace Charcoal\Admin\Tests\Property;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\Admin\Property\AbstractPropertyInput;

use \Charcoal\Admin\Tests\ContainerProvider;

class AbstractInputTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

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

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'ident'=>'foo',
            'required'=>true,
            'disabled'=>true,
            'read_only'=>true
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());
        $this->assertTrue($obj->required());
        $this->assertTrue($obj->disabled());
        $this->assertTrue($obj->readOnly());
    }
}
