<?php

namespace Charcoal\Object\Tests;

// From 'charcoal-object'
use Charcoal\Object\ArchivableTrait;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class ArchivableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var ArchivableTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(ArchivableTrait::class);
    }

    public function testConstructor()
    {
        $this->assertTrue(true);
    }
}
