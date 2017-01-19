<?php

namespace Charcoal\Tests\Object;

/**
 *
 */
class CategorizableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     * Create mock object from trait.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\Object\CategorizableTrait');
    }

    public function testSetCategoryType()
    {
        $obj = $this->obj;
        $this->assertNull($obj->categoryType());

        $ret = $obj->setCategoryType('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->categoryType());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setCategoryType(false);
    }
}
