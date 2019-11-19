<?php

namespace Charcoal\Object;

/**
 *
 */
interface ObjectScheduleInterface
{
    /**
     * @param string $targetType The object type (type-ident).
     * @return self
     */
    public function setTargetType($targetType);

    /**
     * @return string
     */
    public function getTargetType();

    /**
     * @param mixed $targetId The object ID.
     * @return self
     */
    public function setTargetId($targetId);

    /**
     * @return mixed
     */
    public function getTargetId();

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setScheduledDate($ts);

    /**
     * Retrieve the date/time the item should be processed at.
     *
     * @return null|\DateTimeInterface
     */
    public function getScheduledDate();

    /**
     * @param array|string $data The data diff.
     * @return self
     */
    public function setDataDiff($data);

    /**
     * @return array
     */
    public function getDataDiff();

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setProcessedDate($ts);

    /**
     * Retrieve the date/time the item was processed at.
     *
     * @return null|\DateTimeInterface
     */
    public function getProcessedDate();

    /**
     * Process the item.
     *
     * @param  callable $callback        An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean  Success / Failure
     */
    public function process(
        callable $callback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );
}
