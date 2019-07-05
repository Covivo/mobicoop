<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Communication\Service;

use Psr\Log\LoggerInterface;
use App\Communication\Repository\NotificationRepository;
use App\Communication\Entity\Medium;
use App\User\Entity\User;
use App\Communication\Entity\Notified;
use App\Communication\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Notification manager
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class NotificationManager
{
    private $entityManager;
    private $internalMessageManager;
    private $logger;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, InternalMessageManager $internalMessageManager, LoggerInterface $logger, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->logger = $logger;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Send a notification for the domain/action/user.
     *
     * @param string $action    The action
     * @param User $recipient   The user to be notified
     * @param User $sender      The user that sends the notification (optional)
     * @param object $object    The object linked to the notification (if more information is needed to be joined in the notification)
     * @return void
     */
    public function notifies(string $action, User $recipient, ?object $object = null)
    {
        $notifications = $this->notificationRepository->findActiveByAction($action);
        if ($notifications && is_array($notifications)) {
            foreach ($notifications as $notification) {
                switch ($notification->getMedium()->getId()) {
                    case Medium::MEDIUM_MESSAGE:
                        $this->logger->info("Internal message notification for $action / " . get_class($object) . " / " . $recipient->getEmail());
                        if (!is_null($object)) {
                            $this->internalMessageManager->sendForObject([$recipient],$object);
                        }
                        break;
                    case Medium::MEDIUM_EMAIL:
                        // todo : call the dedicated service to send the email with the notification template
                        $this->logger->info("Email notification for $action / " . $recipient->getEmail());
                        break;
                    case Medium::MEDIUM_SMS:
                        // todo : call the dedicated service to send the sms with the notification template
                        $this->logger->info("Sms notification for  $action / " . $recipient->getEmail());
                        break;
                    case Medium::MEDIUM_PUSH:
                        // todo : call the dedicated service to send the push with the notification template
                        $this->logger->info("Push notification for  $action / " . $recipient->getEmail());
                        break;
                }
            }
        }
    }

    /**
     * Create a notified object. Should be called from the service which sends the notification (or by an event ?)
     *
     * @param Notification $notification
     * @param User $user
     * @param Medium $medium
     * @return void
     */
    public function createNotified(Notification $notification, User $user, Medium $medium)
    {
        $notified = new Notified();
        $notified->setStatus(Notified::STATUS_SENT);
        $notified->setSentDate(new \Datetime());
        $notified->setNotification($notification);
        $notified->setUser($user);
        $notified->setMedium($medium);
        $this->entityManager->persist($notified);
        $this->entityManager->flush();
    }
}
