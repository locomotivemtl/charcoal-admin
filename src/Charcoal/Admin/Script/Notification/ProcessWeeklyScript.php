<?php

namespace Charcoal\Admin\Script\Notification;

use Charcoal\Admin\Script\Notification\AbstractNotificationScript;

/**
 * Process "hourly" notifications
 */
class ProcessWeeklyScript extends AbstractNotificationScript
{
    /**
     * Get the frequency type of this script.
     *
     * @return string
     */
    protected function frequency()
    {
        return 'weekly';
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     * @return string
     */
    protected function startDate()
    {
        $d = new DateTime('last monday');
        $d->setTime(0, 0, 0);
        return $d->format('Y-m-d H:i:s');
    }

    /**
     * Retrieve the "maximal" date that the revisions should have been made for this script.
     * @return string
     */
    protected function endDate()
    {
        $d = new DateTime($this->startDate().' +7 days');
        return $d->format('Y-m-d H:i:s');
    }
}
