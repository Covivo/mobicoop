<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Carpool\Entity\Ask;
use App\Event\Entity\Event;
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
use App\Auth\Repository\AuthItemRepository;
use App\Carpool\Service\ProofManager;
use App\Communication\Entity\Message;
use App\Community\Repository\CommunityRepository;
use App\Community\Entity\CommunityUser;
use App\User\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Communication\Repository\MessageRepository;
use App\Communication\Repository\NotificationRepository;
use App\Communication\Service\InternalMessageManager;
use App\Community\Entity\Community;
use App\Payment\Service\PaymentDataProvider;
use App\Solidary\Entity\Operate;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\Structure;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Event\SolidaryUserCreatedEvent;
use App\Solidary\Event\SolidaryUserUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Repository\UserNotificationRepository;
use App\User\Entity\UserNotification;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
use App\User\Event\UserGeneratePhoneTokenAskedEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User\Event\UserUpdatedSelfEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\User\Exception\UserDeleteException;
use App\Payment\Ressource\BankAccount;
use App\User\Entity\SsoUser;
use App\User\Ressource\ProfileSummary;
use App\User\Ressource\PublicProfile;

/**
 * User manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserManager
{
    private $entityManager;
    private $imageManager;
    private $authItemRepository;
    private $communityRepository;
    private $communityUserRepository;
    private $messageRepository;
    private $askRepository;
    private $askHistoryRepository;
    private $notificationRepository;
    private $userNotificationRepository;
    private $userRepository;
    private $proofManager;
    private $solidaryRepository;
    private $structureRepository;
    private $logger;
    private $eventDispatcher;
    private $encoder;
    private $translator;
    private $security;
    private $paymentProvider;
    private $blockManager;
    private $internalMessageManager;
    private $reviewManager;

    // Default carpool settings
    private $chat;
    private $music;
    private $smoke;

    private $fakeFirstMail;
    private $fakeFirstToken;
    private $domains;
    private $profile;
    private $passwordTokenValidity;

    /**
        * Constructor.
        *
        * @param EntityManagerInterface $entityManager
        * @param LoggerInterface $logger
        */
    public function __construct(
        EntityManagerInterface $entityManager,
        ImageManager $imageManager,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        AuthItemRepository $authItemRepository,
        CommunityRepository $communityRepository,
        MessageRepository $messageRepository,
        UserPasswordEncoderInterface $encoder,
        NotificationRepository $notificationRepository,
        UserNotificationRepository $userNotificationRepository,
        AskHistoryRepository $askHistoryRepository,
        AskRepository $askRepository,
        UserRepository $userRepository,
        ProofManager $proofManager,
        $chat,
        $smoke,
        $music,
        CommunityUserRepository $communityUserRepository,
        TranslatorInterface $translator,
        Security $security,
        SolidaryRepository $solidaryRepository,
        StructureRepository $structureRepository,
        string $fakeFirstMail,
        string $fakeFirstToken,
        PaymentDataProvider $paymentProvider,
        BlockManager $blockManager,
        InternalMessageManager $internalMessageManager,
        ReviewManager $reviewManager,
        array $domains,
        array $profile,
        $passwordTokenValidity
    ) {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
        $this->logger = $logger;
        $this->authItemRepository = $authItemRepository;
        $this->communityRepository = $communityRepository;
        $this->communityUserRepository = $communityUserRepository;
        $this->messageRepository = $messageRepository;
        $this->askRepository = $askRepository;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->eventDispatcher = $dispatcher;
        $this->encoder = $encoder;
        $this->translator = $translator;
        $this->security = $security;
        $this->notificationRepository = $notificationRepository;
        $this->userNotificationRepository = $userNotificationRepository;
        $this->userRepository = $userRepository;
        $this->proofManager = $proofManager;
        $this->solidaryRepository = $solidaryRepository;
        $this->structureRepository = $structureRepository;
        $this->chat = $chat;
        $this->music = $music;
        $this->smoke = $smoke;
        $this->fakeFirstMail = $fakeFirstMail;
        $this->fakeFirstToken = $fakeFirstToken;
        $this->domains = $domains;
        $this->paymentProvider = $paymentProvider;
        $this->blockManager = $blockManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->reviewManager = $reviewManager;
        $this->profile = $profile;
        $this->passwordTokenValidity = $passwordTokenValidity;
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
     * Get a user by its email.
     *
     * @param string $email The email to find
     * @return User|null    The user found
     */
    public function getUserByEmail(string $email)
    {
        return $this->userRepository->findOneBy(["email"=>$email]);
    }


    /**
     * Check if an email is already used by someone; returns a code
     *
     * @param string $email The email to check
     * @return string       The code
     */
    public function checkEmail(string $email)
    {
        //Email already exist in db
        if ($this->userRepository->findOneBy(["email"=>$email])) {
            return 'email-exist';
        }

        foreach ($this->domains as $name => $domain) {
            if (explode("@", $email)[1] == $domain) {
                return 'authorized';
            }
        }
        
        return implode(", ", $this->domains);
    }

    /**
     * Check if a password token and password token date exist
     *
     * @param string $pwdToken The password token to check
     * @return string|null The checked token or null if token invalid
     */
    public function checkPasswordToken(string $pwdToken)
    {
        if ($user=$this->userRepository->findOneBy(["pwdToken"=>$pwdToken])) {
            if ((time() - (int)$user->getPwdTokenDate()->getTimestamp()) > $this->passwordTokenValidity) {
                return null;
            }
            return $pwdToken;
        }
        return null;
    }

    /**
     * Get a user by security token.
     *
     * @return User|null
     */
    public function getMe()
    {
        $user = $this->userRepository->findOneBy(["email"=>$this->security->getUser()->getUsername()]);
        
        return $user;
    }

    /**
     * Registers a user.
     *
     * @param User      $user               The user to register
     * @param boolean   $encodePassword     True to encode password
     * @return User     The user created
     */
    public function registerUser(User $user, bool $encodePassword=true)
    {
        $user = $this->prepareUser($user, $encodePassword);
 
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

        if (!is_null($user->getCommunityId())) {
            $communityUser = new CommunityUser();
            $communityUser->setUser($user);
            $communityUser->setCommunity($this->communityRepository->find($user->getCommunityId()));
            $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MEMBER);
            $this->entityManager->persist($communityUser);
            $this->entityManager->flush();
        }

        // return the user
        return $user;
    }


    /**
     * Set the default availabilities of a SolidaryUser
     * If no availabilitie already given, we take the structure default
     * For the days check, if there is no indication, we consider the user available
     *
     * @param SolidaryUser $solidaryUser    The SolidaryUser
     * @param Structure $structure          The Structure (if there is no Structure, we take the admin's one)
     * @return SolidaryUser
     */
    public function setDefaultSolidaryUserAvailabilities(SolidaryUser $solidaryUser, Structure $structure=null): SolidaryUser
    {
        $solidaryUserstructure = null;
        if (!is_null($structure)) {
            // A structure is given. We're looking for the solidaryUserStructure between this structure and the SolidaryUser
            $solidaryUserstructures = $solidaryUser->getSolidaryUserStructures();
            foreach ($solidaryUserstructures as $currentSolidaryUserstructure) {
                if ($currentSolidaryUserstructure->getStructure()->getId() == $structure->getId()) {
                    $solidaryUserstructure = $currentSolidaryUserstructure;
                    break;
                }
            }
        } else {
            // No structure given. We take the admin's one
            $structures = $this->security->getUser()->getSolidaryStructures();
            if (is_array($structures) && isset($structures[0])) {
                $solidaryUserstructure = $structures[0];
            }
        }

        if (is_null($solidaryUserstructure)) {
            throw new SolidaryException(SolidaryException::NO_STRUCTURE);
        }

        // Times
        if ($solidaryUser->getMMinTime()=="") {
            $solidaryUser->setMMinTime($solidaryUserstructure->getStructure()->getMMinTime());
        }
        if ($solidaryUser->getMMaxTime()=="") {
            $solidaryUser->setMMaxTime($solidaryUserstructure->getStructure()->getMMaxTime());
        }
        if ($solidaryUser->getAMinTime()=="") {
            $solidaryUser->setAMinTime($solidaryUserstructure->getStructure()->getAMinTime());
        }
        if ($solidaryUser->getAMaxTime()=="") {
            $solidaryUser->setAMaxTime($solidaryUserstructure->getStructure()->getAMaxTime());
        }
        if ($solidaryUser->getEMinTime()=="") {
            $solidaryUser->setEMinTime($solidaryUserstructure->getStructure()->getEMinTime());
        }
        if ($solidaryUser->getEMaxTime()=="") {
            $solidaryUser->setEMaxTime($solidaryUserstructure->getStructure()->getEMaxTime());
        }

        // Days
        if ($solidaryUser->hasMMon()!==false) {
            $solidaryUser->setMMon(true);
        }
        if ($solidaryUser->hasAMon()!==false) {
            $solidaryUser->setAMon(true);
        }
        if ($solidaryUser->hasEMon()!==false) {
            $solidaryUser->setEMon(true);
        }
        if ($solidaryUser->hasMTue()!==false) {
            $solidaryUser->setMTue(true);
        }
        if ($solidaryUser->hasATue()!==false) {
            $solidaryUser->setATue(true);
        }
        if ($solidaryUser->hasETue()!==false) {
            $solidaryUser->setETue(true);
        }
        if ($solidaryUser->hasMWed()!==false) {
            $solidaryUser->setMWed(true);
        }
        if ($solidaryUser->hasAWed()!==false) {
            $solidaryUser->setAWed(true);
        }
        if ($solidaryUser->hasEWed()!==false) {
            $solidaryUser->setEWed(true);
        }
        if ($solidaryUser->hasMThu()!==false) {
            $solidaryUser->setMThu(true);
        }
        if ($solidaryUser->hasAThu()!==false) {
            $solidaryUser->setAThu(true);
        }
        if ($solidaryUser->hasEThu()!==false) {
            $solidaryUser->setEThu(true);
        }
        if ($solidaryUser->hasMFri()!==false) {
            $solidaryUser->setMFri(true);
        }
        if ($solidaryUser->hasAFri()!==false) {
            $solidaryUser->setAFri(true);
        }
        if ($solidaryUser->hasEFri()!==false) {
            $solidaryUser->setEFri(true);
        }
        if ($solidaryUser->hasMSat()!==false) {
            $solidaryUser->setMSat(true);
        }
        if ($solidaryUser->hasASat()!==false) {
            $solidaryUser->setASat(true);
        }
        if ($solidaryUser->hasESat()!==false) {
            $solidaryUser->setESat(true);
        }
        if ($solidaryUser->hasMSun()!==false) {
            $solidaryUser->setMSun(true);
        }
        if ($solidaryUser->hasASun()!==false) {
            $solidaryUser->setASun(true);
        }
        if ($solidaryUser->hasESun()!==false) {
            $solidaryUser->setESun(true);
        }
        return $solidaryUser;
    }

    /**
     * Prepare a user for registration : set default values
     *
     * @param User      $user               The user to prepare
     * @param boolean   $encodePassword     True to encode password
     * @return User     The prepared user
     */
    public function prepareUser(User $user, bool $encodePassword=false)
    {

        // We add the default roles we set in User Entity
        $authItem = $this->authItemRepository->find(User::ROLE_DEFAULT);
        $userAuthAssignment = new UserAuthAssignment();
        $userAuthAssignment->setAuthItem($authItem);
        $user->addUserAuthAssignment($userAuthAssignment);


        // No password given, we generate one
        if (is_null($user->getPassword())) {
            $user->setPassword($this->randomPassword());
        }
        
        if ($encodePassword) {
            $user->setClearPassword($user->getPassword());
            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
        }

        // default phone display : restricted
        $user->setPhoneDisplay(User::PHONE_DISPLAY_RESTRICTED);

        // Default carpool settings
        $user->setChat($this->chat);
        $user->setMusic($this->music);
        $user->setSmoke($this->smoke);

        // Create geotoken
        $user->setGeoToken($this->createToken($user));

        // Create token to validate inscription
        $user->setEmailToken($this->createToken($user));

        // Create token to unscubscribe from the instance news
        $user->setUnsubscribeToken($this->createToken($user));

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
        $user->setGeoToken($this->createToken($user));

        //we add/remove structures associated to user
        if (!is_null($user->getSolidaryStructures())) {
            // We initialize an arry with the ids of the user's structures
            $structuresIds = [];
            // we initialise the bool that indicate that we update structures at false
            $updateStructures = false;
            foreach ($user->getOperates() as $operate) {
                // we put in array the ids of the user's structures
                $structuresIds[] = $operate->getStructure()->getId();
            }
            // We initialize an arry with the ids of the user's new structures
            $newStructuresIds = [];
            foreach ($user->getSolidaryStructures() as $solidaryStructure) {
                if (!is_array($solidaryStructure)) {
                    continue;
                }
                // we set the boolean at true
                $updateStructures = true;
                // we put in array the ids of the user's new structures
                $newStructuresIds[] = $solidaryStructure['id'];
                // we add the new structures not present in the array of structures to the user
                if (!in_array($solidaryStructure['id'], $structuresIds)) {
                    $structure = $this->structureRepository->find($solidaryStructure['id']);
                    $operate = new Operate;
                    $operate->setStructure($structure);
                    $operate->setCreatedDate(new DateTime());
                    $operate->setUpdatedDate(new DateTime());
                    $user->addOperate($operate);
                }
            }
            // if we delete all structures we pass an empty array with the user so we set the boolean at true
            if (empty($user->getSolidaryStructures())) {
                $updateStructures = true;
            }
            // we execute only if we have updated the structures
            if ($updateStructures) {
              
                // we remove the structures not present in  the new array of structures
                foreach ($user->getOperates() as $operate) {
                    if (!in_array($operate->getStructure()->getId(), $newStructuresIds)) {
                        $user->removeOperate($operate);
                        $this->entityManager->remove($operate);
                    }
                }
            }
        }
       
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
     * Encode a password for a user
     *
     * @param User $user        The user
     * @param string $password  The password to encode
     * @return string           The encoded password
     */
    public function encodePassword(User $user, string $password)
    {
        return $this->encoder->encodePassword($user, $password);
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
        if (count($user->getUserAuthAssignments()) == 0) {
            // default role : user registered full
            $authItem = $this->authItemRepository->find(AuthItem::ROLE_USER_REGISTERED_FULL);
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
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
    public function getThreadsMessages(User $user, $type=Message::TYPE_DIRECT): array
    {
        $threads = [];
        if ($type==Message::TYPE_DIRECT) {
            $threads = $this->messageRepository->findThreadsDirectMessages($user);
        } elseif ($type==Message::TYPE_CARPOOL) {
            $threads = $this->askRepository->findAskByUser($user, Ask::ASKS_WITHOUT_SOLIDARY);
        } elseif ($type==Message::TYPE_SOLIDARY) {
            $threads = $this->askRepository->findAskByUser($user, Ask::ASKS_WITH_SOLIDARY);
        } else {
            return [];
        }

        if (!$threads) {
            return [];
        } else {
            switch ($type) {
                case Message::TYPE_DIRECT:
                    $messages = $this->parseThreadsDirectMessages($user, $threads);
                break;
                case Message::TYPE_CARPOOL:
                case Message::TYPE_SOLIDARY:
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

            // We check if the user and it's carpooler are involved in a block
            $user2 = ($user->getId() === $message->getRecipients()[0]->getUser()->getId() ? $message->getUser()->getId() : $message->getRecipients()[0]->getUser()->getId());
            $blocks = $this->blockManager->getInvolvedInABlock($user, $user2);
            $currentMessage['blockerId'] = null;
            if (is_array($blocks) && count($blocks)>0) {
                foreach ($blocks as $block) {
                    if ($block->getUser()->getId() == $user->getId()) {
                        // The blocker is the current User
                        $currentMessage['blockerId'] = $user->getId();
                        break;
                    }
                    $currentMessage['blockerId'] = $user2->getId();
                }
            }
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
                    if ($formerAskHistory = $this->askHistoryRepository->findLastAskHistoryWithMessage($ask)) {
                        if ($formerAskHistory->getMessage()->getMessage()) {
                            $idMessage = $formerAskHistory->getMessage()->getMessage()->getId();
                        } else {
                            $idMessage = $formerAskHistory->getMessage()->getId();
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

                // We check if the user and it's carpooler are involved in a block
                $user2 = ($user->getId() === $ask->getUserRelated()->getId() ? $ask->getUser() : $ask->getUserRelated());
                $blocks = $this->blockManager->getInvolvedInABlock($user, $user2);
                $currentThread['blockerId'] = null;
                if (is_array($blocks) && count($blocks)>0) {
                    foreach ($blocks as $block) {
                        if ($block->getUser()->getId() == $user->getId()) {
                            // The blocker is the current User
                            $currentThread['blockerId'] = $user->getId();
                            break;
                        }
                        $currentThread['blockerId'] = $user2->getId();
                    }
                }


                $messages[] = $currentThread;
            }
        }

        // Sort with the last message received first
        usort($messages, array($this, 'sortThread'));
        return $messages;
    }

    public function getThreadsDirectMessages(User $user): array
    {
        return $this->getThreadsMessages($user, Message::TYPE_DIRECT);
    }

    public function getThreadsCarpoolMessages(User $user): array
    {
        return $this->getThreadsMessages($user, Message::TYPE_CARPOOL);
    }

    public function getThreadsSolidaryMessages(User $user): array
    {
        return $this->getThreadsMessages($user, Message::TYPE_SOLIDARY);
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
            // Create a password token
            $user->setPwdToken($this->createToken($user));
            // update of the geotoken
            $user->setGeoToken($this->createToken($user));
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
            // we reset tokens
            $user->setPwdTokenDate(null);
            $user->setPwdToken(null);
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
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && !$user->hasMobile()) {
                // check apps for push
                continue;
            }
            if (!in_array($userNotification->getNotification()->getAction()->getName(), $alerts)) {
                $alerts[$userNotification->getNotification()->getAction()->getId()] = [
                    'action' => $userNotification->getNotification()->getAction()->getName(),
                    'alert' => []
                ];
                $actions[$userNotification->getNotification()->getAction()->getId()] = $userNotification->getNotification()->getAction()->getId();
            }
        }
        ksort($alerts);
        // second pass to get the media
        $media = [];
        foreach ($user->getUserNotifications() as $userNotification) {
            if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                // check telephone for sms
                continue;
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && !$user->hasMobile()) {
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
            } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && !$user->hasMobile()) {
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
     * Generate a phone token
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
     * Delete the user
     *
     */
    public function deleteUser(User $user)
    {
        // Check if the user is not the author of an event that is still valid
        foreach ($user->getEvents() as $event) {
            if (($event->getUser()->getId() == $user->getId()) && ($event->getToDate() >= new \DateTime())) {
                // to do throw exception
                throw new UserDeleteException("An Event of the user is still runing");
            }
        }
        // Check if the user is not the author of a community
        foreach ($user->getCommunityUsers() as $communityUser) {
            if ($communityUser->getCommunity()->getUser()->getId() == $user->getId()) {
                // todo throw execption
                throw new UserDeleteException("The user is a community owner");
            } else {
                //delete all community subscriptions
                $this->deleteCommunityUsers($user);
            }
        }
        // check if the user have pending proofs, and remove the links
        $this->proofManager->removeProofs($user);
                    
        // We check if the user have ads.
        // If he have ads we check if a carpool is initiated if yes we send an email to the carpooler
        foreach ($user->getProposals() as $proposal) {
            if ($proposal->isPrivate()) {
                continue;
            }
            foreach ($proposal->getMatchingRequests() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    // todo : find why class of $ask can be a proxy of Ask class
                    if (get_class($ask) !== Ask::class) {
                        continue;
                    }
                    $event = new UserDeleteAccountWasDriverEvent($ask, $user->getId());
                    $this->eventDispatcher->dispatch(UserDeleteAccountWasDriverEvent::NAME, $event);
                }
            }
            foreach ($proposal->getMatchingOffers() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    // todo : find why class of $ask can be a proxy of Ask class
                    if (get_class($ask) !== Ask::class) {
                        continue;
                    }
                    $event = new UserDeleteAccountWasPassengerEvent($ask, $user->getId());
                    $this->eventDispatcher->dispatch(UserDeleteAccountWasPassengerEvent::NAME, $event);
                }
            }
            $this->entityManager->remove($proposal);
        }
        // we remove all user's addresses
        foreach ($user->getAddresses() as $address) {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        }
        $this->deleteUserImages($user);

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    //Delete images associated to the user
    // deleteBase -> delete the base image and remove the entry
    private function deleteUserImages(User $user)
    {
        foreach ($user->getImages() as $image) {
            $this->imageManager->deleteVersions($image);
            $this->imageManager->deleteBase($image);
        }
    }

    //Delete link between the delete account and his communities
    private function deleteCommunityUsers(User $user)
    {
        $myCommunityUsers = $this->communityUserRepository->findBy(array('user'=>$user));

        foreach ($myCommunityUsers as $myCommunityUser) {
            $this->entityManager->remove($myCommunityUser);
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

    /**
     * Update the activity of an user
     *
     * @param User      $user               The user to update
     */
    public function updateActivity(User $user)
    {
        $user->setLastActivityDate(new DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Get the solidaries of a user
     *
     * @param int $userId    The user id we want to get the solidaries
     * @return User|null
     */
    public function getSolidaries(int $userId)
    {
        $user = $this->userRepository->find($userId);
        if (empty($user)) {
            throw new SolidaryException(SolidaryException::UNKNOWN_USER);
        }

        $solidaries = $this->solidaryRepository->findByUser($user);
        if (!empty($solidaries)) {
            $user->setSolidaries($solidaries);
        }

        return $user;
    }

    /**
     * Get the structures of a user
     *
     * @param int $userId    The user id we want to get the structures
     * @return User|null
     */
    public function getStructures(int $userId)
    {
        $user = $this->userRepository->find($userId);
        if (empty($user)) {
            throw new SolidaryException(SolidaryException::UNKNOWN_USER);
        }

        $structures = $this->structureRepository->findByUser($user);
        if (!empty($structures)) {
            $user->setStructures($structures);
        }

        return $user;
    }

    /**
     * Create a random token for a user.
     *
     * @param User $user    The user
     * @return string   The token generated
     */
    private function createToken(User $user)
    {
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        // note : we replace the '/' by an arbitrary 'a' as the token could be used in a url

        if ($user->getEmail() == $this->fakeFirstMail) {
            return $this->fakeFirstToken;
        } else {
            return $this->sanitizeString(hash("sha256", $user->getEmail() . rand() . $time . rand() . $user->getSalt()));
        }
    }

    /**
     * Sanitize a string by replacing non-letter or digits by letters or digits.
     *
     * @param string $string    The string to sanitize
     * @return string The sanitized string
     */
    private function sanitizeString(string $string)
    {
        return preg_replace('/[^\w]/', $this->getRandomChar(), $string);
    }

    /**
     * Get a random letter or digit
     *
     * @return string A letter or digit
     */
    private function getRandomChar()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        return $seed[array_rand($seed)];
    }

    /**
     * Check if an user $user have a specific AuthItem $authItem
     *
     * @param User $user    The user to check
     * @param AuthItem $AuthItem    The auth item to check
     * @return boolean True if user have item
     */
    public function checkUserHaveAuthItem(User $user, AuthItem $authItem)
    {
        foreach ($user->getUserAuthAssignments() as $oneItem) {
            if ($oneItem->getAuthItem() == $authItem) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a random string
     *
     * @param integer $length   The length of the string to generate
     * @return String   The generated string
     */
    public function randomString(int $length = 10)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $string = []; //remember to declare $string as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $string[] = $alphabet[$n];
        }
        return implode($string); //turn the array into a string
    }

    /**
     * Generate a sub email address
     *
     * @param string $email     The base email
     * @param integer $length   The length of the generated random string
     * @param string $glue      The string to add before the random string
     * @return string   The generated sub email address
     */
    public function generateSubEmail(string $email, int $length=10, string $glue='+')
    {
        $exploded = explode('@', $email);
        return $exploded[0] . $glue . $this->randomString($length) . '@' . $exploded[1];
    }

    /**
     * Get the payment profile (bankaccounts, wallets...) of the User
     *
     * @return User|null
     */
    public function getPaymentProfile(User $user=null)
    {
        if (is_null($user)) {
            $user = $this->userRepository->findOneBy(["email"=>$this->security->getUser()->getUsername()]);
        }
        $paymentProfiles = $this->paymentProvider->getPaymentProfiles($user);
        $bankAccounts = $wallets = [];
        foreach ($paymentProfiles as $paymentProfile) {
            if (!is_null($paymentProfile->getBankAccounts())) {
                foreach ($paymentProfile->getBankAccounts() as $bankaccount) {
                    /**
                     * @var BankAccount $bankaccount
                     */
                    
                    // We replace some characters in Iban and Bic by *
                    $iban = $bankaccount->getIban();
                    for ($i=4 ; $i<strlen($iban)-4 ; $i++) {
                        $iban[$i] = "*";
                    }
                    $bic = $bankaccount->getBic();
                    for ($i=2 ; $i<strlen($bic)-2 ; $i++) {
                        $bic[$i] = "*";
                    }
                    
                    $bankaccount->setIban($iban);
                    $bankaccount->setBic($bic);
                    
                    $bankAccounts[] = $bankaccount;
                }
            }
            if (!is_null($paymentProfile->getWallets())) {
                foreach ($paymentProfile->getWallets() as $wallet) {
                    $wallets[] = $wallet;
                }
            }
        }
        $user->setBankAccounts($bankAccounts);
        $user->setWallets($wallets);
        return $user;
    }
    
    /**
     * Generate a randomPassword
     *
     * @return string
     */
    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    
    /**
     * Return a User from a SsoUser
     * Existing user or a new one
     *
     * @param SsoUser $ssoUser
     * @return User|null
     */
    public function getUserFromSso(SsoUser $ssoUser): ?User
    {
        $user = $this->userRepository->findOneBy(['ssoId'=>$ssoUser->getSub(), 'ssoProvider'=>$ssoUser->getProvider()]);
        if (is_null($user)) {

            // check if a user with this email already exists
            $user = $this->userRepository->findOneBy(['email'=>$ssoUser->getEmail()]);
            if (!is_null($user)) {
                // We update the user with ssoId and ssoProvider and return it
                $user->setSsoId($ssoUser->getSub());
                $user->setSsoProvider($ssoUser->getProvider());
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $user;
            }

            // Create a new one
            $user = new User();
            $user->setSsoId($ssoUser->getSub());
            $user->setSsoProvider($ssoUser->getProvider());
            $user->setGivenName($ssoUser->getFirstname());
            $user->setFamilyName($ssoUser->getLastname());
            $user->setEmail($ssoUser->getEmail());

            // Gender
            switch ($ssoUser->getGender()) {
                case SsoUser::GENDER_MALE:$user->setGender(User::GENDER_MALE);break;
                case SsoUser::GENDER_FEMALE:$user->setGender(User::GENDER_FEMALE);break;
                default: $user->setGender(User::GENDER_OTHER);
            }

            if (trim($ssoUser->getBirthdate())!="") {
                $user->setBirthDate(DateTime::createFromFormat("Y-m-d", $ssoUser->getBirthdate()));
            }

            $user = $this->registerUser($user);
        }
        return $user;
    }

    /**
     * Get the profile summary of a User
     *
     * @param User $user   The User
     * @return ProfileSummary
     */
    public function getProfileSummary(User $user): ProfileSummary
    {
        $profileSummary = new ProfileSummary($user->getId());
        $profileSummary->setGivenName($user->getGivenName());
        $profileSummary->setShortFamilyName($user->getShortFamilyName());
        $profileSummary->setCreatedDate($user->getCreatedDate());
        $profileSummary->setLastActivityDate($user->getLastActivityDate());

        if ($user->getBirthDate()) {
            $profileSummary->setAge($user->getBirthDate()->diff(new \DateTime())->y);
        }

        $profileSummary->setPhoneDisplay($user->getPhoneDisplay());
        if ($user->getPhoneDisplay()==User::PHONE_DISPLAY_ALL) {
            $profileSummary->setTelephone($user->getTelephone());
        }
        if (is_array($user->getAvatars()) && count($user->getAvatars())>0) {
            $profileSummary->setAvatar($user->getAvatars()[count($user->getAvatars())-1]);
        }

        // Number of realized carpool (number of accepted Aks as driver or passenger)
        $asks = $this->askRepository->findAcceptedAsksForUser($user);
        // We count only one way and outward of a round trip
        $nbAsks = 0;
        foreach ($asks as $ask) {
            if ($ask->getType() == Ask::TYPE_ONE_WAY || $ask->getType() == Ask::TYPE_OUTWARD_ROUNDTRIP) {
                $nbAsks++;
            }
        }
        $profileSummary->setCarpoolRealized($nbAsks);

        // Get the first messages of every threads the user is involved in
        $threads = $this->messageRepository->findThreads($user);
        $nbMessageConsidered = 0;
        $nbMessagesTotal = 0;
        $nbMessagesAnswered = 0;
        foreach ($threads as $firstMessage) {
            // We keep only the XX last messages (.env variable)
            if ($nbMessageConsidered>=$this->profile['maxMessagesForAnswerRate']) {
                break;
            }

            // We keep only the messages where the user was recipient
            if ($firstMessage->getRecipients()[0]->getUser()->getId() == $user->getId()) {
                $nbMessagesTotal++;
                //We check if the User sent an anwser to this message
                $completeThread = $this->internalMessageManager->getCompleteThread($firstMessage->getId());
                foreach ($completeThread as $message) {
                    if ($message->getUser()->getid() == $user->getId()) {
                        $nbMessagesAnswered++;
                        break;
                    }
                }
            }

            $nbMessageConsidered++;
        }
        $profileSummary->setAnswerPct(($nbMessagesTotal==0) ? $this->profile['experiencedTagMinAnswerPctDefault'] : round(($nbMessagesAnswered/$nbMessagesTotal)*100));
        
        // Experienced user
        // To be experienced :
        // The User has to have a number of realized carpools >= experiencedTagMinCarpools(.env)
        // The User has to have a answer percentage >= experiencedTagMinAnswerPct(.env)
        $profileSummary->setExperienced(false);
        if ($this->profile['experiencedTag']) {
            if (
                $profileSummary->getCarpoolRealized()>=$this->profile['experiencedTagMinCarpools'] &&
                $profileSummary->getAnswerPct()>=$this->profile['experiencedTagMinAnswerPct']
            ) {
                $profileSummary->setExperienced(true);
            }
        }

        return $profileSummary;
    }

    /**
     * Get the public profile of a User
     *
     * @param User $user   The User
     * @return PublicProfile
     */
    public function getPublicProfile(User $user): PublicProfile
    {
        $publicProfile = new PublicProfile($user->getId());
        // Get the profile summary
        $publicProfile->setProfileSummary($this->getProfileSummary($user));

        // Preferences
        $publicProfile->setSmoke($user->getSmoke());
        $publicProfile->setMusic($user->hasMusic());
        $publicProfile->setMusicFavorites($user->getMusicFavorites());
        $publicProfile->setChat($user->hasChat());
        $publicProfile->setChatFavorites($user->getChatFavorites());

        // Get the reviews about this user
        $publicProfile->setReviewActive($this->profile['userReview']);
        $publicProfile->setReviews($this->reviewManager->getSpecificReviews(null, $user));

        return $publicProfile;
    }
}
