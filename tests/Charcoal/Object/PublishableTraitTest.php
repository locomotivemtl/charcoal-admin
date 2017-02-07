<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From 'charcoal-object'
use Charcoal\Object\PublishableTrait;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class PublishableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var PublishableTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(PublishableTrait::class);
    }

    /**
     * Assert that the `setPublishDate` method:
     * - is chainable
     * - sets the publishDate value when a string is passed
     * - sets the publishDate value when a DateTime is passed
     * - throws an InvalidArgumentException if other types of arguments are passed
     */
    public function testSetPublishDate()
    {
        $obj = $this->obj;
        $dt = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setPublishDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($dt, $obj->publishDate());

        $obj->setPublishDate($dt);
        $this->assertEquals($dt, $obj->publishDate());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPublishDate(false);
    }


    /**
     * Assert that the `setExpiryDate` method:
     * - is chainable
     * - sets the expiryDate value when a string is passed
     * - sets the expiryDate value when a DateTime is passed
     * - throws an InvalidArgumentException if other types of arguments are passed
     */
    public function testSetExpiryDate()
    {
        $obj = $this->obj;
        $dt = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setExpiryDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($dt, $obj->expiryDate());

        $obj->setExpiryDate($dt);
        $this->assertEquals($dt, $obj->expiryDate());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setExpiryDate(false);
    }

    public function testSetPublishStatus()
    {
        $obj = $this->obj;
        $obj->setPublishStatus('draft');
        $this->assertEquals('draft', $obj->publishStatus());
        $obj->setPublishStatus('pending');
        $this->assertEquals('pending', $obj->publishStatus());
        $obj->setPublishStatus('published');
        $this->assertEquals('published', $obj->publishStatus());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPublishStatus('foobar');
    }

    /**
     * @dataProvider providerPublishStatus
     */
    public function testPublishStatusFromDates($publishDate, $expiryDate, $expectedStatus)
    {
        $obj = $this->obj;
        if ($publishDate !== null) {
            $obj->setPublishDate($publishDate);
        }
        if ($expiryDate !== null) {
            $obj->setExpiryDate($expiryDate);
        }

        $obj->setPublishStatus('draft');
        $this->assertEquals('draft', $obj->publishStatus());
        $obj->setPublishStatus('pending');
        $this->assertEquals('pending', $obj->publishStatus());

        $obj->setPublishStatus('published');
        $this->assertEquals($expectedStatus, $obj->publishStatus());
    }

    public function providerPublishStatus()
    {
        return [
            [ null, null, 'published' ],
            [ 'yesterday', 'tomorrow', 'published' ],
            [ '2 days ago', 'yesterday', 'expired' ],
            [ 'tomorrow', '+1 week', 'pending' ],
            [ 'tomorrow', null, 'pending' ],
            [ null, 'tomorrow', 'published' ],
            [ null, 'yesterday', 'expired' ]
        ];
    }

    public function testIsPublished()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isPublished());

        $obj->setPublishStatus('draft');
        $this->assertFalse($obj->isPublished());

        $obj->setPublishStatus('published');
        $this->assertTrue($obj->isPublished());

        $obj->setExpiryDate('yesterday');
        $this->assertFalse($obj->isPublished());
    }
}
