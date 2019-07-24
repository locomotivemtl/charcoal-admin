<?php

namespace Charcoal\Object;

/**
 *
 */
interface AuthorableInterface
{
    /**
     * Set the object creator (author at creation time).
     *
     * @param mixed $createdBy The object author, at creation time.
     * @return self
     */
    public function setCreatedBy($createdBy);

    /**
     * Retrieve the object creator (author at creation time).
     *
     * @return mixed
     */
    public function getCreatedBy();

    /**
     * Set the object last editor (author at the last modification time).
     *
     * @param mixed $lastModifiedBy The object author, at last modification time.
     * @return self
     */
    public function setLastModifiedBy($lastModifiedBy);

    /**
     * Retrieve the object last editor (author at the last modification time).
     *
     * @return mixed
     */
    public function getLastModifiedBy();
}
