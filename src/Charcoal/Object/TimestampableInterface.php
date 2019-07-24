<?php

namespace Charcoal\Object;

/**
 *
 */
interface TimestampableInterface
{
    /**
     * Set the object creation timestamp.
     *
     * Note that this should typically be called (automatically) just before saving an object in storage.
     *
     * @param \DateTimeInterface|string|null $created The created date.
     * @return self
     */
    public function setCreated($created);

    /**
     * Retrieve the object creation timestamp (or null if none was set).
     *
     * @return \DateTimeInterface|null
     */
    public function getCreated();

    /**
     * Set the object last modification timestamp.
     *
     * Note that this should typically be called (automatically) just before updating (and saving) an object in storage.
     *
     * @param \DateTimeInterface|string|null $lastModified The last modified date.
     * @return self
     */
    public function setLastModified($lastModified);

    /**
     * Retrieve the object last modification timestamp (or null if none was set).
     *
     * @return \DateTimeInterface|null
     */
    public function getLastModified();
}
