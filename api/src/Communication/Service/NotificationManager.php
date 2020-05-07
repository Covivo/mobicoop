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

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Waypoint;
use App\Communication\Interfaces\MessagerInterface;
use App\Event\Entity\Event;
use Psr\Log\LoggerInterface;
use App\Communication\Repository\NotificationRepository;
use App\User\Repository\UserNotificationRepository;
use App\Communication\Entity\Medium;
use App\User\Entity\User;
use App\Communication\Entity\Notified;
use App\Communication\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use App\Communication\Entity\Email;
use App\Communication\Entity\Sms;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Communication\Entity\Recipient;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Ask;
use App\Communication\Entity\Message;
use App\Rdex\Entity\RdexConnection;
use App\User\Service\UserManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Carpool\Service\AdManager;

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
    private $smsManager;
    private $templating;
    private $emailTemplatePath;
    private $emailTitleTemplatePath;
    private $smsTemplatePath;
    private $logger;
    private $notificationRepository;
    private $userNotificationRepository;
    private $enabled;
    private $translator;
    private $userManager;
    private $adManager;
    const LANG = 'fr_FR';


    public function __construct(EntityManagerInterface $entityManager, Environment $templating, InternalMessageManager $internalMessageManager, EmailManager $emailManager, SmsManager $smsManager, LoggerInterface $logger, NotificationRepository $notificationRepository, UserNotificationRepository $userNotificationRepository, string $emailTemplatePath, string $emailTitleTemplatePath, string $smsTemplatePath, bool $enabled, TranslatorInterface $translator, UserManager $userManager, AdManager $adManager)
    {
        $this->entityManager = $entityManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->emailManager = $emailManager;
        $this->smsManager = $smsManager;
        $this->logger = $logger;
        $this->notificationRepository = $notificationRepository;
        $this->userNotificationRepository = $userNotificationRepository;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->emailTitleTemplatePath = $emailTitleTemplatePath;
        $this->smsTemplatePath = $smsTemplatePath;
        $this->templating = $templating;
        $this->enabled = $enabled;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
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
        if (!$this->enabled) {
            return;
        }
        // Check if the user is anonymised if yes we don't send notifications
        if ($recipient->getStatus() == USER::STATUS_ANONYMIZED) {
            return;
        }

        $notifications = null;
        // we check the user notifications
        $userNotifications = $this->userNotificationRepository->findActiveByAction($action, $recipient->getId());
       
        if (count($userNotifications)>0) {
            // the user should have notifications...
            $notifications = [];
            foreach ($userNotifications as $userNotification) {
                $notifications[] = $userNotification->getNotification();
            }
        } else {
            // if the user have no notifications, we use the default notifications
            $notifications = $this->notificationRepository->findActiveByAction($action);
        }

        if ($notifications && is_array($notifications)) {
            foreach ($notifications as $notification) {
                switch ($notification->getMedium()->getId()) {
                    case Medium::MEDIUM_MESSAGE:
                        if (!is_null($object)) {
                            $this->logger->info("Internal message notification for $action / " . get_class($object) . " / " . $recipient->getEmail());
                            if ($object instanceof  MessagerInterface && !is_null($object->getMessage())) {
                                $this->internalMessageManager->sendForObject([$recipient], $object);
                            }
                        }
                        $this->createNotified($notification, $recipient, $object);
                        break;
                    case Medium::MEDIUM_EMAIL:
                        $this->notifyByEmail($notification, $recipient, $object);
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Email notification for $action / " . $recipient->getEmail());
                        break;
                    case Medium::MEDIUM_SMS:
                        $this->notifyBySMS($notification, $recipient, $object);
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
    private function notifyByEmail(Notification $notification, User $recipient, ?object $object = null, $lang='fr_FR')
    {
        $email = new Email();
        $email->setRecipientEmail($recipient->getEmail());
        $titleContext = [];
        $bodyContext = [];
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $titleContext = [];
                    $bodyContext = ['user'=>$recipient, 'notification'=> $notification];
                    break;
                case Matching::class:
                    $titleContext = [];
                    $bodyContext = ['user'=>$recipient, 'notification'=> $notification, 'matching'=> $object];
                    break;
                case AskHistory::class:
                    $titleContext = [];
                    $bodyContext = ['user'=>$recipient, 'askHistory'=>$object];
                    break;
                case Ask::class:
                    $titleContext = [];
                    foreach ($object->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if ($waypoint->getPosition() == 0) {
                            $passengerOriginWaypoint = $waypoint;
                        } elseif ($waypoint->isDestination() == true) {
                            $passengerDestinationWaypoint = $waypoint;
                        }
                    };
                    $bodyContext = ['user'=>$recipient, 'ask'=>$object, 'origin'=>$passengerOriginWaypoint, 'destination'=>$passengerDestinationWaypoint];
                    break;
                case Ad::class:
                    $titleContext = [];
                    $outwardOrigin = null;
                    $outwardDestination = null;
                    $returnOrigin = null;
                    $returnDestination = null;
                    $sender = $this->userManager->getUser($object->getUserId());
                    if ($object->getResults()[0]->getResultPassenger() !== null) {
                        $result = $object->getResults()[0]->getResultPassenger();
                    } else {
                        $result = $object->getResults()[0]->getResultDriver();
                    };
                    if ($result->getOutward() !== null) {
                        foreach ($result->getOutward()->getWaypoints() as $waypoint) {
                            if ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'origin') {
                                $outwardOrigin = $waypoint;
                            } elseif ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'destination') {
                                $outwardDestination = $waypoint;
                            }
                        }
                        // We check if there is really at least one day checked. Otherwide, we force the $result->outward at null to hide it in the mail
                        // It's the case when the user who made the ask only checked return days
                        if (!$result->getOutward()->isMonCheck() &&
                        !$result->getOutward()->isTueCheck() &&
                        !$result->getOutward()->isWedCheck() &&
                        !$result->getOutward()->isThuCheck() &&
                        !$result->getOutward()->isFriCheck() &&
                        !$result->getOutward()->isSatCheck() &&
                        !$result->getOutward()->isSunCheck()
                        ) {
                            $result->setOutward(null);
                        }
                    }
                    if ($result->getReturn() !== null) {
                        foreach ($result->getReturn()->getWaypoints() as $waypoint) {
                            if ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'origin') {
                                $returnOrigin = $waypoint;
                            } elseif ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'destination') {
                                $returnDestination = $waypoint;
                            }
                        }
                    }
                    $bodyContext = [
                        'user'=>$recipient,
                        'ad'=>$object,
                        'sender'=>$sender,
                        'result'=>$result,
                        'outwardOrigin'=>$outwardOrigin,
                        'outwardDestination'=>$outwardDestination,
                        'returnOrigin'=>$returnOrigin,
                        'returnDestination'=>$returnDestination
                    ];
                    break;
                case Recipient::class:
                    $titleContext = [];
                    $bodyContext = [];
                    break;
                case User::class:
                    $titleContext = [];
                    $bodyContext = ['user'=>$recipient];
                    break;
                case Event::class:
                    $titleContext = [];
                    $bodyContext = ['user'=>$recipient, 'event' => $object];
                    break;
                case Message::class:
                    $titleContext = ['user'=>$object->getUser()];
                    $bodyContext = ['text'=>$object->getText(), 'user'=>$recipient];
                break;
                case RdexConnection::class:
                    $titleContext = [];
                    $ad = $this->adManager->getAd($object->getJourneysId());
                    if (!is_null($object->getDriver()->getUuid())) {
                        $journey = $ad->getResults()[0]->getResultPassenger();
                    } else {
                        $journey = $ad->getResults()[0]->getResultDriver();
                    }

                    $origin = $journey->getOutward()->getOrigin()->getAddressLocality();
                    $destination = $journey->getOutward()->getDestination()->getAddressLocality();
                    $date = "";
                    if (!is_null($journey->getOutward()->getDate())) {
                        $date = $journey->getOutward()->getDate()->format("d/m/Y");
                    } elseif (!is_null($journey->getOutward()->getFromDate())) {
                        $date = $journey->getOutward()->getFromDate()->format("d/m/Y");
                    }
                    $time = "";
                    if (!is_null($journey->getOutward()->getTime())) {
                        $time = $journey->getOutward()->getTime()->format("H\hi");
                    }
                    $bodyContext = [
                        'text'=>$object->getDetails(),
                        'user'=>$recipient,
                        'operator'=>$object->getOperator(),
                        'origin'=>$object->getOrigin(),
                        'journeyOrigin' => $origin,
                        'journeyDestination' => $destination,
                        'journeyDate' => $date,
                        'journeyTime' => $time
                    ];
                break;
                default:
                    if (isset($object->new) && isset($object->old) && isset($object->ask) && isset($object->sender)) {
                        $outwardOrigin = null;
                        $outwardDestination = null;
                        /** @var Waypoint $waypoint */
                        foreach ($object->ask->getWaypoints() as $waypoint) {
                            if ($waypoint->getPosition() == 0) {
                                $outwardOrigin = $waypoint;
                            } elseif ($waypoint->isDestination()) {
                                $outwardDestination = $waypoint;
                            }
                        }
                        $bodyContext = [
                            'user' => $recipient,
                            'notification' => $notification,
                            'object' => $object,
                            'origin' => $outwardOrigin,
                            'destination' => $outwardDestination
                        ];
                    }
            }
        } else {
            $bodyContext = ['user'=>$recipient, 'notification'=> $notification];
        }
        
        $lang = self::LANG;
        if (!is_null($recipient->getLanguage())) {
            $lang = $recipient->getLanguage();
        }
        
        $this->translator->setLocale($lang);
        $email->setObject($this->templating->render(
            $notification->getTemplateTitle() ? $this->emailTitleTemplatePath . $notification->getTemplateTitle() : $this->emailTitleTemplatePath . $notification->getAction()->getName().'.html.twig',
            [
                'context' => $titleContext
            ]
        ));

        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        $this->emailManager->send($email, $notification->getTemplateBody() ? $this->emailTemplatePath . $notification->getTemplateBody() : $this->emailTemplatePath . $notification->getAction()->getName(), $bodyContext, $lang);
    }

    /**
     * Notify a user by sms.
     * Different variables can be passed to the notification body and title depending on the object linked to the notification.
     *
     * @param Notification  $notification
     * @param User          $recipient
     * @param object|null   $object
     * @return void
     */
    private function notifyBySms(Notification $notification, User $recipient, ?object $object = null)
    {
        $sms = new Sms();
        $sms->setRecipientTelephone($recipient->getTelephone());
        $bodyContext = [];
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $bodyContext = ['user'=>$recipient, 'notification'=> $notification, 'object' => $object];
                    break;
                case Matching::class:
                    $bodyContext = ['user'=>$recipient, 'notification'=> $notification, 'matching'=> $object];
                    break;
                case AskHistory::class:
                    $bodyContext = ['user'=>$recipient];
                    break;
                case Ask::class:
                    foreach ($object->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if ($waypoint->getPosition() == 0) {
                            $passengerOriginWaypoint = $waypoint;
                        } elseif ($waypoint->isDestination() == true) {
                            $passengerDestinationWaypoint = $waypoint;
                        }
                    };
                    $bodyContext = ['user'=>$recipient, 'ask'=>$object, 'origin'=>$passengerOriginWaypoint, 'destination'=>$passengerDestinationWaypoint];
                    break;
                case Ad::class:
                    $outwardOrigin = null;
                    $outwardDestination = null;
                    $returnOrigin = null;
                    $returnDestination = null;
                    $sender = $this->userManager->getUser($object->getUserId());
                    if ($object->getResults()[0]->getResultPassenger() !== null) {
                        $result = $object->getResults()[0]->getResultPassenger();
                    } else {
                        $result = $object->getResults()[0]->getResultDriver();
                    };
                    if ($result->getOutward() !== null) {
                        foreach ($result->getOutward()->getWaypoints() as $waypoint) {
                            if ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'origin') {
                                $outwardOrigin = $waypoint;
                            } elseif ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'destination') {
                                $outwardDestination = $waypoint;
                            }
                        }
                    }
                    if ($result->getReturn() !== null) {
                        foreach ($result->getReturn()->getWaypoints() as $waypoint) {
                            if ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'origin') {
                                $returnOrigin = $waypoint;
                            } elseif ($waypoint['role'] == 'passenger' && $waypoint['type'] == 'destination') {
                                $returnDestination = $waypoint;
                            }
                        }
                    }
                    $bodyContext = [
                        'user'=>$recipient,
                        'ad'=>$object,
                        'sender'=>$sender,
                        'result'=>$result,
                        'outwardOrigin'=>$outwardOrigin,
                        'outwardDestination'=>$outwardDestination,
                        'returnOrigin'=>$returnOrigin,
                        'returnDestination'=>$returnDestination
                    ];
                    break;
                case Recipient::class:
                    $bodyContext = [];
                    break;
                case User::class:
                    $bodyContext = ['user'=>$recipient];
                    break;
                case Message::class:
                    $bodyContext = ['text'=>$object->getText(), 'user'=>$recipient];
                    break;
                default:
                    if (isset($object->new) && isset($object->old) && isset($object->ask) && isset($object->sender)) {
                        $outwardOrigin = null;
                        $outwardDestination = null;
                        /** @var Waypoint $waypoint */
                        foreach ($object->ask->getWaypoints() as $waypoint) {
                            if ($waypoint->getPosition() == 0) {
                                $outwardOrigin = $waypoint;
                            } elseif ($waypoint->isDestination()) {
                                $outwardDestination = $waypoint;
                            }
                        }
                        $bodyContext = [
                            'user' => $recipient,
                            'notification' => $notification,
                            'object' => $object,
                            'origin' => $outwardOrigin,
                            'destination' => $outwardDestination
                        ];
                    }
            }
        } else {
            $bodyContext = ['user'=>$recipient, 'notification'=> $notification];
        }

        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        $this->smsManager->send($sms, $notification->getTemplateBody() ? $this->smsTemplatePath . $notification->getTemplateBody() : $this->smsTemplatePath . $notification->getAction()->getName(), $bodyContext);
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
