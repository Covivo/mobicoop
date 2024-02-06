<?php

namespace App\Communication\Service\PushToCertifiedJourney\PushEvents;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Matching;
use App\Communication\Interfaces\PushEventInterface;
use App\Communication\Service\NotificationManager;
use App\Service\Date\DateService;
use App\User\Entity\User;

abstract class PushEvent implements PushEventInterface
{
    public const PUSH_ACTION = '';

    /**
     * @var NotificationManager
     */
    protected $_notificationManager;

    /**
     * @var \DateTime
     */
    protected $_deadlineDate;

    /**
     * @var int
     */
    protected $_interval;

    /**
     * @var CarpoolProof[]|Matching[]
     */
    protected $_journeys;

    /**
     * @var \DateTimeInterface
     */
    protected $_now;

    /**
     * @var User[]
     */
    protected $_users = [];

    protected function __construct(NotificationManager $notificationManager, int $interval)
    {
        $this->_notificationManager = $notificationManager;

        $this->_interval = $interval;
        $this->_now = DateService::getNow();
    }

    public function execute(): bool
    {
        $this->_setUsersToNotify();

        $this->_notifyUsers();

        return true;
    }

    protected function _setUsersToNotify()
    {
        foreach ($this->_journeys as $journey) {
            switch (true) {
                case $journey instanceof CarpoolProof:
                    array_push($this->_users, $journey->getDriver());
                    array_push($this->_users, $journey->getPassenger());

                    break;

                case $journey instanceof Matching:
                    array_push($this->_users, $journey->getProposalOffer()->getUser());
                    array_push($this->_users, $journey->getProposalRequest()->getUser());

                    break;
            }
        }
    }

    protected function _notifyUsers()
    {
        foreach ($this->_users as $user) {
            $this->_notificationManager->notifies(static::PUSH_ACTION, $user);
        }
    }

    protected function _resetUsers()
    {
        $this->_users = [];
    }
}
