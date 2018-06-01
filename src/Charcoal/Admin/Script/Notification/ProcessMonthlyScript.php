<?php

namespace Charcoal\Admin\Script\Notification;

use DateTime;

// From 'charcoal-admin'
use Charcoal\Admin\Object\Notification;
use Charcoal\Admin\Script\Notification\AbstractNotificationScript;

/**
 * Process "hourly" notifications
 */
class ProcessMonthlyScript extends AbstractNotificationScript
{
    /**
     * Get the frequency type of this script.
     *
     * @return string
     */
    protected function frequency()
    {
        return 'monthly';
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     * @return DateTime
     */
    protected function startDate()
    {
        $d = new DateTime('first day of last month');
        $d->setTime(0, 0, 0);
        return $d;
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     * @return DateTime
     */
    protected function endDate()
    {
        $d = new DateTime('first day of this month');
        $d->setTime(0, 0, 0);
        return $d;
    }

    /**
     * @param Notification $notification The notification object.
     * @param array        $objects      The objects that were modified.
     * @return array
     */
    protected function emailData(Notification $notification, array $objects)
    {
        return [
            'subject'         => sprintf('Monthly Charcoal Notification - %s', $this->startDate()->format('Y-m-d')),
            'template_ident'  => 'charcoal/admin/email/notification.monthly',
            'template_data'   => [
                'startString' => $this->startDate()->format('Y-m-d')
            ]
        ];
    }
}
