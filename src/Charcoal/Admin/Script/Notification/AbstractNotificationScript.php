<?php

namespace Charcoal\Admin\Script\Notification;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\CollectionInterface;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevision;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-app'
use Charcoal\App\Script\CronScriptInterface;
use Charcoal\App\Script\CronScriptTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;
use Charcoal\Admin\Object\Notification;
use Charcoal\Admin\User;

/**
 * Base class for all the notification script
 */
abstract class AbstractNotificationScript extends AdminScript implements CronScriptInterface
{
    use CronScriptTrait;

    /**
     * @var FactoryInterface
     */
    private $notificationFactory;

    /**
     * @var FactoryInterface
     */
    private $emailFactory;

    /**
     * @var FactoryInterface
     */
    private $userFactory;

    /**
     * @var FactoryInterface
     */
    private $objectFactory;

    /**
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'now' => [
                'longPrefix'    => 'now',
                'description'   => 'The "relative" time this script should run at. '.
                                   'If nothing is provided, default "now" is used.',
                'defaultValue'  => 'now'
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $this->startLock();

        $climate = $this->climate();

        $frequency = $this->frequency();

        $notifications = $this->loadNotifications($frequency);

        if (!$notifications) {
            return $response;
        }

        foreach ($notifications as $notification) {
            $this->handleNotification($notification);
        }

        $this->stopLock();

        return $response;
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setNotificationFactory($container['model/factory']);
        $this->setRevisionFactory($container['model/factory']);
        $this->emailFactory = $container['email/factory'];
        $this->userFactory = $container['model/factory'];
        $this->objectFactory = $container['model/factory'];
    }

    /**
     * Get the frequency type of this script.
     *
     * @return string
     */
    abstract protected function frequency();

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     * @return DateTime
     */
    abstract protected function startDate();

    /**
     * Retrieve the "maximal" date that the revisions should have been made for this script.
     * @return DateTime
     */
    abstract protected function endDate();

    /**
     * @param Notification $notification The notification object.
     * @param array        $objects      The objects that were modified.
     * @return array
     */
    abstract protected function emailData(Notification $notification, array $objects);

    /**
     * @param string $frequency The frequency type to load.
     * @return Charcoal\Model\CollectionInterface
     */
    private function loadNotifications($frequency)
    {
        $loader = new CollectionLoader([
            'logger' => $this->logger,
            'factory' => $this->notificationFactory()
        ]);
        $loader->setModel(Notification::class);
        $loader->addFilter([
            'property'  => 'frequency',
            'val'     => $frequency
        ]);
        $notifications = $loader->load();
        return $notifications;
    }

    /**
     * Handle a notification request
     *
     * @param Notification $notification The notification object to handle.
     * @return void
     */
    private function handleNotification(Notification $notification)
    {
        if (empty($notification->targetTypes())) {
            return;
        }
        $objectsByTypes = [];
        $numTotal = 0;
        foreach ($notification->targetTypes() as $objType) {
            $objType = trim($objType);
            $objects = $this->updatedObjects($objType);
            $num = count($objects);
            if ($num == 0) {
                continue;
            }
            $obj = [];
            $obj['objects'] = $objects;
            $obj['num'] = $num;
            $obj['type'] = $objType;
            $obj['typeLabel'] = isset($objects[0]['targetTypeLabel']) ? $objects[0]['targetTypeLabel'] : $objType;

            $objectsByTypes[$objType] = $obj;
            $numTotal += $num;
        }
        $this->sendEmail($notification, $objectsByTypes, $numTotal);
    }

