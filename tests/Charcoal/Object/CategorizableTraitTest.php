<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Object\CategorizableTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class CategorizableTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var CategorizableTrait
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(CategorizableTrait::class);
    }

    /**
     * @return void
     */
    public function testSetCategoryType()
    {
        $obj = $this->obj;
        $this->assertNull($obj->getCategoryType());

        $ret = $obj->setCategoryType('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->getCategoryType());

        $this->expectException('\InvalidArgumentException');
        $obj->setCategoryType(false);
    }
}
