<?php

namespace Charcoal\Object;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Content Interface, based on charcoal/model/model-interface.
 */
interface ContentInterface extends ModelInterface
{
    /**
     * @param boolean $active The active flag.
     * @return Content Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function getActive();

    /**
     * @param integer $position The position index.
     * @return Content Chainable
     */
    public function setPosition($position);

    /**
     * @return integer
     */
    public function getPosition();
}
