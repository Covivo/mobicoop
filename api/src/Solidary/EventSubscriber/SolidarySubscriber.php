<?php

namespace App\Solidary\EventSubscriber;

use App\Communication\Service\NotificationManager;
use App\Solidary\Event\SolidaryCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SolidarySubscriber implements EventSubscriberInterface
{
    private $_isOperatorNotificationAllowed;
    private $_notificationManager;

    public function __construct(bool $isOperatorNotificationAllowed = false, NotificationManager $notificationManager)
    {
        $this->_isOperatorNotificationAllowed = $isOperatorNotificationAllowed;
        $this->_notificationManager = $notificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryCreatedEvent::NAME => 'onSolidaryCreated',
        ];
    }

    public function onSolidaryCreated(SolidaryCreatedEvent $event)
    {
        if (!$this->_isOperatorNotificationAllowed) {
            return;
        }

        $solidary = $event->getSolidary();

        if (!is_null($solidary->getSolidaryUserstructure()) && !is_null($solidary->getSolidaryUserstructure()->getStructure())) {
            $operators = array_map(function ($operate) {
                return $operate->getUser();
            }, $solidary->getSolidaryUserstructure()->getStructure()->getOperates());

            foreach ($operators as $operator) {
                $this->_notificationManager->notifies(SolidaryCreatedEvent::NAME, $operator, $solidary);
            }
        }
    }
}
