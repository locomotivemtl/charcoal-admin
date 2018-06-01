<?php

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\RoutableInterface;
use Charcoal\Object\RoutableTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class RoutableClass extends AbstractModel implements
    RoutableInterface
{
    use RoutableTrait;

    /**
     * @return string
     */
    public function objType()
    {
        return 'charcoal/tests/object/routable-class';
    }

    /**
     * @return null
     */
    public function templateIdent()
    {
        return null;
    }
}
