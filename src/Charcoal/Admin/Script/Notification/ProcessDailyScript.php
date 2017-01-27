<?php

namespace Charcoal\Admin\Script\Notification;

use DateTime;

use Charcoal\Admin\Script\Notification\AbstractNotificationScript;

/**
 * Process "daily" notifications
 */
class ProcessDailyScript extends AbstractNotificationScript
{
    /**
     * Get the frequency type of this script.
     *
     * @return string
     */
    protected function frequency()
    {
        return 'daily';
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     * @return string
     */
    protected function startDate()
    {
        $d = new DateTime('yesterday');
        $d->setTime(0, 0, 0);
        return $d->format('Y-m-d H:i:s');
    }

    /**
     * Retrieve the "maximal" date that the revisions should have been made for this script.
     * @return string
     */
    protected function endDate()
    {
        $d = new DateTime('yesterday');
        $d->setTime(23, 59, 59);
        return $d->format('Y-m-d H:i:s');
    }
}
