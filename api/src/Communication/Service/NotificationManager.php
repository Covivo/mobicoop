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
 */

namespace App\Communication\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Carpool\Service\ProposalManager;
use App\Communication\Entity\Email;
use App\Communication\Entity\Medium;
use App\Communication\Entity\Message;
use App\Communication\Entity\Notification;
use App\Communication\Entity\Notified;
use App\Communication\Entity\Push;
use App\Communication\Entity\Recipient;
use App\Communication\Entity\Sms;
use App\Communication\Interfaces\MessagerInterface;
use App\Communication\Repository\NotificationRepository;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Event\Entity\Event;
use App\Match\Entity\Mass;
use App\Match\Entity\MassPerson;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\PaymentProfile;
use App\Rdex\Entity\RdexConnection;
use App\Scammer\Entity\Scammer;
use App\Solidary\Entity\SolidaryContact;
use App\User\Entity\PushToken;
use App\User\Entity\Review;
use App\User\Entity\User;
use App\User\Repository\UserNotificationRepository;
use App\User\Service\UserManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Notification manager.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class NotificationManager
{
    public const LANG = 'fr';
    private $entityManager;
    private $internalMessageManager;
    private $emailManager;
    private $pushManager;
    private $smsManager;
    private $templating;
    private $emailTemplatePath;
    private $emailTitleTemplatePath;
    private $pushTemplatePath;
    private $pushTitleTemplatePath;
    private $smsTemplatePath;
    private $logger;
    private $notificationRepository;
    private $userNotificationRepository;
    private $enabled;
    private $mailsEnabled;
    private $smsEnabled;
    private $pushEnabled;
    private $translator;
    private $userManager;
    private $adManager;
    private $proposalManager;
    private $communicationFolder;
    private $altCommunicationFolder;

    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $templating,
        InternalMessageManager $internalMessageManager,
        EmailManager $emailManager,
        PushManager $pushManager,
        SmsManager $smsManager,
        LoggerInterface $logger,
        NotificationRepository $notificationRepository,
        UserNotificationRepository $userNotificationRepository,
        string $emailTemplatePath,
        string $emailTitleTemplatePath,
        string $pushTemplatePath,
        string $pushTitleTemplatePath,
        string $smsTemplatePath,
        bool $enabled,
        bool $mailsEnabled,
        bool $smsEnabled,
        bool $pushEnabled,
        TranslatorInterface $translator,
        UserManager $userManager,
        AdManager $adManager,
        ProposalManager $proposalManager,
        string $communicationFolder,
        string $altCommunicationFolder
    ) {
        $this->entityManager = $entityManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->emailManager = $emailManager;
        $this->pushManager = $pushManager;
        $this->smsManager = $smsManager;
        $this->logger = $logger;
        $this->notificationRepository = $notificationRepository;
        $this->userNotificationRepository = $userNotificationRepository;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->emailTitleTemplatePath = $emailTitleTemplatePath;
        $this->pushTemplatePath = $pushTemplatePath;
        $this->pushTitleTemplatePath = $pushTitleTemplatePath;
        $this->smsTemplatePath = $smsTemplatePath;
        $this->templating = $templating;
        $this->enabled = $enabled;
        $this->mailsEnabled = $mailsEnabled;
        $this->smsEnabled = $smsEnabled;
        $this->pushEnabled = $pushEnabled;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
        $this->proposalManager = $proposalManager;
        $this->communicationFolder = $communicationFolder;
        $this->altCommunicationFolder = $altCommunicationFolder;
    }

    /**
     * Send a notification for the action/user.
     *
     * @param string $action    The action
     * @param User   $recipient The user to be notified
     * @param object $object    The object linked to the notification (if more information is needed to be joined in the notification)
     */
    public function notifies(string $action, User $recipient, ?object $object = null)
    {
        // check if notification system is enabled
        if (!$this->enabled) {
            return;
        }

        // Check if the user is anonymised if yes we don't send notifications
        if (USER::STATUS_ANONYMIZED == $recipient->getStatus()) {
            return;
        }

        // check if there's a notification associated with the given action
        $notifications = null;

        // we check the user notifications
        $userNotifications = $this->userNotificationRepository->findActiveByAction($action, $recipient->getId());

        if (count($userNotifications) > 0) {
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
                            $this->logger->info("Internal message notification for {$action} / ".get_class($object).' / '.$recipient->getEmail());
                            if ($object instanceof MessagerInterface && !is_null($object->getMessage())) {
                                $this->internalMessageManager->sendForObject([$recipient], $object);
                            }
                        }
                        $this->createNotified($notification, $recipient, $object);

                        break;

                    case Medium::MEDIUM_EMAIL:
                        if (!$this->mailsEnabled) {
                            break;
                        }
                        $this->notifyByEmail($notification, $recipient, $object);
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Email notification for {$action} / ".$recipient->getEmail());

                        break;

                    case Medium::MEDIUM_SMS:
                        if (!$this->smsEnabled) {
                            break;
                        }
                        $this->notifyBySMS($notification, $recipient, $object);

                        break;

                    case Medium::MEDIUM_PUSH:
                        if (!$this->pushEnabled) {
                            break;
                        }
                        $this->notifyByPush($notification, $recipient, $object);
                        $this->createNotified($notification, $recipient, $object);
                        $this->logger->info("Push notification for {$action} / ".$recipient->getEmail());

                        break;
                }
            }
        }
    }

    /**
     * Create a notified object.
     *
     * @param Notification $notification The notification at the origin of the notified
     * @param User         $user         The recipient of the notification
     * @param null|object  $object       The object linked with the notification
     */
    public function createNotified(Notification $notification, User $user, ?object $object)
    {
        $notified = new Notified();
        $notified->setStatus(Notified::STATUS_SENT);
        $notified->setSentDate(new \DateTime());
        $notified->setNotification($notification);
        $notified->setUser($user);
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $notified->setProposal($object);

                    break;

                case Community::class:
                    $notified->setCommunity($object);

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

    /**
     * Notify a user by email.
     * Different variables can be passed to the notification body and title depending on the object linked to the notification.
     *
     * @param mixed $lang
     */
    private function notifyByEmail(Notification $notification, User $recipient, ?object $object = null)
    {
        $email = new Email();
        $email->setRecipientEmail($recipient->getEmail());
        $titleContext = [];
        $bodyContext = [];
        if ($object) {
            switch (ClassUtils::getRealClass(get_class($object))) {
                case Proposal::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'notification' => $notification];

                    break;

                case Matching::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'notification' => $notification, 'matching' => $object];

                    break;

                case AskHistory::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'askHistory' => $object];

                    break;

                case Ask::class:
                    $titleContext = [];
                    foreach ($object->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOriginWaypoint = $waypoint;
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestinationWaypoint = $waypoint;
                        }
                    }
                    $bodyContext = ['user' => $recipient, 'ask' => $object, 'origin' => $passengerOriginWaypoint, 'destination' => $passengerDestinationWaypoint];

                    break;

                case Ad::class:
                    $titleContext = [];
                    $outwardOrigin = null;
                    $outwardDestination = null;
                    $returnOrigin = null;
                    $returnDestination = null;
                    $sender = $this->userManager->getUser($object->getUserId());
                    if (null !== $object->getResults()[0]->getResultPassenger()) {
                        $result = $object->getResults()[0]->getResultPassenger();
                    } else {
                        $result = $object->getResults()[0]->getResultDriver();
                    }
                    if (null !== $result->getOutward()) {
                        foreach ($result->getOutward()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $outwardOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $outwardDestination = $waypoint;
                            }
                        }
                        // We check if there is really at least one day checked. Otherwide, we force the $result->outward at null to hide it in the mail
                        // It's the case when the user who made the ask only checked return days
                        if (Criteria::FREQUENCY_REGULAR == $object->getFrequency()
                        && !$result->getOutward()->isMonCheck()
                        && !$result->getOutward()->isTueCheck()
                        && !$result->getOutward()->isWedCheck()
                        && !$result->getOutward()->isThuCheck()
                        && !$result->getOutward()->isFriCheck()
                        && !$result->getOutward()->isSatCheck()
                        && !$result->getOutward()->isSunCheck()
                        ) {
                            $result->setOutward(null);
                        }
                    }
                    if (null !== $result->getReturn()) {
                        foreach ($result->getReturn()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $returnOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $returnDestination = $waypoint;
                            }
                        }
                    }
                    $bodyContext = [
                        'user' => $recipient,
                        'ad' => $object,
                        'sender' => $sender,
                        'result' => $result,
                        'outwardOrigin' => $outwardOrigin,
                        'outwardDestination' => $outwardDestination,
                        'returnOrigin' => $returnOrigin,
                        'returnDestination' => $returnDestination,
                    ];

                    break;

                case Recipient::class:
                    $titleContext = [];
                    $bodyContext = [];

                    break;

                case User::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient];

                    break;

                case Event::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'event' => $object];

                    break;

                case Community::class:
                    $sender = null;
                    $titleContext = [
                        'community' => $object,
                    ];
                    $bodyContext = [
                        'recipient' => $recipient,
                        'community' => $object,
                    ];

                    break;

                case CommunityUser::class:
                    $sender = null;
                    $bodyContext = [
                        'recipient' => $recipient,
                        'community' => $object->getCommunity(),
                        'senderGivenName' => $object->getUser()->getGivenName(),
                        'senderShortFamilyName' => $object->getUser()->getShortFamilyName(),
                    ];

                    break;

                case Message::class:
                    $titleContext = ['user' => $object->getUser()];
                    $bodyContext = ['text' => $object->getText(), 'user' => $recipient];

                break;

                case RdexConnection::class:
                    $titleContext = [];
                    $proposal = $this->proposalManager->get($object->getJourneysId());

                    $origin = $proposal->getWaypoints()[0]->getAddress()->getAddressLocality();
                    $destination = $proposal->getWaypoints()[count($proposal->getWaypoints()) - 1]->getAddress()->getAddressLocality();

                    $date = $time = '';
                    if (Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency()) {
                        if (!is_null($proposal->getCriteria()->getFromDate())) {
                            $date = $proposal->getCriteria()->getFromDate()->format('d/m/Y');
                        }
                        if (!is_null($proposal->getCriteria()->getFromTime())) {
                            $date = $proposal->getCriteria()->getFromDate();
                        }
                    }

                    $bodyContext = [
                        'text' => $object->getDetails(),
                        'user' => $recipient,
                        'operator' => $object->getOperator(),
                        'origin' => $object->getOrigin(),
                        'journeyOrigin' => $origin,
                        'journeyDestination' => $destination,
                        'journeyDate' => $date,
                        'journeyTime' => $time,
                    ];

                break;

                case SolidaryContact::class:
                    $titleContext = ['user' => $object->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser()];
                    $bodyContext = ['text' => $object->getContent(), 'recipient' => $recipient];

                break;

                case Mass::class:
                    $titleContext = ['massId' => $object->getId()];
                    $bodyContext = ['massId' => $object->getId(), 'errors' => $object->getErrors()];

                    break;

                case MassPerson::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'clearPassword' => $object->getClearPassword()];

                    break;

                case CarpoolItem::class:
                    $titleContext = ['deptor' => $object->getDebtorUser()];
                    foreach ($object->getAsk()->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOrigin = $waypoint->getAddress()->getAddressLocality();
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestination = $waypoint->getAddress()->getAddressLocality();
                        }
                    }
                    // if regular we get the first day of the week
                    $firstDayOfWeek = null;
                    if (Criteria::FREQUENCY_REGULAR == $object->getAsk()->getCriteria()->getFrequency()) {
                        $day = new \DateTime($object->getItemDate()->format('d-m-Y'));
                        $day->setISODate((int) $day->format('o'), (int) $day->format('W'), 1);
                        $firstDayOfWeek = $day->format('l d F Y');
                    }
                    $bodyContext = [
                        'deptor' => $object->getDebtorUser(),
                        'creditor' => $object->getCreditorUser(),
                        'amount' => $object->getAmount(),
                        'origin' => $passengerOrigin,
                        'destination' => $passengerDestination,
                        'week' => $firstDayOfWeek,
                    ];

                    break;

                case PaymentProfile::class:
                    $titleContext = [];
                    $bodyContext = ['paymentProfile' => $object];

                    break;

                case Review::class:
                    $titleContext = [];
                    $bodyContext = [
                        'givenName' => $object->getReviewer()->getGivenName(),
                        'shortFamilyName' => $object->getReviewer()->getShortFamilyName(),
                    ];

                    break;

                case Scammer::class:
                    $titleContext = [];
                    $bodyContext = ['scammer' => $object];

                    break;

                default:
                    if (isset($object->new, $object->old, $object->ask, $object->sender)) {
                        $outwardOrigin = null;
                        $outwardDestination = null;

                        /** @var Waypoint $waypoint */
                        foreach ($object->ask->getWaypoints() as $waypoint) {
                            if (0 == $waypoint->getPosition()) {
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
                            'destination' => $outwardDestination,
                        ];
                    }
            }
        } else {
            $bodyContext = ['user' => $recipient, 'notification' => $notification];
        }

        $lang = self::LANG;
        if (!is_null($recipient->getLanguage())) {
            $lang = $recipient->getLanguage();
            $this->translator->setLocale($lang->getCode());
            $templateLanguage = $lang->getCode();
        } else {
            $this->translator->setLocale($lang);
            $templateLanguage = $lang;
        }

        if ($notification->hasAlt()) {
            $titleTemplate = $this->altCommunicationFolder.$templateLanguage.$this->emailTitleTemplatePath.$notification->getAction()->getName().'.html.twig';
        } else {
            $titleTemplate = $this->communicationFolder.$templateLanguage.$this->emailTitleTemplatePath.$notification->getAction()->getName().'.html.twig';
        }
        $email->setObject($this->templating->render(
            $titleTemplate,
            [
                'context' => $titleContext,
            ]
        ));

        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        if ($notification->hasAlt()) {
            $this->emailManager->send($email, $this->altCommunicationFolder.$templateLanguage.$this->emailTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        } else {
            $this->emailManager->send($email, $this->communicationFolder.$templateLanguage.$this->emailTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        }
    }

    /**
     * Notify a user by sms.
     * Different variables can be passed to the notification body and title depending on the object linked to the notification.
     *
     * @param mixed $lang
     */
    private function notifyBySms(Notification $notification, User $recipient, ?object $object = null)
    {
        if (is_null($recipient->getTelephone())) {
            return;
        }
        if (!$notification->isPermissive() && is_null($recipient->getPhoneValidatedDate()) && is_null($recipient->getPhoneToken())) {
            return;
        }
        $sms = new Sms();
        $sms->setRecipientTelephone($recipient->getTelephone());
        $bodyContext = [];
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $bodyContext = ['user' => $recipient, 'notification' => $notification, 'object' => $object];

                    break;

                case Matching::class:
                    $bodyContext = ['user' => $recipient, 'notification' => $notification, 'matching' => $object];

                    break;

                case AskHistory::class:
                    $bodyContext = ['user' => $recipient];

                    break;

                case Ask::class:
                    foreach ($object->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOriginWaypoint = $waypoint;
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestinationWaypoint = $waypoint;
                        }
                    }
                    $bodyContext = ['user' => $recipient, 'ask' => $object, 'origin' => $passengerOriginWaypoint, 'destination' => $passengerDestinationWaypoint];

                    break;

                case Ad::class:
                    $outwardOrigin = null;
                    $outwardDestination = null;
                    $returnOrigin = null;
                    $returnDestination = null;
                    $sender = $this->userManager->getUser($object->getUserId());
                    if (null !== $object->getResults()[0]->getResultPassenger()) {
                        $result = $object->getResults()[0]->getResultPassenger();
                    } else {
                        $result = $object->getResults()[0]->getResultDriver();
                    }
                    if (null !== $result->getOutward()) {
                        foreach ($result->getOutward()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $outwardOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $outwardDestination = $waypoint;
                            }
                        }
                    }
                    if (null !== $result->getReturn()) {
                        foreach ($result->getReturn()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $returnOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $returnDestination = $waypoint;
                            }
                        }
                    }
                    $bodyContext = [
                        'user' => $recipient,
                        'ad' => $object,
                        'sender' => $sender,
                        'result' => $result,
                        'outwardOrigin' => $outwardOrigin,
                        'outwardDestination' => $outwardDestination,
                        'returnOrigin' => $returnOrigin,
                        'returnDestination' => $returnDestination,
                    ];

                    break;

                case Recipient::class:
                    $bodyContext = [];

                    break;

                case User::class:
                    $bodyContext = ['user' => $recipient];

                    break;

                case Message::class:
                    $bodyContext = ['text' => $object->getText(), 'user' => $recipient];

                    break;

                case SolidaryContact::class:
                    $bodyContext = ['text' => $object->getContent(), 'recipient' => $recipient];

                    break;

                case CarpoolItem::class:
                    foreach ($object->getAsk()->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOrigin = $waypoint->getAddress()->getAddressLocality();
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestination = $waypoint->getAddress()->getAddressLocality();
                        }
                    }
                    $firstDayOfWeek = null;
                    if (Criteria::FREQUENCY_REGULAR == $object->getAsk()->getCriteria()->getFrequency()) {
                        $day = new \DateTime($object->getItemDate()->format('d-m-Y'));
                        $day->setISODate((int) $day->format('o'), (int) $day->format('W'), 1);
                        $firstDayOfWeek = $day->format('l d F Y');
                    }
                    $bodyContext = [
                        'deptor' => $object->getDebtorUser(),
                        'creditor' => $object->getCreditorUser(),
                        'amount' => $object->getAmount(),
                        'origin' => $passengerOrigin,
                        'destination' => $passengerDestination,
                        'week' => $firstDayOfWeek,
                    ];

                    break;

                case PaymentProfile::class:
                    $bodyContext = ['paymentProfile' => $object];

                    break;

                case Review::class:
                    $bodyContext = [
                        'givenName' => $object->getReviewer()->getGivenName(),
                        'shortFamilyName' => $object->getReviewer()->getShortFamilyName(),
                    ];

                    break;

                default:
                    if (isset($object->new, $object->old, $object->ask, $object->sender)) {
                        $outwardOrigin = null;
                        $outwardDestination = null;

                        /** @var Ask $ask */
                        $ask = $object->ask;
                        if ($recipient->getId() == $ask->getMatching()->getProposalOffer()->getUser()->getId()) {
                            // The recipient is the driver, we take the waypoints of the ProposalOffer
                            $waypoints = $ask->getMatching()->getProposalOffer()->getWaypoints();
                        } else {
                            // The recipient is the passenger, we take the waypoints of ProposalRequest
                            $waypoints = $ask->getMatching()->getProposalRequest()->getWaypoints();
                        }

                        foreach ($waypoints as $waypoint) {
                            if (0 == $waypoint->getPosition()) {
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
                            'destination' => $outwardDestination,
                        ];
                    }
            }
        } else {
            $bodyContext = ['user' => $recipient, 'notification' => $notification];
        }

        $lang = self::LANG;
        if (!is_null($recipient->getLanguage())) {
            $lang = $recipient->getLanguage();
            $this->translator->setLocale($lang->getCode());
            $templateLanguage = $lang->getCode();
        } else {
            $this->translator->setLocale($lang);
            $templateLanguage = $lang;
        }
        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        if ($notification->hasAlt()) {
            $this->smsManager->send($sms, $this->altCommunicationFolder.$templateLanguage.$this->smsTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        } else {
            $this->smsManager->send($sms, $this->communicationFolder.$templateLanguage.$this->smsTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        }
        $this->createNotified($notification, $recipient, $object);
        $this->logger->info('Sms notification for '.$notification->getAction()->getName().' / '.$recipient->getEmail());
    }

    /**
     * Notify a user by push notification.
     * Different variables can be passed to the notification body and title depending on the object linked to the notification.
     *
     * @param Notification $notification The notification
     * @param User         $recipient    The recipient user
     * @param null|object  $object       The object to use
     * @param mixed        $lang
     */
    private function notifyByPush(Notification $notification, User $recipient, ?object $object = null)
    {
        $push = new Push();
        $recipientDeviceIds = [];
        foreach ($recipient->getPushTokens() as $pushToken) {
            // @var PushToken $pushToken
            $recipientDeviceIds[] = $pushToken->getToken();
        }
        // we check if the recipient has at least one push token id
        if (0 == count($recipientDeviceIds)) {
            return;
        }
        $push->setRecipientDeviceIds($recipientDeviceIds);
        $bodyContext = [];
        if ($object) {
            switch (get_class($object)) {
                case Proposal::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'notification' => $notification, 'object' => $object];

                    break;

                case Matching::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient, 'notification' => $notification, 'matching' => $object];

                    break;

                case AskHistory::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient];

                    break;

                case Ask::class:
                    $titleContext = [];
                    foreach ($object->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOriginWaypoint = $waypoint;
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestinationWaypoint = $waypoint;
                        }
                    }
                    $bodyContext = ['user' => $recipient, 'ask' => $object, 'origin' => $passengerOriginWaypoint, 'destination' => $passengerDestinationWaypoint];

                    break;

                case Ad::class:
                    $titleContext = [];
                    $outwardOrigin = null;
                    $outwardDestination = null;
                    $returnOrigin = null;
                    $returnDestination = null;
                    $sender = $this->userManager->getUser($object->getUserId());
                    if (null !== $object->getResults()[0]->getResultPassenger()) {
                        $result = $object->getResults()[0]->getResultPassenger();
                    } else {
                        $result = $object->getResults()[0]->getResultDriver();
                    }
                    if (null !== $result->getOutward()) {
                        foreach ($result->getOutward()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $outwardOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $outwardDestination = $waypoint;
                            }
                        }
                    }
                    if (null !== $result->getReturn()) {
                        foreach ($result->getReturn()->getWaypoints() as $waypoint) {
                            if ('passenger' == $waypoint['role'] && 'origin' == $waypoint['type']) {
                                $returnOrigin = $waypoint;
                            } elseif ('passenger' == $waypoint['role'] && 'destination' == $waypoint['type']) {
                                $returnDestination = $waypoint;
                            }
                        }
                    }
                    $bodyContext = [
                        'user' => $recipient,
                        'ad' => $object,
                        'sender' => $sender,
                        'result' => $result,
                        'outwardOrigin' => $outwardOrigin,
                        'outwardDestination' => $outwardDestination,
                        'returnOrigin' => $returnOrigin,
                        'returnDestination' => $returnDestination,
                    ];

                    break;

                case Recipient::class:
                    $titleContext = [];
                    $bodyContext = [];

                    break;

                case User::class:
                    $titleContext = [];
                    $bodyContext = ['user' => $recipient];

                    break;

                case Message::class:
                    $titleContext = ['user' => $object->getUser()];
                    $bodyContext = ['text' => $object->getText(), 'user' => $recipient];

                    break;

                case CarpoolItem::class:
                    $titleContext = ['deptor' => $object->getDebtorUser()];
                    foreach ($object->getAsk()->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                        if (0 == $waypoint->getPosition()) {
                            $passengerOrigin = $waypoint->getAddress()->getAddressLocality();
                        } elseif (true == $waypoint->isDestination()) {
                            $passengerDestination = $waypoint->getAddress()->getAddressLocality();
                        }
                    }
                    $firstDayOfWeek = null;
                    if (Criteria::FREQUENCY_REGULAR == $object->getAsk()->getCriteria()->getFrequency()) {
                        $day = new \DateTime($object->getItemDate()->format('d-m-Y'));
                        $day->setISODate((int) $day->format('o'), (int) $day->format('W'), 1);
                        $firstDayOfWeek = $day->format('l d F Y');
                    }
                    $bodyContext = [
                        'deptor' => $object->getDebtorUser(),
                        'creditor' => $object->getCreditorUser(),
                        'amount' => $object->getAmount(),
                        'origin' => $passengerOrigin,
                        'destination' => $passengerDestination,
                        'week' => $firstDayOfWeek,
                    ];

                    break;

                case PaymentProfile::class:
                    $titleContext = [];
                    $bodyContext = ['paymentProfile' => $object];

                    break;

                case Review::class:
                    $titleContext = [];
                    $bodyContext = [
                        'givenName' => $object->getReviewer()->getGivenName(),
                        'shortFamilyName' => $object->getReviewer()->getShortFamilyName(),
                    ];

                    break;

                default:
                    $titleContext = [];
                    if (isset($object->new, $object->old, $object->ask, $object->sender)) {
                        $outwardOrigin = null;
                        $outwardDestination = null;

                        /** @var Waypoint $waypoint */
                        foreach ($object->ask->getWaypoints() as $waypoint) {
                            if (0 == $waypoint->getPosition()) {
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
                            'destination' => $outwardDestination,
                        ];
                    }
            }
        } else {
            $titleContext = [];
            $bodyContext = ['user' => $recipient, 'notification' => $notification];
        }

        $lang = self::LANG;
        if (!is_null($recipient->getLanguage())) {
            $lang = $recipient->getLanguage();
            $this->translator->setLocale($lang->getCode());
            $templateLanguage = $lang->getCode();
        } else {
            $this->translator->setLocale($lang);
            $templateLanguage = $lang;
        }

        if ($notification->hasAlt()) {
            $titleTemplate = $this->altCommunicationFolder.$templateLanguage.$this->pushTitleTemplatePath.$notification->getAction()->getName().'.html.twig';
        } else {
            $titleTemplate = $this->communicationFolder.$templateLanguage.$this->pushTitleTemplatePath.$notification->getAction()->getName().'.html.twig';
        }
        $push->setTitle($this->templating->render(
            $titleTemplate,
            [
                'context' => $titleContext,
            ]
        ));

        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        if ($notification->hasAlt()) {
            $this->pushManager->send($push, $this->altCommunicationFolder.$templateLanguage.$this->pushTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        } else {
            $this->smsMapushManagernager->send($push, $this->communicationFolder.$templateLanguage.$this->pushTemplatePath.$notification->getAction()->getName(), $bodyContext, $lang);
        }
    }
}
