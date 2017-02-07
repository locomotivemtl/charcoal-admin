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
    public function active();

    /**
     * @param integer $position The position index.
     * @return Content Chainable
     */
    public function setPosition($position);

    /**
     * @return integer
     */
    public function position();

    /**
     * @param \DateTimeInterface|string|null $created The created date.
     * @return Content Chainable
     */
    public function setCreated($created);

    /**
     * @return \DateTimeInterface|null
     */
    public function created();

    /**
     * @param mixed $createdBy The author, at object creation.
     * @return Content Chainable
     */
    public function setCreatedBy($createdBy);

    /**
     * @return mixed
     */
    public function createdBy();

    /**
     * @param \DateTimeInterface|string|null $lastModified The last modified date.
     * @return Content Chainable
     */
    public function setLastModified($lastModified);

    /**
     * @return \DateTimeInterface|null
     */
    public function lastModified();

    /**
     * @param mixed $lastModifiedBy The author, at object modificaition (update).
     * @return Content Chainable
     */
    public function setLastModifiedBy($lastModifiedBy);

    /**
     * @return mixed
     */
    public function lastModifiedBy();
}
