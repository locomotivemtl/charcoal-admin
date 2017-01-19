<?php

namespace Charcoal\Tests\Object;

/**
 *
 */
class ArchivableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     * Create mock object from trait.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\Object\ArchivableTrait');
    }

    public function testConstructor()
    {
        // This unit test is a stub
        $this->assertTrue(true);
    }
}
