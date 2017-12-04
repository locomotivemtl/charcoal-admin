<?php

namespace Charcoal\Object\Tests;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalTrait;
use Charcoal\Object\Tests\ContainerProvider;
use Charcoal\Object\Tests\Mocks\HierarchicalClass as HierarchicalObject;

class HierarchicalTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var Hierarchical
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = new HierarchicalObject();
    }

    public function testSetMaster()
    {
        $obj = $this->obj;
        $master = $this->createMock(get_class($obj));
        $ret = $obj->setMaster($master);
        $this->assertSame($ret, $obj);
        $this->assertSame($master, $obj->master());

        $this->expectException('\InvalidArgumentException');
        $obj->setMaster(['foobar']);
    }

    public function testHasMaster()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasMaster());

        $master = $this->createMock(get_class($obj));
        $obj->setMaster($master);
        $this->assertTrue($obj->hasMaster());
    }

    public function testIsTopLevel()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isTopLevel());

        $master = $this->createMock(get_class($obj));
        $obj->setMaster($master);
        $this->assertFalse($obj->isTopLevel());
    }

    public function testIsLastLevel()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isLastLevel());

        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertFalse($obj->isLastLevel());
    }

    public function testHierarchyLevel()
    {
        $obj = $this->obj;
        $this->assertEquals(1, $obj->hierarchyLevel());

        $master = $this->createMock(get_class($obj));
        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setMaster($master);
        $obj->setChildren($children);
        $this->assertEquals(2, $obj->hierarchyLevel());

        $master2 = $this->createMock(get_class($obj));
        $obj->master()->setMaster($master2);

        //$this->assertEquals(3, $obj->hierarchyLevel());
    }

    public function testToplevelMaster()
    {
        $obj = $this->obj;
        $this->assertSame(null, $obj->toplevelMaster());

        $master1 = $this->createMock(get_class($obj));
        $master2 = $this->createMock(get_class($obj));

        $obj->setMaster($master1);
        $this->assertSame($master1, $obj->toplevelMaster());

        $master1->setMaster($master2);
        //$this->assertSame($master2, $obj->toplevelMaster());
    }

    public function testHierarchy()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->hierarchy());

        $master1 = $this->createMock(get_class($obj));
        $master2 = $this->createMock(get_class($obj));

        $obj->setMaster($master1);
        $this->assertSame([$master1], $obj->hierarchy());

        $master1->setMaster($master2);
        //$this->assertSame([$master1, $master2], $obj->hierarchy());
    }

    public function testInvertedHierarchy()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->invertedHierarchy());

        $master1 = $this->createMock(get_class($obj));
        $master2 = $this->createMock(get_class($obj));

        $obj->setMaster($master1);
        $this->assertSame([$master1], $obj->invertedHierarchy());

        $master1->setMaster($master2);
        //$this->assertSame([$master2, $master1], $obj->invertedHierarchy());
    }

    public function testIsMasterOf()
    {
        $obj = $this->obj;
        $master = $this->createMock(get_class($obj));

        //$this->assertFalse($master->isMasterOf($obj));
        $obj->setMaster($master);
        //$this->assertTrue($master->isMasterOf($obj));
        //$this->assertFalse($obj->isMasterOf($master));
    }

    public function testHasChildren()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertTrue($obj->hasChildren());
    }

    public function testNumChildren()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());


        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertEquals(4, $obj->numChildren());

        $child5 = $this->createMock(get_class($obj));
        $obj->addChild($child5);
        $this->assertEquals(5, $obj->numChildren());
    }

    public function testIsChildOf()
    {
        $obj = $this->obj;
        $master = $this->createMock(get_class($obj));

        $this->assertFalse($obj->isChildOf($master));
        $obj->setMaster($master);
        $this->assertTrue($obj->isChildOf($master));
    }
}
