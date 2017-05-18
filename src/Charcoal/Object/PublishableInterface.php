<?php

namespace Charcoal\Object;

use DateTimeInterface;

/**
 * Defines an object as publishable via date/time values and statuses.
 *
 * Default statuses:
 *
 * - `draft` — Incomplete object viewable by a limited userbase.
 * - `pending` — Awaiting a user with higher access to publish.
 * - `published` — Publiclly viewable by everyone.
 *
 * Special statuses:
 *
 * - `upcoming` — Scheduled to be published at a future date.
 * - `expired` — Removed from public viewing.
 *
 * Note: the specialized statuses are used when the object is set to `published`
 * but the publication date or expiration date do not match.
 */
interface PublishableInterface
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_PENDING   = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_UPCOMING  = 'upcoming';
    const STATUS_EXPIRED   = 'expired';

    /**
     * Set the object's publication date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @return PublishableInterface Chainable
     */
    public function setPublishDate($time);

    /**
     * Retrieve the object's publication date.
     *
     * @return \DateTimeInterface|null
     */
    public function publishDate();

    /**
     * Set the object's expiration date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @return PublishableInterface Chainable
     */
    public function setExpiryDate($time);

    /**
     * Retrieve the object's expiration date.
     *
     * @return \DateTimeInterface|null
     */
    public function expiryDate();

    /**
     * Set the object's publication status.
     *
     * @param  string $status A publication status.
     * @return PublishableInterface Chainable
     */
    public function setPublishStatus($status);

    /**
     * Retrieve the object's publication status.
     *
     * @return string
     */
    public function publishStatus();

    /**
     * Determine if the object is published.
     *
     * @return boolean
     */
    public function isPublished();
}
