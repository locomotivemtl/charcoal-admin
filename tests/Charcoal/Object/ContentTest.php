<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\Content;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class ContentTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var Content
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
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(Content::class);
    }

    public function testDefaults()
    {
        $this->assertTrue($this->obj->active());
        $this->assertEquals(0, $this->obj->position());
        $this->assertNull($this->obj->created());
        $this->assertNull($this->obj->createdBy());
        $this->assertNull($this->obj->lastModified());
        $this->assertNull($this->obj->lastModifiedBy());
        $this->assertEquals([], $this->obj->requiredAclPermissions());
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'active'          => false,
            'position'        => 42,
            'created'         => '2015-01-01 13:05:45',
            'created_by'      => 'Me',
            'last_modified'   => '2015-04-01 22:10:30',
            'lastModified_by' => 'You',
            'required_acl_permissions' => ['foo', 'bar']
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertNotTrue($this->obj->active());
        $this->assertEquals(42, $this->obj->position());
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $this->obj->created());
        $this->assertEquals('Me', $this->obj->createdBy());
        $expected = new DateTime('2015-04-01 22:10:30');
        $this->assertEquals($expected, $this->obj->lastModified());
        $this->assertEquals('You', $this->obj->lastModifiedBy());
        $this->assertEquals(['foo', 'bar'], $this->obj->requiredAclPermissions());
    }

    public function testSetActive()
    {
        $this->assertTrue($this->obj->active());
        $ret = $this->obj->setActive(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->active());

        $this->obj->setActive(1);
        $this->assertTrue($this->obj->active());

        $this->obj['active'] = false;
        $this->assertFalse($this->obj->active());

        $this->obj->set('active', true);
        $this->assertTrue($this->obj['active']);
    }

    public function testSetPosition()
    {
        $this->obj = $this->obj;
        $this->assertEquals(0, $this->obj->position());
        $ret = $this->obj->setPosition(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->position());

        $this->obj['position'] = '3';
        $this->assertEquals(3, $this->obj->position());

        $this->obj->set('position', 1);
        $this->assertEquals(1, $this->obj['position']);

        $this->obj->setPosition(null);
        $this->assertEquals(0, $this->obj->position());

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->obj->setPosition('foo');
    }

    public function testSetCreated()
    {
        $ret = $this->obj->setCreated('2015-01-01 13:05:45');
        $this->assertSame($ret, $this->obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $this->obj->created());

        $this->obj['created'] = 'today';
        $this->assertEquals(new DateTime('today'), $this->obj->created());

        $this->obj->set('created', 'tomorrow');
        $this->assertEquals(new DateTime('tomorrow'), $this->obj['created']);

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->obj->setCreated(false);
    }

    public function testSetCreatedInvalidDate()
    {
        $this->setExpectedException('\Exception');
        $this->obj->setCreated('foo.bar');
    }

    public function testSetCreatedBy()
    {
        $ret = $this->obj->setCreatedBy('Me');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Me', $this->obj->createdBy());

        //$this->setExpectedException(\InvalidArgumentException::class);
        //$this->obj->setCreatedBy(false);
    }

    public function testSetLastModified()
    {
        $ret = $this->obj->setLastModified('2015-01-01 13:05:45');
        $this->assertSame($ret, $this->obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $this->obj->lastModified());

        $this->obj['last_modified'] = 'today';
        $this->assertEquals(new DateTime('today'), $this->obj->lastModified());

        $this->obj->set('last_modified', 'tomorrow');
        $this->assertEquals(new DateTime('tomorrow'), $this->obj['last_modified']);

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->obj->setLastModified(false);
    }

    public function testSetLastModifiedInvalidDate()
    {
        $this->setExpectedException('\Exception');
        $this->obj->setLastModified('foo.bar');
    }

    public function testSetLastModifiedBy()
    {
        $ret = $this->obj->setLastModifiedBy('Me');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Me', $this->obj->lastModifiedBy());

        //$this->setExpectedException(\InvalidArgumentException::class);
        //$this->obj->setLastModifiedBy(false);
    }

    public function testSetRequiredAclPermissions()
    {
        $ret = $this->obj->setRequiredAclPermissions(['a', 'b', 'c']);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(['a', 'b', 'c'], $this->obj['required_acl_permissions']);

        $this->obj->setRequiredAclPermissions('foo, bar');
        $this->assertEquals(['foo', 'bar'], $this->obj->requiredAclPermissions());

        $this->obj->setRequiredAclPermissions(null);
        $this->assertEquals([], $this->obj->requiredAclPermissions());
        
        $this->obj->setRequiredAclPermissions(false);
        $this->assertEquals([], $this->obj->requiredAclPermissions());

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->obj->setRequiredAclPermissions(true);
    }

    public function testSetPreSave()
    {
        $this->assertSame(null, $this->obj->created());
        $this->assertSame(null, $this->obj->lastModified());

        $this->obj->preSave();
        $this->assertNotSame(null, $this->obj->created());
        $this->assertNotSame(null, $this->obj->lastModified());
    }

    public function testSetPreUpdate()
    {
         $this->assertSame(null, $this->obj->lastModified());

         $this->obj->preUpdate();
         $this->assertNotSame(null, $this->obj->lastModified());

    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
