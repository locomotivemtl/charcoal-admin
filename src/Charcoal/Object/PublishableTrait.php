<?php

namespace Charcoal\Object;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use UnexpectedValueException;
use Exception;

/**
 * Publishable Object
 *
 * A full implementation, as trait, of the {@see \Charcoal\Object\PublishableInterface}.
 */
trait PublishableTrait
{
    /**
     * The publication date.
     *
     * @var DateTimeInterface $publishDate
     */
    protected $publishDate;

    /**
     * The expiration date.
     *
     * @var DateTimeInterface $expiryDate
     */
    protected $expiryDate;

    /**
     * The publication status.
     *
     * @var string|null
     */
    protected $publishStatus;

    /**
     * Set the object's publication date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @throws UnexpectedValueException If the date/time value is invalid.
     * @throws InvalidArgumentException If the value is not a date/time instance.
     * @return PublishableInterface Chainable
     */
    public function setPublishDate($time)
    {
        if ($time === null || $time === '') {
            $this->publishDate = null;
            return $this;
        }

        if (is_string($time)) {
            try {
                $time = new DateTime($time);
            } catch (Exception $e) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid Publication Date: %s',
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }

        if (!$time instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                'Publication Date must be a date/time string or an instance of DateTimeInterface'
            );
        }

        $this->publishDate = $time;

        return $this;
    }

    /**
     * Retrieve the object's publication date.
     *
     * @return DateTimeInterface|null
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set the object's expiration date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @throws UnexpectedValueException If the date/time value is invalid.
     * @throws InvalidArgumentException If the value is not a date/time instance.
     * @return PublishableInterface Chainable
     */
    public function setExpiryDate($time)
    {
        if ($time === null || $time === '') {
            $this->expiryDate = null;
            return $this;
        }

        if (is_string($time)) {
            try {
                $time = new DateTime($time);
            } catch (Exception $e) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid Expiration Date: %s',
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }

        if (!$time instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                'Expiration Date must be a date/time string or an instance of DateTimeInterface'
            );
        }

        $this->expiryDate = $time;

        return $this;
    }

    /**
     * Retrieve the object's expiration date.
     *
     * @return DateTimeInterface|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set the object's publication status.
     *
     * @param  string $status A publication status.
     * @throws InvalidArgumentException If the status is invalid.
     * @return PublishableInterface Chainable
     */
    public function setPublishStatus($status)
    {
        if ($status === null || $status === '') {
            $this->publishStatus = null;
            return $this;
        }

        $specialStatus = [
            static::STATUS_EXPIRED  => static::STATUS_PUBLISHED,
            static::STATUS_UPCOMING => static::STATUS_PUBLISHED
        ];

        /** Resolve any special statuses */
        if (isset($specialStatus[$status])) {
            $status = $specialStatus[$status];
        }

        $validStatus = [
            static::STATUS_DRAFT,
            static::STATUS_PENDING,
            static::STATUS_PUBLISHED
        ];

        if (!in_array($status, $validStatus)) {
            throw new InvalidArgumentException(sprintf(
                'Status "%s" is not a valid publish status.',
                $status
            ));
        }

        $this->publishStatus = $status;

        return $this;
    }

    /**
     * Retrieve the object's publication status.
     *
     * @return string|null
     */
    public function getPublishStatus()
    {
        return $this->publishStatus;
    }

    /**
     * Retrieve the object's publication status based on publication and expiration dates.
     *
     * - If the publication status is not "published", that status is returned (e.g., "draft", "pending", NULL).
     * - If no publication date is set, then it's assumed to be always published (or "expired").
     * - If no expiration date is set, then it's assumed to never expire.
     * - If a publication date is set to a future date, then it's assumed to be scheduled to be published ("upcoming").
     *
     * @return string|null
     */
    public function publishDateStatus()
    {
        $now = new DateTime();
        $publish = $this->getPublishDate();
        $expiry  = $this->getExpiryDate();
        $status  = $this->getPublishStatus()        ;

        if ($status !== static::STATUS_PUBLISHED) {
            return $status;
        }

        if (!$publish) {
            if (!$expiry || $now < $expiry) {
                return static::STATUS_PUBLISHED;
            } else {
                return static::STATUS_EXPIRED;
            }
        } else {
            if ($now < $publish) {
                return static::STATUS_UPCOMING;
            } else {
                if (!$expiry || $now < $expiry) {
                    return static::STATUS_PUBLISHED;
                } else {
                    return static::STATUS_EXPIRED;
                }
            }
        }
    }

    /**
     * Determine if the object is published.
     *
     * @return boolean
     */
    public function isPublished()
    {
        return ($this->publishDateStatus() === static::STATUS_PUBLISHED);
    }
}
