<?php

namespace Charcoal\Admin\Widget\Graph;

use \Charcoal\Admin\Widget\Graph\GraphWidgetInterface;

/**
 * Graph Widget Interface
 */
interface TimeGraphWidgetInterface extends GraphWidgetInterface
{
    /**
     * @param string $type The group type.
     */
    public function setGroupingType($type);

    /**
     * @return string
     */
    public function groupingType();

    /**
     * @param string $format The date format.
     * @return UserTimeGraphWidget Chainable
     */
    public function setDateFormat($format);

    /**
     * @return string
     */
    public function dateFormat();

    /**
     * @param string $format The date format.
     * @return UserTimeGraphWidget Chainable
     */
    public function setSqlDateFormat($format);

    /**
     * @return string
     */
    public function sqlDateFormat();

    /**
     * @param string|DateTimeInterface $ts The start date.
     * @return UserTimeGraphWidget Chainable
     */
    public function setStartDate($ts);

    /**
     * @return DateTimeInterface
     */
    public function startDate();

    /**
     * @param string|DateTimeInterface $ts The end date.
     * @return UserTimeGraphWidget Chainable
     */
    public function setEndDate($ts);

    /**
     * @return DateTimeInterface
     */
    public function endDate();

    /**
     * @param string|DateInterval $interval
     * @return UserTimeGraphWidget Chainable
     */
    public function setDateInterval($interval);

    /**
     * @return DateInterval
     */
    public function dateInterval();
}
