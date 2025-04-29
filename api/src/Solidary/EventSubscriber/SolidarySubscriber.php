<?php

namespace App\Solidary\EventSubscriber;

use App\Communication\Service\NotificationManager;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Service\SolidaryManager;
use App\User\Event\UserHomeAddressUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SolidarySubscriber implements EventSubscriberInterface
{
    private $_isOperatorNotificationAllowed;
    private $_notificationManager;

    /**
     * @var SolidaryManager
     */
    private $_solidaryManager;

    public function __construct(bool $isOperatorNotificationAllowed = false, NotificationManager $notificationManager, SolidaryManager $solidaryManager)
    {
        $this->_isOperatorNotificationAllowed = $isOperatorNotificationAllowed;
        $this->_notificationManager = $notificationManager;
        $this->_solidaryManager = $solidaryManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryCreatedEvent::NAME => 'onSolidaryCreated',
            UserHomeAddressUpdateEvent::NAME => 'onUserHomeAddressUpdated',
        ];
    }

    public function onSolidaryCreated(SolidaryCreatedEvent $event)
    {
        if (!$this->_isOperatorNotificationAllowed) {
            return;
        }

        $solidary = $event->getSolidary();
        $solidary->getSolidaryUserStructure()->getSolidaryUser()->setUser($event->getuser());
        if (!is_null($solidary->getSolidaryUserstructure()) && !is_null($solidary->getSolidaryUserstructure()->getStructure())) {
            $operators = array_map(function ($operate) {
                return $operate->getUser();
            }, $solidary->getSolidaryUserstructure()->getStructure()->getOperates());

            foreach ($operators as $operator) {
                $this->_notificationManager->notifies(SolidaryCreatedEvent::NAME, $operator, $solidary);
            }
        }
    }

    public function onUserHomeAddressUpdated(UserHomeAddressUpdateEvent $event) {
        $user = $event->getUser();

        if (!is_null($user->getSolidaryUser())) {
            $this->_solidaryManager->updateSolidaryUserAddress($user);
        }
    }
}
