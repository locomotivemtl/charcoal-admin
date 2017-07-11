<?php

namespace Charcoal\Object;

use InvalidArgumentException;

use DateTime;
use DateTimeInterface;

trait TimestampableTrait
{
    /**
     * Object creation date (set automatically on save)
     * @var DateTimeInterface
     */
    private $created;

    /**
     * Object last modified date (set automatically on save and update)
     * @var DateTimeInterface
     */
    private $lastModified;

    /**
     * @param \DateTimeInterface|string|null $created The object's creation timestamp.
     * @throws InvalidArgumentException If the provided date/time is invalid.
     * @return self
     */
    public function setCreated($created)
    {
        if ($created === null) {
            $this->created = null;
            return $this;
        }
        if (is_string($created)) {
            $created = new DateTime($created);
        }
        if (!($created instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Created" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->created = $created;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface|string|null $lastModified The object's last modified timestamp.
     * @throws InvalidArgumentException If the provided date/time is invalid.
     * @return self
     */
    public function setLastModified($lastModified)
    {
        if ($lastModified === null) {
            $this->lastModified = null;
            return $this;
        }
        if (is_string($lastModified)) {
            $lastModified = new DateTime($lastModified);
        }
        if (!($lastModified instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Last Modified" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function lastModified()
    {
        return $this->lastModified;
    }
}
