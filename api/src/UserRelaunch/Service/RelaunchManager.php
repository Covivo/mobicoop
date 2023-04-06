<?php

namespace App\UserRelaunch\Service;

use App\Carpool\Entity\Criteria;
use App\Communication\Service\NotificationManager;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\UserRelaunch\Entity\Notification;

class RelaunchManager
{
    /**
     * @var CarpoolItemRepository
     */
    private $_carpoolItemRepository;

    /**
     * @var NotificationManager
     */
    private $_notificationManager;

    /**
     * @var array
     */
    private $_programmedNotifications;

    /**
     * @var Notification
     */
    private $_currentNotification;

    public function __construct(CarpoolItemRepository $carpoolItemRepository, NotificationManager $notificationManager, array $notifications)
    {
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_notificationManager = $notificationManager;
        $this->_programmedNotifications = $notifications;
    }

    public function relaunchUsers()
    {
        foreach ($this->_programmedNotifications as $notification) {
            $this->_currentNotification = new Notification($notification);

            if ($this->_currentNotification->canNotify()) {
                $this->_whatIsToDo();
            }
        }
    }

    private function _whatIsToDo(): void
    {
        switch ($this->_currentNotification->getName()) {
            case 'pay_after_carpool_regular':
                if ($this->_currentNotification->canNotify()) {
                    $objects = $this->_carpoolItemRepository->findUnpaydForRelaunch(Criteria::FREQUENCY_REGULAR, $this->_currentNotification->getReminderDate());
                }

                break;
                // Define other actions

            default:
                throw new \LogicException('The relaunch '.$this->_currentNotification->getName().' is not available');

                break;
        }

        foreach ($objects as $object) {
            switch (true) {
                case $object instanceof CarpoolItem:
                    $recipient = $object->getDebtorUser();

                    break;
                    // Define other types of objects
            }

            $this->_executeRelaunch($recipient, $object);
        }
    }

    private function _executeRelaunch(User $recipient, $object): void
    {
        $this->_notificationManager->notifies($this->_currentNotification->getName(), $recipient, $object);
    }
}
