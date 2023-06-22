<?php

namespace App\UserRelaunch\Service;

use App\Carpool\Entity\Criteria;
use App\Communication\Service\NotificationManager;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Event\PayAfterCarpoolRegularEvent;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\UserRelaunch\Entity\Notification;

class RelaunchManager
{
    public const DEFAULT_START_DAY = 'monday';
    public const DEFAULT_END_DAY = 'sunday';
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

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
        $objects = [];

        switch ($this->_currentNotification->getName()) {
            case PayAfterCarpoolRegularEvent::NAME:
                $objects = $this->_carpoolItemRepository->findUnpaydForRelaunch(Criteria::FREQUENCY_REGULAR, $this->_getLastWeek());
                $this->_currentNotification->setTemplateNameSuffix($this->_currentNotification->getToday()->format('D'));

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
        $this->_notificationManager->notifies($this->_currentNotification->getActionName(), $recipient, $object);
    }

    private function _getLastWeek(): array
    {
        $previous_week = strtotime('-1 week +1 day');

        $start_week = strtotime('last '.self::DEFAULT_START_DAY.' midnight', $previous_week);
        $end_week = strtotime('next '.self::DEFAULT_END_DAY, $start_week);

        $start_week = date(self::DEFAULT_DATE_FORMAT, $start_week);
        $end_week = date(self::DEFAULT_DATE_FORMAT, $end_week);

        return [
            'Mon' => new \DateTime($start_week),
            'Sun' => new \DateTime($end_week),
        ];
    }
}
