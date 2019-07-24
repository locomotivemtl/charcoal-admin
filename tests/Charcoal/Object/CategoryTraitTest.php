<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Object\CategoryTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class CategoryTraitTest extends AbstractTestCase
{
    /**
     * Set up the test.
     *
     * @return CategoryTrait
     */
    public function createTrait()
    {
        return $this->getMockForTrait(CategoryTrait::class);
    }

    /**
     * @return void
     */
    public function testUnsetCategoryItemTypeThrowsException()
    {
        $mock = $this->createTrait();

        $this->expectException('\Exception');
        $mock->getCategoryItemType();
    }

    /**
     * @return void
     */
    public function testSetCategoryItemType()
    {
        $mock = $this->createTrait();

        $ret = $mock->setCategoryItemType('foobar');
        $this->assertSame($ret, $mock);
        $this->assertEquals('foobar', $mock->getCategoryItemType());

        $this->expectException('\InvalidArgumentException');
        $mock->setCategoryItemType(false);
    }

    /**
     * @return void
     */
    public function testNumCategoryItems()
    {
        $mock = $this->createTrait();
        $mock->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([]));

        $this->assertEquals(0, $mock->numCategoryItems());

        $mock = $this->createTrait();
        $mock->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([ 'item' ]));

        $this->assertEquals(1, $mock->numCategoryItems());
    }

    /**
     * @return void
     */
    public function testHasCategoryItems()
    {
        $mock = $this->createTrait();
        $mock->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([]));

        $this->assertFalse($mock->hasCategoryItems());

        $mock = $this->createTrait();
        $mock->expects($this->any())
            ->method('loadCategoryItems')
            ->will($this->returnValue([ 'item' ]));

        $this->assertTrue($mock->hasCategoryItems());
    }
}
