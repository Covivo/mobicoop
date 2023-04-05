<?php

namespace App\UserRelaunch\Service;

use App\UserRelaunch\Entity\Notification;
use App\UserRelaunch\Entity\Planning;

class RelaunchManager
{
    /**
     * @var array
     */
    private $_programmedNotifications;

    /**
     * @var Planning
     */
    private $_currentNotification;

    public function __construct(array $notifications)
    {
        $this->_programmedNotifications = $notifications;
    }

    public function relaunchUsers()
    {
        foreach ($this->_programmedNotifications as $notification) {
            $this->_currentNotification = new Notification($notification);

            $this->_whatIsToDo();
        }
    }

    private function _whatIsToDo(): void
    {
    }
}
