<?php

namespace Charcoal\Object;

/**
 *
 */
trait AuthorableTrait
{
    /**
     * @var mixed
     */
    private $createdBy;

    /**
     * @var mixed
     */
    private $lastModifiedBy;

    /**
     * @param mixed $createdBy The creator of the content object.
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function createdBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $lastModifiedBy The last modification's username.
     * @return self
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function lastModifiedBy()
    {
        return $this->lastModifiedBy;
    }
}
