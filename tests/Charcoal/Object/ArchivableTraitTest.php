<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Object\ArchivableTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class ArchivableTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var ArchivableTrait
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(ArchivableTrait::class);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertTrue(true);
    }
}
