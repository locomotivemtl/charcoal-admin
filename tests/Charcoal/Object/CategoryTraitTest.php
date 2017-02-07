<?php

namespace Charcoal\Object\Tests;

// From 'charcoal-object'
use Charcoal\Object\CategoryTrait;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class CategoryTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var CategoryTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(CategoryTrait::class);
    }

    public function testUnsetCategoryItemTypeThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->categoryItemType();
    }

    public function testSetCategoryItemType()
    {
        $ret = $this->obj->setCategoryItemType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->categoryItemType());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setCategoryItemType(false);
    }

    public function testNumCategoryItems()
    {
        $this->assertEquals(0, $this->obj->numCategoryItems());

        $this->obj->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([1]));

        $this->assertEquals(1, $this->obj->numCategoryItems());
    }

    public function testHasCategoryItems()
    {
        $this->assertFalse($this->obj->hasCategoryItems());

        $this->obj->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([1]));

        $this->assertTrue($this->obj->hasCategoryItems());
    }
}
