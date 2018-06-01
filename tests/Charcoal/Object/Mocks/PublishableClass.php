<?php

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\PublishableInterface;
use Charcoal\Object\PublishableTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class PublishableClass extends AbstractModel implements
    PublishableInterface
{
    use PublishableTrait;

    /**
     * @return string
     */
    public function objType()
    {
        return 'charcoal/tests/object/publishable-class';
    }
}
