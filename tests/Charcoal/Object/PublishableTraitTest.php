<?php

namespace Charcoal\Tests\Object;

use DateTime;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\PublishableTrait;
use Charcoal\Object\PublishableInterface as Publishable;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;
use Charcoal\Tests\Object\Mocks\PublishableClass as PublishableObject;

/**
 *
 */
class PublishableTraitTest extends AbstractTestCase
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
     *
     * @return void
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
     *
     * @return void
     */
    public function testPublishDate()
    {
        $obj  = $this->obj;
        $time = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setPublishDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($time, $obj->getPublishDate());

        $obj->setPublishDate('');
        $this->assertNull($obj->getPublishDate());

        $obj->setPublishDate($time);
        $this->assertEquals($time, $obj->getPublishDate());
    }

    /**
     * @return void
     */
    public function testUnexpectedPublishDate()
    {
        $obj = $this->obj;

        $this->expectException(UnexpectedValueException::class);
        $obj->setPublishDate('foobar');
    }

    /**
     * @return void
     */
    public function testInvalidPublishDate()
    {
        $obj = $this->obj;

        $this->expectException(InvalidArgumentException::class);
        $obj->setPublishDate(false);
    }

    /**
     * Assert that the `setExpiryDate` method:
     * - is chainable
     * - accepts a string representation of a date/time value
     * - accepts a {@see \DateTimeInterface}
     * - accepts an blank value
     *
     * @return void
     */
    public function testExpiryDate()
    {
        $obj  = $this->obj;
        $time = new DateTime('2015-01-01 00:00:00');

        $ret = $obj->setExpiryDate('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals($time, $obj->getExpiryDate());

        $obj->setExpiryDate('');
        $this->assertNull($obj->getExpiryDate());

        $obj->setExpiryDate($time);
        $this->assertEquals($time, $obj->getExpiryDate());
    }

    /**
     * @return void
     */
    public function testUnexpectedExpiryDate()
    {
        $obj = $this->obj;

        $this->expectException(UnexpectedValueException::class);
        $obj->setExpiryDate('foobar');
    }

    /**
     * @return void
     */
    public function testInvalidExpiryDate()
    {
        $obj = $this->obj;

        $this->expectException(InvalidArgumentException::class);
        $obj->setExpiryDate(false);
    }

    /**
     * @return void
     */
    public function testPublishStatus()
    {
        $obj = $this->obj;

        $obj->setPublishStatus(Publishable::STATUS_DRAFT);
        $this->assertEquals(Publishable::STATUS_DRAFT, $obj->getPublishStatus());

        $obj->setPublishStatus(Publishable::STATUS_PENDING);
        $this->assertEquals(Publishable::STATUS_PENDING, $obj->getPublishStatus());

        $obj->setPublishStatus(Publishable::STATUS_PUBLISHED);
        $this->assertEquals(Publishable::STATUS_PUBLISHED, $obj->getPublishStatus());

        $obj->setPublishStatus(Publishable::STATUS_UPCOMING);
        $this->assertEquals(Publishable::STATUS_PUBLISHED, $obj->getPublishStatus());

        $obj->setPublishStatus(Publishable::STATUS_EXPIRED);
        $this->assertEquals(Publishable::STATUS_PUBLISHED, $obj->getPublishStatus());

        $obj->setPublishStatus('');
        $this->assertNull($obj->getPublishStatus());

        $this->expectException(InvalidArgumentException::class);
        $obj->setPublishStatus('foobar');
    }

    /**
     * @dataProvider providerPublishStatus
     *
     * @param  mixed  $publishDate    A date/time value.
     * @param  mixed  $expiryDate     A date/time value.
     * @param  string $expectedStatus The expected publication status.
     * @return void
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

        $obj->setPublishStatus(null);
        $this->assertNull($obj->publishDateStatus());

        $obj->setPublishStatus(Publishable::STATUS_DRAFT);
        $this->assertEquals(Publishable::STATUS_DRAFT, $obj->publishDateStatus());

        $obj->setPublishStatus(Publishable::STATUS_PENDING);
        $this->assertEquals(Publishable::STATUS_PENDING, $obj->publishDateStatus());

        $obj->setPublishStatus(Publishable::STATUS_PUBLISHED);
        $this->assertEquals($expectedStatus, $obj->publishDateStatus());
    }

    /**
     * @return array
     */
    public function providerPublishStatus()
    {
        return [
            [ null, null, Publishable::STATUS_PUBLISHED ],
            [ 'yesterday', 'tomorrow', Publishable::STATUS_PUBLISHED ],
            [ '2 days ago', 'yesterday', Publishable::STATUS_EXPIRED ],
            [ 'tomorrow', '+1 week', Publishable::STATUS_UPCOMING ],
            [ 'tomorrow', null, Publishable::STATUS_UPCOMING ],
            [ null, 'tomorrow', Publishable::STATUS_PUBLISHED ],
            [ null, 'yesterday', Publishable::STATUS_EXPIRED ]
        ];
    }

    /**
     * @return void
     */
    public function testIsPublished()
    {
        $obj = $this->obj;

        $obj->setPublishDate(null);
        $obj->setExpiryDate(null);
        $obj->setPublishStatus(null);
        $this->assertFalse($obj->isPublished());

        $obj->setPublishStatus(Publishable::STATUS_DRAFT);
        $this->assertFalse($obj->isPublished());

        $obj->setPublishStatus(Publishable::STATUS_PUBLISHED);
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
