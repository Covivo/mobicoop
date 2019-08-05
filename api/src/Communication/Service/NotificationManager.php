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
use App\Communication\Entity\Email;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Communication\Entity\Recipient;
use App\Carpool\Entity\AskHistory;

/**
 * Notification manager
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class NotificationManager
{
    private $entityManager;
    private $internalMessageManager;
    private $emailManager;
    private $templating;
    private $emailTemplatePath;
    private $emailTitleTemplatePath;
    private $smsTemplatePath;
    private $logger;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, \Twig_Environment $templating, InternalMessageManager $internalMessageManager, EmailManager $emailManager, LoggerInterface $logger, NotificationRepository $notificationRepository, string $emailTemplatePath, string $emailTitleTemplatePath, string $smsTemplatePath)
    {
        $this->entityManager = $entityManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->emailManager = $emailManager;
        $this->logger = $logger;
        $this->notificationRepository = $notificationRepository;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->emailTitleTemplatePath = $emailTitleTemplatePath;
        $this->smsTemplatePath = $smsTemplatePath;
        $this->templating = $templating;
    }

    /**
     * Send a notification for the action/user.
     *
     * @param string $action    The action
     * @param User $recipient   The user to be notified
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
                        if (!is_null($object)) {
                            $this->logger->info("Internal message notification for $action / " . get_class($object) . " / " . $recipient->getEmail());
                            $this->internalMessageManager->sendForObject([$recipient], $object);
                        }
                        $this->createNotified($notification, $recipient, $object);
                        break;
                    case Medium::MEDIUM_EMAIL:
                        $this->notifyByEmail($notification, $recipient, $object);
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Email notification for $action / " . $recipient->getEmail());
                        break;
                    case Medium::MEDIUM_SMS:
                        // todo : call the dedicated service to send the sms with the notification template
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Sms notification for  $action / " . $recipient->getEmail());
                        break;
                    case Medium::MEDIUM_PUSH:
                        // todo : call the dedicated service to send the push with the notification template
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Push notification for  $action / " . $recipient->getEmail());
                        break;
                }
            }
        }
    }

    /**
     * Notify a user by email.
     * Different variables can be passed to the notification body and title depending on the object linked to the notification.
     *
     * @param Notification  $notification
     * @param User          $recipient
     * @param object|null   $object
     * @return void
     */
    private function notifyByEmail(Notification $notification, User $recipient, ?object $object = null)
    {
        $email = new Email();
        $email->setRecipientEmail($recipient->getEmail());
        $titleContext = [];
        $bodyContext = [];
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $titleContext = [];
                    $bodyContext = [];
                    break;
                case Matching::class:
                    $titleContext = [];
                    $bodyContext = [];
                    break;
                case AskHistory::class:
                    $titleContext = [];
                    $bodyContext = [];
                    break;
                case Recipient::class:
                    $titleContext = [];
                    $bodyContext = [];
                    break;
            }
        } else {
            $bodyContext = ['user'=>$recipient, 'notification'=> $notification];
        }
        
        $email->setObject($this->templating->render(
            $notification->getTemplateTitle() ? $this->emailTitleTemplatePath . $notification->getTemplateTitle() : $this->emailTitleTemplatePath . $notification->getAction()->getName().'.html.twig',
            [
                'context' => $titleContext
            ]
        ));
        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        $this->emailManager->send($email, $notification->getTemplateBody() ? $this->emailTemplatePath . $notification->getTemplateBody() : $this->emailTemplatePath . $notification->getAction()->getName(), $bodyContext, $recipient->getLanguage());
    }

    /**
     * Create a notified object.
     *
     * @param Notification  $notification   The notification at the origin of the notified
     * @param User          $user           The recipient of the notification
     * @param object|null   $object         The object linked with the notification
     * @return void
     */
    public function createNotified(Notification $notification, User $user, ?object $object)
    {
        $notified = new Notified();
        $notified->setStatus(Notified::STATUS_SENT);
        $notified->setSentDate(new \Datetime());
        $notified->setNotification($notification);
        $notified->setUser($user);
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $notified->setProposal($object);
                    break;
                case Matching::class:
                    $notified->setMatching($object);
                    break;
                case AskHistory::class:
                    $notified->setAskHistory($object);
                    break;
                case Recipient::class:
                    $notified->setRecipient($object);
                    break;
            }
        }
        $this->entityManager->persist($notified);
        $this->entityManager->flush();
    }
}
