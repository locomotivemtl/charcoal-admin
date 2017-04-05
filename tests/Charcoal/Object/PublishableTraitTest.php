<?php

namespace Charcoal\Object\Tests;

use DateTime;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\PublishableTrait;
use Charcoal\Object\Tests\ContainerProvider;
use Charcoal\Object\Tests\Mocks\PublishableClass as PublishableObject;

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

        $this->obj = new PublishableObject([
            'factory'    => $container['model/factory'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Assert that the `setPublishDate` method:
     * - is chainable
     * - accepts a string representation of a date/time value
     * - accepts a {@see \DateTimeInterface}
     * - accepts an blank value
     */
    public function testPublishDate()
    {
        $obj  = $this->obj;
        $time = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setPublishDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($time, $obj->publishDate());

        $obj->setPublishDate('');
        $this->assertEquals(null, $obj->publishDate());

        $obj->setPublishDate($time);
        $this->assertEquals($time, $obj->publishDate());
    }

    public function testUnexpectedPublishDate()
    {
        $obj = $this->obj;

        $this->setExpectedException(UnexpectedValueException::class);
        $obj->setPublishDate('foobar');
    }

    public function testInvalidPublishDate()
    {
        $obj = $this->obj;

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setPublishDate(false);
    }

    /**
     * Assert that the `setExpiryDate` method:
     * - is chainable
     * - accepts a string representation of a date/time value
     * - accepts a {@see \DateTimeInterface}
     * - accepts an blank value
     */
    public function testExpiryDate()
    {
        $obj  = $this->obj;
        $time = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setExpiryDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($time, $obj->expiryDate());

        $obj->setExpiryDate('');
        $this->assertEquals(null, $obj->expiryDate());

        $obj->setExpiryDate($time);
        $this->assertEquals($time, $obj->expiryDate());
    }

    public function testUnexpectedExpiryDate()
    {
        $obj = $this->obj;

        $this->setExpectedException(UnexpectedValueException::class);
        $obj->setExpiryDate('foobar');
    }

    public function testInvalidExpiryDate()
    {
        $obj = $this->obj;

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setExpiryDate(false);
    }

    public function testPublishStatus()
    {
        $obj = $this->obj;
        $obj->setPublishStatus('draft');
        $this->assertEquals('draft', $obj->publishStatus());

        $obj->setPublishStatus('pending');
        $this->assertEquals('pending', $obj->publishStatus());

        $obj->setPublishStatus('published');
        $this->assertEquals('published', $obj->publishStatus());

        $obj->setPublishStatus('upcoming');
        $this->assertEquals('published', $obj->publishStatus());

        $obj->setPublishStatus('expired');
        $this->assertEquals('published', $obj->publishStatus());

        $obj->setPublishDate(null);
        $obj->setExpiryDate(null);
        $obj->setPublishStatus('');
        $this->assertEquals(null, $obj->publishStatus());

        $this->setExpectedException(InvalidArgumentException::class);
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
            [ 'tomorrow', '+1 week', 'upcoming' ],
            [ 'tomorrow', null, 'upcoming' ],
            [ null, 'tomorrow', 'published' ],
            [ null, 'yesterday', 'expired' ]
        ];
    }

    public function testIsPublished()
    {
        $obj = $this->obj;

        $obj->setPublishDate(null);
        $obj->setExpiryDate(null);
        $obj->setPublishStatus(null);
        $this->assertFalse($obj->isPublished());

        $obj->setPublishStatus('draft');
        $this->assertFalse($obj->isPublished());

        $obj->setPublishStatus('published');
        $this->assertTrue($obj->isPublished());

        $obj->setPublishDate('tomorrow');
        $this->assertFalse($obj->isPublished());

        $obj->setExpiryDate('yesterday');
        $this->assertFalse($obj->isPublished());
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
            $containerProvider->registerModelCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
