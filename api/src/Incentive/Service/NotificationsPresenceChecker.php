<?php

namespace App\Incentive\Service;

use App\Action\Entity\Action;
use App\Communication\Entity\Medium;
use App\Communication\Entity\Notification;
use App\Communication\Entity\Notified;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationsPresenceChecker
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var string
     */
    private $_actionName;

    /**
     * @var Action
     */
    private $_action;

    /**
     * @var Notification
     */
    private $_notification;

    /**
     * @var Notified[]
     */
    private $_notified;

    /**
     * @var User
     */
    private $_user;

    public function __construct(EntityManagerInterface $em, User $user, string $actionName)
    {
        $this->_em = $em;

        $this->_user = $user;
        $this->_actionName = $actionName;

        $this->_build();
    }

    public function hasLastNotificationBeenSendAfterDeadline(int $time): bool
    {
        $now = new \DateTime();
        $dealineDate = $now->sub(new \DateInterval('P'.$time.'D'));

        $lastNotified = $this->_getLastNotified();

        return $lastNotified ? $dealineDate < $lastNotified->getSentDate() : false;
    }

    private function _build(): void
    {
        $this->_setAction();
        $this->_setNotification();
        $this->_setNotified();
    }

    private function _setAction()
    {
        $this->_action = $this->_em->getRepository(Action::class)->findOneBy(['name' => $this->_actionName]);
    }

    private function _setNotification(): void
    {
        if (is_null($this->_action)) {
            return;
        }

        $this->_notification = $this->_em->getRepository(Notification::class)->findOneBy([
            'action' => $this->_action,
            'medium' => Medium::MEDIUM_EMAIL,
        ]);
    }

    private function _setNotified(): void
    {
        if (is_null($this->_notification) || is_null($this->_user)) {
            return;
        }

        $this->_notified = $this->_em->getRepository(Notified::class)->findBy(
            [
                'notification' => $this->_notification,
                'user' => $this->_user,
            ]
        );
    }

    private function _getLastNotified(): ?Notified
    {
        return end($this->_notified);
    }
}
