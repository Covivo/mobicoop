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

namespace App\User\Service;

use App\Carpool\Repository\AskHistoryRepository;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Service\AskManager;
use App\Communication\Entity\Medium;
use App\Community\Repository\CommunityUserRepository;
use App\Image\Service\ImageManager;
use App\User\Entity\User;
use App\User\Event\UserDeleteAccountWasDriverEvent;
use App\User\Event\UserDeleteAccountWasPassengerEvent;
use App\User\Event\UserPasswordChangeAskedEvent;
use App\User\Event\UserPasswordChangedEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Community\Repository\CommunityRepository;
use App\Community\Entity\CommunityUser;
use App\User\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Communication\Repository\MessageRepository;
use App\Communication\Repository\NotificationRepository;
use App\User\Repository\UserNotificationRepository;
use App\User\Entity\UserNotification;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
use App\User\Event\UserGeneratePhoneTokenAskedEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User\Event\UserUpdatedSelfEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\User\Repository\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * User manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class UserManager
{
    private $entityManager;
    private $imageManager;
    private $roleRepository;
    private $communityRepository;
    private $communityUserRepository;
    private $messageRepository;
    private $askRepository;
    private $askHistoryRepository;
    private $notificationRepository;
    private $userNotificationRepository;
    private $userRepository;
    private $logger;
    private $eventDispatcher;
    private $encoder;
    private $translator;

    // Default carpool settings
    private $chat;
    private $music;
    private $smoke;

    /**
        * Constructor.
        *
        * @param EntityManagerInterface $entityManager
        * @param LoggerInterface $logger
        */
    public function __construct(EntityManagerInterface $entityManager, ImageManager $imageManager, LoggerInterface $logger, EventDispatcherInterface $dispatcher, RoleRepository $roleRepository, CommunityRepository $communityRepository, MessageRepository $messageRepository, UserPasswordEncoderInterface $encoder, NotificationRepository $notificationRepository, UserNotificationRepository $userNotificationRepository, AskHistoryRepository $askHistoryRepository, AskRepository $askRepository, UserRepository $userRepository, $chat, $smoke, $music, CommunityUserRepository $communityUserRepository, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->communityRepository = $communityRepository;
        $this->communityUserRepository = $communityUserRepository;
        $this->messageRepository = $messageRepository;
        $this->askRepository = $askRepository;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->eventDispatcher = $dispatcher;
        $this->encoder = $encoder;
        $this->translator = $translator;
        $this->notificationRepository = $notificationRepository;
        $this->userNotificationRepository = $userNotificationRepository;
        $this->userRepository = $userRepository;
        $this->chat = $chat;
        $this->music = $music;
        $this->smoke = $smoke;
    }

    /**
     * Get a user by its id.
     *
     * @param integer $id
     * @return User|null
     */
    public function getUser(int $id)
    {
        return $this->userRepository->find($id);
    }
    
    /**
     * Registers a user.
     *
     * @param User      $user               The user to register
     * @param boolean   $encodePassword     True to encode password
     * @return User     The user created
     */
    public function registerUser(User $user, bool $encodePassword=false)
    {
        if (count($user->getUserRoles()) == 0) {
            // default role : user registered full
            $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL);
            $userRole = new UserRole();
            $userRole->setRole($role);
            $user->addUserRole($userRole);
        }

        if ($encodePassword) {
            $user->setClearPassword($user->getPassword());
            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
        }

        // default phone display : restricted
        $user->setPhoneDisplay(User::PHONE_DISPLAY_RESTRICTED);
        // creation of the geotoken
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setGeoToken($geoToken);
        // Default carpool settings
        $user->setChat($this->chat);
        $user->setMusic($this->music);
        $user->setSmoke($this->smoke);

        // Create token to valid inscription
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        // For safety, we strip the slashes because this token can be passed in url
        $validationToken = hash("sha256", $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setValidatedDateToken($validationToken);

        $unsubscribeToken = hash("sha256", $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setUnsubscribeToken($unsubscribeToken);

        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // creation of the alert preferences
        $user = $this->createAlerts($user);

        // dispatch en event
        if (is_null($user->getUserDelegate())) {
            // registration by the user itself
            $event = new UserRegisteredEvent($user);
            $this->eventDispatcher->dispatch(UserRegisteredEvent::NAME, $event);
        } else {
            // delegate registration
            $event = new UserDelegateRegisteredEvent($user);
            $this->eventDispatcher->dispatch(UserDelegateRegisteredEvent::NAME, $event);
            // send password ?
            if ($user->getPasswordSendType() == User::PWD_SEND_TYPE_SMS) {
                $event = new UserDelegateRegisteredPasswordSendEvent($user);
                $this->eventDispatcher->dispatch(UserDelegateRegisteredPasswordSendEvent::NAME, $event);
            }
        }

        // return the user
        return $user;
    }

    /**
     * Update a user.
     *
     * @param User $user    The user to update
     * @return User         The user updated
     */
    public function updateUser(User $user)
    {

         // activate sms notification if phone validated
        if ($user->getPhoneValidatedDate()) {
            $user = $this->activateSmsNotification($user);
        }
        // check if the phone is updated and if so reset phoneToken and validatedDate
        if ($user->getTelephone() != $user->getOldTelephone()) {
            $user->setPhoneToken(null);
            $user->setPhoneValidatedDate(null);
            // deactivate sms notification since the phone is new
            $user = $this->deActivateSmsNotification($user);
        }
       
        // update of the geotoken
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setGeoToken($geoToken);
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // dispatch an event
        $event = new UserUpdatedSelfEvent($user);
        $this->eventDispatcher->dispatch(UserUpdatedSelfEvent::NAME, $event);
        // return the user
        return $user;
    }

    /**
     * Treat a user : set default parameters.
     * Used for example for imports.
     *
     * @param User $user    The user to treat
     * @return User         The user treated
     */
    public function treatUser(User $user)
    {
        // we treat the role
        if (count($user->getUserRoles()) == 0) {
            // we have to add a role
            $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL);
            $userRole = new UserRole();
            $userRole->setRole($role);
            $user->addUserRole($userRole);
        }

        // we treat the notifications
        if (count($user->getUserNotifications()) == 0) {
            // we have to create the default user notifications, we don't persist immediately
            $user = $this->createAlerts($user, false);
        }

        $this->entityManager->persist($user);

        return $user;
    }

    /**
     * Get the private communities of the given user.
     *
     * @param User $user
     * @return array
     */
    public function getPrivateCommunities(?User $user): array
    {
        if (is_null($user)) {
            return [];
        }
        if ($communities = $this->communityRepository->findByUser($user, true, null, [CommunityUser::STATUS_ACCEPTED_AS_MODERATOR,CommunityUser::STATUS_ACCEPTED_AS_MEMBER])) {
            return $communities;
        }
        return [];
    }

    public function getThreads(User $user): array
    {
        if ($threads = $this->messageRepository->findThreads($user)) {
            return $threads;
        }
        return [];
    }

    
    /**
     * Build messages threads considering the type (Direct or Carpool)
     * @param User $user    The User involved
     * @param String $type  Type of messages Direct or Carpool
     */
    public function getThreadsMessages(User $user, $type="Direct"): array
    {
        $threads = [];
        if ($type=="Direct") {
            $threads = $this->messageRepository->findThreadsDirectMessages($user);
        } elseif ($type=="Carpool") {
            $threads = $this->askRepository->findAskByUser($user);
        } else {
            return [];
        }
        
        if (!$threads) {
            return [];
        } else {
            switch ($type) {
                case "Direct":
                    $messages = $this->parseThreadsDirectMessages($user, $threads);
                break;
                case "Carpool":
                    $messages = $this->parseThreadsCarpoolMessages($user, $threads);
                break;
            }
            return $messages;
        }
    }
    
    public function parseThreadsDirectMessages(User $user, array $threads)
    {
        $messages = [];
        foreach ($threads as $message) {
            // To Do : We support only one recipient at this time...
            $currentMessage = [
                'idMessage' => $message->getId(),
                'idRecipient' => ($user->getId() === $message->getUser('user')->getId()) ? $message->getRecipients()[0]->getUser('user')->getId() : $message->getUser('user')->getId(),
                'avatarsRecipient' => ($user->getId() === $message->getUser('user')->getId()) ? $message->getRecipients()[0]->getUser('user')->getAvatars()[0] : $message->getUser('user')->getAvatars()[0],
                'givenName' => ($user->getId() === $message->getUser('user')->getId()) ? $message->getRecipients()[0]->getUser('user')->getGivenName() : $message->getUser('user')->getGivenName(),
                'shortFamilyName' => ($user->getId() === $message->getUser('user')->getId()) ? $message->getRecipients()[0]->getUser('user')->getShortFamilyName() : $message->getUser('user')->getShortFamilyName(),
                'date' => ($message->getLastMessage()===null) ? $message->getCreatedDate() : $message->getLastMessage()->getCreatedDate(),
                'selected' => false
            ];

            $messages[] = $currentMessage;
        }
        // Sort with the last message received first
        usort($messages, array($this, 'sortThread'));
        return $messages;
    }


    public static function sortThread($a, $b)
    {
        if ($a['date'] == $b['date']) {
            return 0;
        }
        return ($a['date'] < $b['date']) ? 1 : -1;
    }

    public function parseThreadsCarpoolMessages(User $user, array $threads)
    {
        $messages = [];


        foreach ($threads as $ask) {
            $askHistories = $this->askHistoryRepository->findLastAskHistory($ask);
            
            // Only the Ask with at least one AskHistory
            // Only one-way or outward of a round trip.
            if (count($askHistories)>0 && ($ask->getType()==1 || $ask->getType()==2)) {
                $askHistory = $askHistories[0];


                $message = $askHistory->getMessage();

                $currentThread = [
                    'idAskHistory'=>$askHistory->getId(),
                    'idAsk'=>$ask->getId(),
                    'idRecipient' => ($user->getId() === $ask->getUser('user')->getId()) ? $ask->getUserRelated()->getId() : $ask->getUser('user')->getId(),
                    'avatarsRecipient' => ($user->getId() === $ask->getUser('user')->getId()) ? $ask->getUserRelated()->getAvatars()[0] : $ask->getUser('user')->getAvatars()[0],
                    'givenName' => ($user->getId() === $ask->getUser('user')->getId()) ? $ask->getUserRelated()->getGivenName() : $ask->getUser('user')->getGivenName(),
                    'shortFamilyName' => ($user->getId() === $ask->getUser('user')->getId()) ? $ask->getUserRelated()->getShortFamilyName() : $ask->getUser('user')->getShortFamilyName(),
                    'date' => ($message===null) ? $askHistory->getCreatedDate() : $message->getCreatedDate(),
                    'selected' => false
                ];

                // The message id : the one linked to the current askHistory or we try to find the last existing one
                $idMessage = -99;
                if ($message !== null) {
                    ($message->getMessage()!==null) ? $idMessage = $message->getMessage()->getId() :  $idMessage = $message->getId();
                } else {
                    $formerAskHistory = $this->askHistoryRepository->findLastAskHistoryWithMessage($ask);
                    if (count($formerAskHistory)>0 && $formerAskHistory[0]->getMessage()) {
                        if ($formerAskHistory[0]->getMessage()->getMessage()) {
                            $idMessage = $formerAskHistory[0]->getMessage()->getMessage()->getId();
                        } else {
                            $idMessage = $formerAskHistory[0]->getMessage()->getId();
                        }
                    }
                }
                $currentThread['idMessage'] = $idMessage;

                $waypoints = $ask->getMatching()->getWaypoints();
                $criteria = $ask->getCriteria();
                $currentThread["carpoolInfos"] = [
                    "askHistoryId" => $askHistory->getId(),
                    "origin" => $waypoints[0]->getAddress()->getAddressLocality(),
                    "destination" => $waypoints[count($waypoints)-1]->getAddress()->getAddressLocality(),
                    "criteria" => [
                        "frequency" => $criteria->getFrequency(),
                        "fromDate" => $criteria->getFromDate(),
                        "fromTime" => $criteria->getFromTime(),
                        "monCheck" => $criteria->isMonCheck(),
                        "tueCheck" => $criteria->isTueCheck(),
                        "wedCheck" => $criteria->isWedCheck(),
                        "thuCheck" => $criteria->isThuCheck(),
                        "friCheck" => $criteria->isFriCheck(),
                        "satCheck" => $criteria->isSatCheck(),
                        "sunCheck" => $criteria->isSunCheck()
                    ]
                ];

                $messages[] = $currentThread;
            }
        }

        // Sort with the last message received first
        usort($messages, array($this, 'sortThread'));
        return $messages;
    }

    public function getThreadsDirectMessages(User $user): array
    {
        return $this->getThreadsMessages($user, "Direct");
    }

    public function getThreadsCarpoolMessages(User $user): array
    {
        return $this->getThreadsMessages($user, "Carpool");
    }
    
    /**
       * User password change request.
       *
       * @param User $user
       * @return Response
       */
    public function updateUserPasswordRequest(User $data)
    {
        // Get the user
        $user = $this->userRepository->findOneBy(["email"=>$data->getEmail()]);
        
        if (!is_null($user)) {
            $datetime = new DateTime();
            $time = $datetime->getTimestamp();
            // encoding of the password
            $pwdToken = hash("sha256", $user->getEmail() . rand() . $time . rand() . $user->getSalt());
            $user->setPwdToken($pwdToken);
            // update of the geotoken
            $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
            $user->setGeoToken($geoToken);
            // persist the user
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            // dispatch en event
            $event = new UserPasswordChangeAskedEvent($user);
            $this->eventDispatcher->dispatch($event, UserPasswordChangeAskedEvent::NAME);
            return $user;
        }
        return new JsonResponse();
    }
 
    /**
       * User password change confirmation.
       *
       * @param User $user
       * @return Response
       */
    public function updateUserPassword(User $data)
    {
        $user = $this->userRepository->findOneBy(["pwdToken"=>$data->getPwdToken()]);
        if (!is_null($user)) {
            $user->setPassword($data->getPassword());
            // persist the user
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            // dispatch en event
            $event = new UserPasswordChangedEvent($user);
            $this->eventDispatcher->dispatch($event, UserPasswordChangedEvent::NAME);
            // return the user
            return $user;
        }
        return new JsonResponse();
    }

    /**
     * Get user alert preferences
     *
     * @param User $user
     * @return User
     */
    public function getAlerts(User $user)
    {
        // if no alerts are detected we create them
        if (count($user->getUserNotifications()) == 0) {
            $user = $this->createAlerts($user);
        }
        $alerts = [];
        $actions = [];
        // first pass to get the actions
        foreach ($user->getUserNotifications() as $userNotification) {
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                // check telephone for sms
                continue;
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && is_null($user->getIosAppId()) && is_null($user->getAndroidAppId())) {
                // check apps for push
                continue;
            }
            if (!in_array($userNotification->getNotification()->getAction()->getName(), $alerts)) {
                $alerts[$userNotification->getNotification()->getAction()->getPosition()] = [
                    'action' => $userNotification->getNotification()->getAction()->getName(),
                    'alert' => []
                ];
                $actions[$userNotification->getNotification()->getAction()->getId()] = $userNotification->getNotification()->getAction()->getPosition();
            }
        }
        ksort($alerts);
        // second pass to get the media
        $media = [];
        foreach ($user->getUserNotifications() as $userNotification) {
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                // check telephone for sms
                continue;
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && is_null($user->getIosAppId()) && is_null($user->getAndroidAppId())) {
                // check apps for push
                continue;
            }
            $media[$userNotification->getNotification()->getAction()->getId()][$userNotification->getNotification()->getPosition()] = [
                'medium' => $userNotification->getNotification()->getMedium()->getId(),
                'id' => $userNotification->getId(),
                'active' => $userNotification->isActive()
            ];
        }
        // third pass to order media
        $mediaOrdered = [];
        foreach ($media as $actionID => $unorderedMedia) {
            $copy = $unorderedMedia;
            ksort($copy);
            $mediaOrdered[$actionID] = $copy;
        }
        // fourth pass to link media to actions
        foreach ($mediaOrdered as $actionID => $orderedMedia) {
            $alerts[$actions[$actionID]]['alert'] = $orderedMedia;
        }
        $user->setAlerts($alerts);
        return $user;
    }

    /**
     * Create user alerts
     *
     * @param User $user        The user to treat
     * @param boolean $perist   Persist immediately (false for mass import)
     * @return User
     */
    public function createAlerts(User $user, $persist=true)
    {
        $notifications = $this->notificationRepository->findUserEditable();
        foreach ($notifications as $notification) {
            $userNotification = new UserNotification();
            $userNotification->setNotification($notification);
            $userNotification->setActive($notification->isUserActiveDefault());
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                // check telephone for sms
                $userNotification->setActive(false);
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && is_null($user->getIosAppId()) && is_null($user->getAndroidAppId())) {
                // check apps for push
                $userNotification->setActive(false);
            }
            $user->addUserNotification($userNotification);
        }
        $this->entityManager->persist($user);
        if ($persist) {
            $this->entityManager->flush();
        }
        return $user;
    }

    /**
     * Update user alerts
     *
     * @param User $user
     * @return void
     */
    public function updateAlerts(User $user)
    {
        if (!is_null($user->getAlerts())) {
            foreach ($user->getAlerts() as $id => $active) {
                if ($userNotification = $this->userNotificationRepository->find($id)) {
                    $userNotification->setActive($active);
                    $this->entityManager->persist($userNotification);
                }
            }
            $this->entityManager->flush();
        }
        return $this->getAlerts($user);
    }

    /**
     * set sms notification to active when phone is validated
     *
     * @param User $user
     * @return void
     */
    public function activateSmsNotification(User $user)
    {
        $userNotifications = $this->userNotificationRepository->findUserNotifications($user->getId());
        foreach ($userNotifications as $userNotification) {
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS) {
                // check telephone for sms
                $userNotification->setActive(true);
                $userNotification->setUser($user);
                $this->entityManager->persist($userNotification);
            }
        }
        $this->entityManager->flush();
        return $user;
    }

    /**
    * set sms notification to non active when phone change or is removed
    *
    * @param User $user
    * @return void
    */
    public function deActivateSmsNotification(User $user)
    {
        $userNotifications = $this->userNotificationRepository->findUserNotifications($user->getId());
        foreach ($userNotifications as $userNotification) {
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS) {
                $userNotification->setActive(false);
                $userNotification->setUser($user);
                $this->entityManager->persist($userNotification);
            }
        }
        $this->entityManager->flush();
        return $user;
    }

    /**
     * Generate a validation token
     * (Ajax)
     *
     * @param User $user
     * @return void
     */
    public function generatePhoneToken(User $user)
    {
        // Generate the token
        $phoneToken= strval(mt_rand(1000, 9999));
        $user->setPhoneToken($phoneToken);
        // dispatch the event
        $event = new UserGeneratePhoneTokenAskedEvent($user);
        $this->eventDispatcher->dispatch(UserGeneratePhoneTokenAskedEvent::NAME, $event);
        // Persist user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }


    /**
     * Anonymise the user
     *
     */
    public function anonymiseUser(User $user)
    {

        // L'utilisateur à posté des annonces de covoiturages -> on les supprimes
        // User create ad : we delete them
        foreach ($user->getProposals() as $proposal) {
            foreach ($proposal->getMatchingRequests() as $matching) {
                //Check if there is ask on a proposal -> event for notifications
                foreach ($matching->getAsks() as $ask) {
                    $event = new UserDeleteAccountWasDriverEvent($ask, $user->getId());
                    $this->eventDispatcher->dispatch(UserDeleteAccountWasDriverEvent::NAME, $event);
                }
            }
            //There is offers on the proposal -> we delete proposal + send email to passengers
            foreach ($proposal->getMatchingOffers() as $matching) {
                //TODO libérer les places sur les annonces réservées
                foreach ($matching->getAsks() as $ask) {
                    $event = new UserDeleteAccountWasPassengerEvent($ask, $user->getId());
                    $this->eventDispatcher->dispatch(UserDeleteAccountWasPassengerEvent::NAME, $event);
                }
            }
            //Set user at null and private on the proposal : we keep info for message, proposal cant be found
            $proposal->setPrivate(1);
        }

        //Anonymise content of message with a key
        foreach ($user->getMessages() as $message) {
            $message->setText('@mobicoop2020Message_supprimer');
        }

        return $this->setUserAtNull($user);
    }

    private function setUserAtNull(User $user)
    {
        $datenow = new DateTime();
        //Replace all mandatory value by default value or token
        $user->setEmail(uniqid().'@'.uniqid().'.fr');
        $user->setGender(3);
        $user->setStatus(3);
        $user->setCreatedDate($datenow);
        $user->setValidatedDate($datenow);
        $user->setPhoneDisplay(1);

        //Replace all value nullable by null
        $user->setGivenName(null);
        $user->setFamilyName(null);
        $user->setPassword(null);
        $user->setGivenName(null);
        $user->setNationality(null);
        $user->setBirthDate(null);
        $user->setTelephone(null);
        $user->setAnyRouteAsPassenger(null);
        $user->setMultiTransportMode(null);
        $user->setMaxDetourDistance(null);
        $user->setMaxDetourDuration(null);
        $user->setPwdToken(null);
        $user->setGeoToken(null);
        $user->setLanguage(null);
        $user->setPwdToken(null);
        $user->setValidatedDateToken(null);
        $user->setFacebookId(null);
        $user->setSmoke(null);
        $user->setMusic(null);
        $user->setMusicFavorites(null);
        $user->setChat(null);
        $user->setChatFavorites(null);
        $user->setNewsSubscription(null);
        $user->setPhoneToken(null);
        $user->setIosAppId(null);
        $user->setAndroidAppId(null);
        $user->setPhoneValidatedDate(null);


        // $this->entityManager->persist($user);
        // $this->entityManager->flush();
        die;
        $this->checkIfUserHaveImages($user);
        $this->checkIfUserIsInCommunity($user);

        return array();
    }


    //Check if the delete account have image, and delete them
    // deleteBase -> delete the base image and remove the entry
    private function checkIfUserHaveImages(User $user)
    {
        foreach ($user->getImages() as $image) {
            $this->imageManager->deleteVersions($image);
            $this->imageManager->deleteBase($image);
        }
    }


    //Check if the delete account is in community, and delete the link between
    private function checkIfUserIsInCommunity(User $user)
    {
        $myCommunities = $this->communityUserRepository->findBy(array('user'=>$user));

        foreach ($myCommunities as $myCommunity) {
            $this->entityManager->remove($myCommunity);
        }
        $this->entityManager->flush();
    }


    //Get asks for an user -> use for check if a ask is already done on a proposal
    public function getAsks(User $user): array
    {
        if ($asks = $this->askRepository->findAskByAsker($user)) {
            $arrayAsks = array();
            foreach ($asks as $ask) {
                $arrayAsks['offers'][] = $ask->getMatching()->getProposalOffer()->getId();
                $arrayAsks['request'][] = $ask->getMatching()->getProposalRequest()->getId();
            }
            return $arrayAsks;
        }
        return [];
    }

    public function checkValidatedDateToken($data)
    {
        $userFound = $this->userRepository->findOneBy(["validatedDateToken"=>$data->getValidatedDateToken()]);
        
        if (!is_null($userFound)) {
            if ($data->getEmail()===$userFound->getEmail()) {
                // User found by token match with the given email. We update de validated date, persist, then return the user found
                $userFound->setValidatedDate(new \Datetime());
                $this->entityManager->persist($userFound);
                $this->entityManager->flush();
                return $userFound;
            } else {
                // User found by token doesn't match with the given email. We return nothing.
                return new JsonResponse();
            }
        } else {
            // No user found. We return nothing.
            return new JsonResponse();
        }
        return new JsonResponse();
    }

    public function checkPhoneToken($data)
    {
        $userFound = $this->userRepository->findOneBy(["phoneToken"=>$data->getPhoneToken()]);
        
        if (!is_null($userFound)) {
            if ($data->getTelephone()===$userFound->getTelephone()) {
                // User found by token match with the given Telephone. We update de validated date, persist, then return the user found
                $userFound->setPhoneValidatedDate(new \Datetime());
                $this->entityManager->persist($userFound);
                $this->entityManager->flush();
                return $userFound;
            } else {
                // User found by token doesn't match with the given telephone. We return nothing.
                return new JsonResponse();
            }
        } else {
            // No user found. We return nothing.
            return new JsonResponse();
        }
        return new JsonResponse();
    }

    public function unsubscribeFromEmail(User $user, $lang='fr_FR')
    {
        $this->translator->setLocale($lang);

        $messageUnsubscribe = $this->translator->trans('unsubscribeEmailAlertFront', ['instanceName' => $_ENV['EMAILS_PLATFORM_NAME']]);

        $user->setNewsSubscription(0);
        $user->setUnsubscribeDate(new \Datetime());

        $user->setUnsubscribeMessage($messageUnsubscribe);

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return $user;
    }
}
