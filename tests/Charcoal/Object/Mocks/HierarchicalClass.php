<?php

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalInterface;
use Charcoal\Object\HierarchicalTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class HierarchicalClass extends AbstractModel implements
    HierarchicalInterface
{
    use HierarchicalTrait;

    /**
     * @return string
     */
    public function objType()
    {
        return 'charcoal/tests/object/hierarchical-class';
    }

    /**
     * @return array
     */
    public function loadChildren()
    {
        return [];
    }
}