    /**
     * @param Notification $notification The notification object.
     * @param array        $objects      The objects that were modified.
     * @param integer      $numTotal     Total number of modified objects.
     * @return void
     */
    private function sendEmail(Notification $notification, array $objects, $numTotal)
    {
        if ($numTotal == 0) {
            return;
        }

        $email = $this->emailFactory->create('email');

        $defaultEmailData = [
            'campaign'      => 'admin-notification-'.$notification->id(),
            'subject'       => 'Charcoal Notification',
            'from'          => 'charcoal@example.com',
            'template_data' => [
                'objects'       => new \ArrayIterator($objects),
                'numObjects'    => $numTotal,
                'frequency'     => $this->frequency(),
                'startString'   => $this->startDate()->format('Y-m-d H:i:s'),
                'endString'     => $this->endDate()->format('Y-m-d H:i:s')
            ]
        ];
        $emailData = array_replace_recursive($defaultEmailData, $this->emailData($notification, $objects));

        $email->setData($emailData);

        foreach ($notification->users() as $userId) {
            $user = $this->userFactory->create(User::class);
            $user->load($userId);
            if (!$user->id() || !$user->email()) {
                continue;
            }
            $email->addTo($user->email());
        }

        foreach ($notification->extraEmails() as $extraEmail) {
            $email->addBcc($extraEmail);
        }
        $email->send();
    }

    /**
     * @param string $objType The object (target) type to process.
     * @return CollectionInterface
     */
    private function updatedObjects($objType)
    {
        $loader = new CollectionLoader([
            'logger'   => $this->logger,
            'factory'  => $this->revisionFactory()
        ]);
        $loader->setModel(ObjectRevision::class);
        $loader->addFilter([
            'property'  => 'target_type',
            'val'       => $objType
        ]);
        $loader->addFilter([
            'property'  => 'rev_ts',
            'val'       => $this->startDate()->format('Y-m-d H:i:s'),
            'operator'  => '>'
        ]);
        $loader->addFilter([
            'property'  => 'rev_ts',
            'val'       => $this->endDate()->format('Y-m-d H:i:s'),
            'operator'  => '<'
        ]);
        $loader->addOrder([
            'property' => 'rev_ts',
            'mode'     => 'DESC'
        ]);
        $objFactory = $this->objectFactory;
        $userFactory = $this->userFactory;
        $baseUrl = $this->baseUrl();

        $loader->setCallback(function (&$obj) use ($objFactory, $userFactory, $baseUrl) {
            $diff = $obj->dataDiff();
            $obj->updatedProperties = isset($diff[0]) ? array_keys($diff[0]) : [];
            $obj->dateStr = $obj['rev_ts']->format('Y-m-d H:i:s');
            $obj->numProperties = count($obj->updatedProperties);
            $obj->propertiesString = implode(', ', $obj->updatedProperties);
            $obj->targetObject = $objFactory->create($obj['target_type'])->load($obj['target_id']);
            if (is_callable([$obj->targetObject, 'title'])) {
                $obj['title'] = $obj->targetObject->title();
            } elseif (is_callable([$obj->targetObject, 'name'])) {
                $obj['title'] = $obj->targetObject->name();
            }
            if (isset($obj->targetObject->metadata()['label'])) {
                $obj->targetTypeLabel = $this->translator()->translation($obj->targetObject->metadata()['label']);
            } else {
                $obj->targetTypeLabel = $obj['target_type'];
            }
            $obj->userObject = $userFactory->create(User::class)->load($obj['rev_user']);
            $obj->publicUrl = is_callable([$obj->targetObject, 'url']) ? $baseUrl.$obj->targetObject->url() : null;
            $obj->charcoalUrl = sprintf(
                $baseUrl.'admin/object/edit?obj_type=%s&obj_id=%s',
                $obj['target_type'],
                $obj['target_id']
            );
        });
        return $loader->load();
    }

    /**
     * @param FactoryInterface $factory The factory used to create queue items.
     * @return void
     */
    private function setNotificationFactory(FactoryInterface $factory)
    {
        $this->notificationFactory = $factory;
    }

    /**
     * @return FactoryInterface
     */
    private function notificationFactory()
    {
        return $this->notificationFactory;
    }

    /**
     * @param FactoryInterface $factory The factory used to create queue items.
     * @return void
     */
    private function setRevisionFactory(FactoryInterface $factory)
    {
        $this->revisionFactory = $factory;
    }

    /**
     * @return FactoryInterface
     */
    private function revisionFactory()
    {
        return $this->revisionFactory;
    }
}
