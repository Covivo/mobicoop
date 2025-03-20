<?php

namespace App\Solidary\EventSubscriber;

use App\Solidary\Event\SolidaryMatchingEvent;
use App\Solidary\Service\SolidaryNotificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SolidaryMatchingSubscriber implements EventSubscriberInterface
{
    /**
     * @var SolidaryNotificationManager
     */
    private $_solidaryNotificationManager;

    public function __construct(SolidaryNotificationManager $solidaryNotificationManager)
    {
        $this->_solidaryNotificationManager = $solidaryNotificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryMatchingEvent::NAME => 'onSolidaryMatchingSuccess',
        ];
    }

    public function onSolidaryMatchingSuccess(SolidaryMatchingEvent $event)
    {
        $this->_solidaryNotificationManager->notifyMatched($event->getSolidary());
    }
}
