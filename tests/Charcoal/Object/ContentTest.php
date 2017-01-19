<?php

namespace Charcoal\Tests\Object;

use \PHPUnit_Framework_TestCase;

use \DateTime;

use \Psr\Log\NullLogger;

use \Charcoal\Model\Service\MetadataLoader;

use \Charcoal\Object\Content;

/**
 *
 */
class ContentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $metadataLoader = new MetadataLoader([
            'logger' => new NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'config' => $GLOBALS['container']['config'],
            'cache'  => $GLOBALS['container']['cache']
        ]);

        $logger = new NullLogger();
        $this->obj = new Content([
            'logger'=>$logger,
            'metadata_loader' => $metadataLoader
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'active'=>false,
            'position'=>42,
            'created'=>'2015-01-01 13:05:45',
            'created_by'=>'Me',
            'last_modified'=>'2015-04-01 22:10:30',
            'lastModified_by'=>'You'
            ]
        );
        $this->assertSame($ret, $obj);
        $this->assertNotTrue($obj->active());
        $this->assertEquals(42, $obj->position());
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->created());
        $this->assertEquals('Me', $obj->createdBy());
        $expected = new DateTime('2015-04-01 22:10:30');
        $this->assertEquals($expected, $obj->lastModified());
        $this->assertEquals('You', $obj->lastModifiedBy());
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
        $obj = $this->obj;
        $this->assertEquals(0, $this->obj->position());
        $ret = $obj->setPosition(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->position());

        $this->obj['position'] = 3;
        $this->assertEquals(3, $this->obj->position());

        $this->obj->set('position', 1);
        $this->assertEquals(1, $this->obj['position']);

        $this->setExpectedException('\InvalidArgumentException');
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

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setCreated(false);
    }

    public function testSetCreatedInvalidDate()
    {
        $this->setExpectedException('\Exception');
        $this->obj->setCreated('foo.bar');
    }

    public function testSetCreatedBy()
    {
        $obj = $this->obj;
        $ret = $obj->setCreatedBy('Me');
        $this->assertSame($ret, $obj);
        $this->assertEquals('Me', $obj->createdBy());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setCreatedBy(false);
    }

    public function testSetLastModified()
    {
        $obj = $this->obj;
        $ret = $obj->setLastModified('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->lastModified());

        $this->obj['last_modified'] = 'today';
        $this->assertEquals(new DateTime('today'), $this->obj->lastModified());

        $this->obj->set('last_modified', 'tomorrow');
        $this->assertEquals(new DateTime('tomorrow'), $this->obj['last_modified']);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLastModified(false);
    }

    public function testSetLastModifiedInvalidDate()
    {
        $this->setExpectedException('\Exception');
        $this->obj->setLastModified('foo.bar');
    }

    public function testSetLastModifiedBy()
    {
        $obj = $this->obj;
        $ret = $obj->setLastModifiedBy('Me');
        $this->assertSame($ret, $obj);
        $this->assertEquals('Me', $obj->lastModifiedBy());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setLastModifiedBy(false);
    }

    public function testSetPreSave()
    {
        $obj = $this->obj;
        $this->assertSame(null, $obj->created());
        $this->assertSame(null, $obj->lastModified());

        $obj->preSave();
        $this->assertNotSame(null, $obj->created());
        $this->assertNotSame(null, $obj->lastModified());
    }

    // public function testSetPreUpdate()
    // {
    //     $obj = $this->obj;
    //     $this->assertSame(null, $obj->lastModified());

    //     $obj->preUpdate();
    //     $this->assertNotSame(null, $obj->lastModified());

    // }
}
