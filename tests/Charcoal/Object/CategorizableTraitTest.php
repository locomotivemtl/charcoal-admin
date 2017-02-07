<?php

namespace Charcoal\Object\Tests;

// From 'charcoal-object'
use Charcoal\Object\CategorizableTrait;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class CategorizableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var CategorizableTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(CategorizableTrait::class);
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
