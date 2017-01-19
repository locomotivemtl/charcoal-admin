<?php

namespace Charcoal\Object;

// Dependencies from `PHP`
use \DateTime;
use \DateTimeInterface;
use \InvalidArgumentException;

/**
 * A full implementation, as trait, of the `PublishableInterface`.
 */
trait PublishableTrait
{
    /**
     * @var DateTimeInterface $publishDate
     */
    protected $publishDate;

    /**
     * @var DateTimeInterface $expiryDate
     */
    protected $expiryDate;

    /**
     * @var string $publishStatus
     */
    protected $publishStatus;

    /**
     * @param string|DateTimeInterface|null $publishDate The publishing date.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return PublishableInterface Chainable
     */
    public function setPublishDate($publishDate)
    {
        if ($publishDate === null || $publishDate === '') {
            $this->publishDate = null;
            return $this;
        }
        if (is_string($publishDate)) {
            $publishDate = new DateTime($publishDate);
        }
        if (!($publishDate instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Publish Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->publishDate = $publishDate;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function publishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param string|DateTimeInterface|null $expiryDate The expiry date.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return PublishableInterface Chainable
     */
    public function setExpiryDate($expiryDate)
    {
        if ($expiryDate === null || $expiryDate === '') {
            $this->expiryDate = null;
            return $this;
        }
        if (is_string($expiryDate)) {
            $expiryDate = new DateTime($expiryDate);
        }
        if (!($expiryDate instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Expiry Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function expiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param string $status The publish status (draft, pending or published).
     * @throws InvalidArgumentException If the status is not one of the 3 valid status.
     * @return PublishableInterface Chainable
     */
    public function setPublishStatus($status)
    {
        $validStatus = [
            '',
            'draft',
            'pending',
            'expired',
            'published'
        ];
        if (!in_array($status, $validStatus)) {
            throw new InvalidArgumentException(
                sprintf('Status "%s" is not a valid publish status.', $status)
            );
        }
        $this->publishStatus = $status;
        return $this;
    }

    /**
     * Get the object's publish status.
     *
     * Status can be:
     * - `draft`
     * - `pending`
     * - `published`
     * - `upcoming`
     * - `expired`
     *
     * Note that the `upcoming` and `expired` status are specialized status when
     * the object is set to `published` but the `publishDate` or `expiryDate` do not match.
     *
     * @return string
     */
    public function publishStatus()
    {
        $status = $this->publishStatus;
        if (!$status || $status == 'published') {
            $status = $this->publishDateStatus();
        }
        return $status;
    }

    /**
     * Get the "publish status" from the publish date / expiry date.
     *
     * - If no publish date is set, then it is assumed to be "always published." (or expired)
     * - If no expiry date is set, then it is assumed to never expire.
     *
     * @return string
     */
    private function publishDateStatus()
    {
        $now = new DateTime();
        $publish = $this->publishDate();
        $expiry = $this->expiryDate();

        if (!$publish) {
            if (!$expiry || $now < $expiry) {
                return 'published';
            } else {
                return 'expired';
            }
        } else {
            if ($now < $publish) {
                return 'pending';
            } else {
                if (!$expiry || $now < $expiry) {
                    return 'published';
                } else {
                    return 'expired';
                }
            }
        }
    }

    /**
     * @return boolean
     */
    public function isPublished()
    {
        return ($this->publishStatus() == 'published');
    }
}
