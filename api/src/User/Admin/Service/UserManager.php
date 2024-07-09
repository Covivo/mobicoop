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
 */

namespace App\User\Admin\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Communication\Entity\Medium;
use App\Community\Entity\Community;
use App\DataProvider\Entity\RezopouceProvider;
use App\Event\Entity\Event;
use App\Geography\Entity\Address;
use App\Geography\Entity\RezoPouceTerritoryStatus;
use App\Geography\Repository\TerritoryRepository;
use App\Geography\Ressource\Point;
use App\Geography\Service\PointSearcher;
use App\User\Entity\IdentityProof;
use App\User\Entity\User;
use App\User\Event\AskParentalConsentEvent;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
use App\User\Event\UserDrivingLicenceNumberUpdateEvent;
use App\User\Event\UserPhoneUpdateEvent;
use App\User\Repository\UserNotificationRepository;
use App\User\Repository\UserRepository;
use App\User\Service\UserManager as ServiceUserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * User manager service for administration.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class UserManager
{
    private $entityManager;
    private $authItemRepository;
    private $territoryRepository;
    private $encoder;
    private $eventDispatcher;
    private $security;
    private $userManager;
    private $userRepository;
    private $pointSearcher;
    private $chat;
    private $music;
    private $smoke;
    private $rzpUri;
    private $rzpLogin;
    private $rzpPassword;
    private $userDelegateEmailBase;
    private $userNotificationRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param mixed                  $chat
     * @param mixed                  $smoke
     * @param mixed                  $music
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthItemRepository $authItemRepository,
        TerritoryRepository $territoryRepository,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ServiceUserManager $userManager,
        UserRepository $userRepository,
        PointSearcher $pointSearcher,
        $chat,
        $smoke,
        $music,
        string $rzpUri,
        string $rzpLogin,
        string $rzpPassword,
        string $userDelegateEmailBase,
        UserNotificationRepository $userNotificationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->authItemRepository = $authItemRepository;
        $this->territoryRepository = $territoryRepository;
        $this->encoder = $encoder;
        $this->eventDispatcher = $dispatcher;
        $this->security = $security;
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
        $this->pointSearcher = $pointSearcher;
        $this->chat = $chat;
        $this->music = $music;
        $this->smoke = $smoke;
        $this->rzpUri = $rzpUri;
        $this->rzpLogin = $rzpLogin;
        $this->rzpPassword = $rzpPassword;
        $this->userDelegateEmailBase = $userDelegateEmailBase;
        $this->userNotificationRepository = $userNotificationRepository;
    }

    private function __getLocalityCode(float $lon, float $lat): ?int
    {
        $result = $this->pointSearcher->reverse($lon, $lat);
        if (isset($result[0]) && $result[0] instanceof Point) {
            return $result[0]->getLocalityCode();
        }

        return null;
    }

    private function __getHomeAddressLocality(array $addresses): ?string
    {
        foreach ($addresses as $address) {
            if ($address->isHome()) {
                return $address->getAddressLocality();
            }
        }

        return null;
    }

    /**
     * Get a user by its id.
     *
     * @param int $id The user id
     *
     * @return null|User The user if found
     */
    public function getUser(int $id)
    {
        $user = $this->userRepository->find($id);
        $user->initOwnership(); // construct of User not called

        // check if the user is not the author of an event that is still valid
        $events = [];
        foreach ($user->getEvents() as $event) {
            /**
             * @var Event $event
             */
            if ($event->getToDate() >= new \DateTime()) {
                $events[] = $event->getId().' - '.$event->getName();
            }
        }
        if (count($events) > 0) {
            $user->addOwnership(['events' => $events]);
        }

        $communities = [];
        foreach ($user->getCommunities() as $community) {
            // @var Community $community
            $communities[] = $community->getId().' - '.$community->getName();
        }
        if (count($communities) > 0) {
            $user->addOwnership(['communities' => $communities]);
        }

        return $this->setUserPostalAddress($user);
    }

    /**
     * Add a user.
     *
     * @param User $user The user to register
     *
     * @return User The user created
     */
    public function addUser(User $user)
    {
        // add delegation
        $user->setUserDelegate($this->security->getUser());
        // check if roles were set
        if (count($user->getRolesTerritory()) > 0) {
            // roles are set => add each role
            foreach ($user->getRolesTerritory() as $roleTerritory) {
                $userAuthAssignment = new UserAuthAssignment();
                $authItem = $this->authItemRepository->find($roleTerritory['role']);
                $userAuthAssignment->setAuthItem($authItem);
                if (isset($roleTerritory['territory'])) {
                    $territory = $this->territoryRepository->find($roleTerritory['territory']);
                    $userAuthAssignment->setTerritory($territory);
                }
                $user->addUserAuthAssignment($userAuthAssignment);
            }
        } else {
            // no role set => add the default role
            $authItem = $this->authItemRepository->find(User::ROLE_DEFAULT);
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
        }

        // create password if not given
        if (is_null($user->getPassword())) {
            $user->setPassword($this->userManager->randomPassword());
        }
        $user->setClearPassword($user->getPassword());
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        if (is_null($user->getPhoneDisplay())) {
            $user->setPhoneDisplay(User::PHONE_DISPLAY_RESTRICTED);
        }

        if (is_null($user->hasChat())) {
            $user->setChat($this->chat);
        }
        if (is_null($user->hasMusic())) {
            $user->setMusic($this->music);
        }

        if (is_null($user->getSmoke())) {
            $user->setSmoke($this->smoke);
        }

        if (is_null($user->getEmail())) {
            $user->setEmail($this->userManager->generateSubEmail($this->userDelegateEmailBase));
        }

        // create token to validate regisration
        $user->setEmailToken($this->userManager->createShortToken());

        // create token to unsubscribe from the instance news
        $user->setUnsubscribeToken($this->userManager->createToken($user));

        if (!is_null($user->getLegalGuardianEmail())) {
            $user->setParentalConsentToken($this->userManager->createShortToken());
            $user->setParentalConsentUuid($this->userManager->_generateUuid());
        }
        // check if identity is validated manually
        if ($user->hasVerifiedIdentity()) {
            $user->setIdentityStatus(IdentityProof::STATUS_ACCEPTED);
        }

        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // check if the home address was set
        if (!is_null($user->getHomeAddress())) {
            $homeAddress = new Address();
            $homeAddress->setStreetAddress($user->getHomeAddress()->getStreetAddress());
            $homeAddress->setPostalCode($user->getHomeAddress()->getPostalCode());
            $homeAddress->setAddressLocality($user->getHomeAddress()->getAddressLocality());
            $homeAddress->setAddressCountry($user->getHomeAddress()->getAddressCountry());
            $homeAddress->setLatitude($user->getHomeAddress()->getLatitude());
            $homeAddress->setLongitude($user->getHomeAddress()->getLongitude());
            $homeAddress->setHouseNumber($user->getHomeAddress()->getHouseNumber());
            $homeAddress->setSubLocality($user->getHomeAddress()->getSubLocality());
            $homeAddress->setLocalAdmin($user->getHomeAddress()->getLocalAdmin());
            $homeAddress->setCounty($user->getHomeAddress()->getCounty());
            $homeAddress->setMacroCounty($user->getHomeAddress()->getMacroCounty());
            $homeAddress->setRegion($user->getHomeAddress()->getRegion());
            $homeAddress->setMacroRegion($user->getHomeAddress()->getMacroRegion());
            $homeAddress->setCountryCode($user->getHomeAddress()->getCountryCode());
            $homeAddress->setHome(true);
            $homeAddress->setName(Address::HOME_ADDRESS);
            $homeAddress->setUser($user);
            $this->entityManager->persist($homeAddress);
            $this->entityManager->flush();
        }

        // create of the alert preferences
        $user = $this->userManager->createAlerts($user);

        // dispatch the delegate registration event
        $event = new UserDelegateRegisteredEvent($user);
        $this->eventDispatcher->dispatch(UserDelegateRegisteredEvent::NAME, $event);
        // send password ?
        if (!is_null($user->getTelephone())) {
            $event = new UserDelegateRegisteredPasswordSendEvent($user);
            $this->eventDispatcher->dispatch(UserDelegateRegisteredPasswordSendEvent::NAME, $event);
        }

        if (!is_null($user->getLegalGuardianEmail())) {
            $event = new AskParentalConsentEvent($user);
            $this->eventDispatcher->dispatch(AskParentalConsentEvent::NAME, $event);
        }

        return $user;
    }

    /**
     * Patch a user.
     *
     * @param User  $user   The user to update
     * @param array $fields The updated fields
     *
     * @return User The user updated
     */
    public function patchUser(User $user, array $fields)
    {
        // check if the home address was updated
        if (in_array('homeAddress', array_keys($fields))) {
            // home address updated, we search the original home address
            $homeAddress = null;
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $homeAddress = $address;

                    break;
                }
            }
            if (!is_null($homeAddress)) {
                // we have to update each field...
                // @var Address $homeAddress
                $homeAddress->setStreetAddress($user->getHomeAddress()->getStreetAddress());
                $homeAddress->setStreet($user->getHomeAddress()->getStreet());
                $homeAddress->setPostalCode($user->getHomeAddress()->getPostalCode());
                $homeAddress->setAddressLocality($user->getHomeAddress()->getAddressLocality());
                $homeAddress->setAddressCountry($user->getHomeAddress()->getAddressCountry());
                $homeAddress->setLatitude($user->getHomeAddress()->getLatitude());
                $homeAddress->setLongitude($user->getHomeAddress()->getLongitude());
                $homeAddress->setHouseNumber($user->getHomeAddress()->getHouseNumber());
                $homeAddress->setSubLocality($user->getHomeAddress()->getSubLocality());
                $homeAddress->setLocalAdmin($user->getHomeAddress()->getLocalAdmin());
                $homeAddress->setCounty($user->getHomeAddress()->getCounty());
                $homeAddress->setMacroCounty($user->getHomeAddress()->getMacroCounty());
                $homeAddress->setRegion($user->getHomeAddress()->getRegion());
                $homeAddress->setMacroRegion($user->getHomeAddress()->getMacroRegion());
                $homeAddress->setCountryCode($user->getHomeAddress()->getCountryCode());
                $this->entityManager->persist($homeAddress);
                $this->entityManager->flush();

                // check if the user is also a solidary user
                if ($user->getSolidaryUser() && $user->getSolidaryUser()->isVolunteer()) {
                    $user->getSolidaryUser()->setAddress($homeAddress);
                }
            }
        }

        // check if roles were updated
        if (in_array('rolesTerritory', array_keys($fields))) {
            foreach ($user->getUserAuthAssignments() as $userAuthAssignment) {
                $this->entityManager->remove($userAuthAssignment);
            }
            $this->entityManager->flush();

            // remove current roles
            $user->removeUserAuthAssignments();
            // add each role
            foreach ($fields['rolesTerritory'] as $roleTerritory) {
                $userAuthAssignment = new UserAuthAssignment();
                $authItem = $this->authItemRepository->find($roleTerritory['role']);
                $userAuthAssignment->setAuthItem($authItem);
                if (isset($roleTerritory['territory'])) {
                    $territory = $this->territoryRepository->find($roleTerritory['territory']);
                    $userAuthAssignment->setTerritory($territory);
                }
                $user->addUserAuthAssignment($userAuthAssignment);
            }
        }

        // check if identity is validated manually
        if (in_array('verifiedIdentity', array_keys($fields))) {
            if (true === $fields['verifiedIdentity']) {
                $user->setIdentityStatus(IdentityProof::STATUS_ACCEPTED);
            } else {
                $user->setIdentityStatus(IdentityProof::STATUS_NONE);
            }
        }

        if (in_array('rezoKit', array_keys($fields))) {
            if (true === $fields['rezoKit']) {
                $user->setRezoKit(true);
            } else {
                $user->setRezoKit(false);
            }
        }

        if (in_array('cardLetter', array_keys($fields))) {
            if (true === $fields['cardLetter']) {
                $user->setCardLetter(true);
            } else {
                $user->setCardLetter(false);
            }
        }

        if (in_array('drivingLicenceNumber', array_keys($fields)) && $fields['drivingLicenceNumber'] !== $user->getOldDrivingLicenceNumber()) {
            $eecEvent = new UserDrivingLicenceNumberUpdateEvent($user);
            $this->eventDispatcher->dispatch(UserDrivingLicenceNumberUpdateEvent::NAME, $eecEvent);
        }

        if (in_array('telephone', array_keys($fields))) {
            if ($fields['telephone'] !== $user->getOldTelephone()) {
                $user->setPhoneToken(null);
                $user->setPhoneValidatedDate(null);
                // deactivate sms notification since the phone is new
                $this->deActivateSmsNotification($user);

                $eecEvent = new UserPhoneUpdateEvent($user);
                $this->eventDispatcher->dispatch(UserPhoneUpdateEvent::NAME, $eecEvent);
            }
        }

        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // return the user
        return $user;
    }

    /**
     * Check if a user have a specific AuthItem.
     *
     * @param User     $user     The user
     * @param AuthItem $authItem The authItem
     *
     * @return bool True if the user have the authItem, false otherwise
     */
    public function userHaveAuthItem(User $user, AuthItem $authItem)
    {
        foreach ($user->getUserAuthAssignments() as $userAuthAssignment) {
            if ($userAuthAssignment->getAuthItem() == $authItem) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a User object from an array.
     *
     * @param array $auser The user to create, as an array
     *
     * @return User The User object
     */
    public function createUserFromArray(array $auser)
    {
        $user = new User();
        if (isset($auser['givenName'])) {
            $user->setGivenName($auser['givenName']);
        }
        if (isset($auser['familyName'])) {
            $user->setFamilyName($auser['familyName']);
        }
        if (isset($auser['email'])) {
            $user->setEmail($auser['email']);
        }
        if (isset($auser['telephone'])) {
            $user->setTelephone($auser['telephone']);
        }
        if (isset($auser['gender'])) {
            $user->setGender($auser['gender']);
        }
        if (isset($auser['birthDate'])) {
            $user->setBirthDate(new \DateTime($auser['birthDate']));
        }

        if (!isset($auser['password'])) {
            // create password if not given
            $user->setPassword($this->userManager->randomPassword());
        }
        $user->setClearPassword($user->getPassword());
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        if (!isset($auser['phoneDisplay'])) {
            $user->setPhoneDisplay(User::PHONE_DISPLAY_RESTRICTED);
        }

        if (!isset($auser['chat'])) {
            $user->setChat($this->chat);
        }
        if (!isset($auser['music'])) {
            $user->setMusic($this->music);
        }

        if (!isset($auser['smoke'])) {
            $user->setSmoke($this->smoke);
        }

        // create token to validate registration
        $user->setEmailToken($this->userManager->createToken($user));

        // create token to unsubscribe from the instance news
        $user->setUnsubscribeToken($this->userManager->createToken($user));

        // set default role
        $authItem = $this->authItemRepository->find(User::ROLE_DEFAULT);
        $userAuthAssignment = new UserAuthAssignment();
        $userAuthAssignment->setAuthItem($authItem);
        $user->addUserAuthAssignment($userAuthAssignment);

        // check if the home address was set
        if (isset($auser['homeAddress'])) {
            $homeAddress = new Address();
            if (isset($auser['homeAddress']['streetAddress'])) {
                $homeAddress->setStreetAddress($auser['homeAddress']['streetAddress']);
            }
            if (isset($auser['homeAddress']['postalCode'])) {
                $homeAddress->setPostalCode($auser['homeAddress']['postalCode']);
            }
            if (isset($auser['homeAddress']['addressLocality'])) {
                $homeAddress->setAddressLocality($auser['homeAddress']['addressLocality']);
            }
            if (isset($auser['homeAddress']['addressCountry'])) {
                $homeAddress->setAddressCountry($auser['homeAddress']['addressCountry']);
            }
            if (isset($auser['homeAddress']['latitude'])) {
                $homeAddress->setLatitude($auser['homeAddress']['latitude']);
            }
            if (isset($auser['homeAddress']['longitude'])) {
                $homeAddress->setLongitude($auser['homeAddress']['longitude']);
            }
            if (isset($auser['homeAddress']['houseNumber'])) {
                $homeAddress->setHouseNumber($auser['homeAddress']['houseNumber']);
            }
            if (isset($auser['homeAddress']['subLocality'])) {
                $homeAddress->setSubLocality($auser['homeAddress']['subLocality']);
            }
            if (isset($auser['homeAddress']['localAdmin'])) {
                $homeAddress->setLocalAdmin($auser['homeAddress']['localAdmin']);
            }
            if (isset($auser['homeAddress']['county'])) {
                $homeAddress->setCounty($auser['homeAddress']['county']);
            }
            if (isset($auser['homeAddress']['macroCounty'])) {
                $homeAddress->setMacroCounty($auser['homeAddress']['macroCounty']);
            }
            if (isset($auser['homeAddress']['region'])) {
                $homeAddress->setRegion($auser['homeAddress']['region']);
            }
            if (isset($auser['homeAddress']['macroRegion'])) {
                $homeAddress->setMacroRegion($auser['homeAddress']['macroRegion']);
            }
            if (isset($auser['homeAddress']['countryCode'])) {
                $homeAddress->setCountryCode($auser['homeAddress']['countryCode']);
            }
            $homeAddress->setHome(true);
            $homeAddress->setName(Address::HOME_ADDRESS);
            $homeAddress->setUser($user);
            $this->entityManager->persist($homeAddress);
        }

        // return the user
        return $user;
    }

    /**
     * Delete a user.
     *
     * @param User  $user      The user to delete
     * @param mixed $isScammer
     */
    public function deleteUser(User $user, $isScammer = false)
    {
        $this->userManager->deleteUser($user, $isScammer);

        return $user;
    }

    /*
     * Generate a sub email address
     *
     * @param string $email     The base email
     * @return string           The generated sub email address
     */
    public function generateSubEmail(string $email)
    {
        return $this->userManager->generateSubEmail($email);
    }

    public function getRzpTerritoryStatus(int $userId): ?User
    {
        $user = $this->userRepository->find($userId);
        $user->setRzpTerritoryStatus(RezoPouceTerritoryStatus::RZP_TERRITORY_STATUS_LABELS[RezoPouceTerritoryStatus::RZP_TERRITORY_STATUS_NOT_CONSIDERED]);

        $searchedLocality = $this->__getHomeAddressLocality($user->getAddresses());
        if (!is_null($searchedLocality)) {
            $localityCode = $this->__getLocalityCode($user->getHomeAddress()->getLongitude(), $user->getHomeAddress()->getLatitude());
            $rzpProvider = new RezopouceProvider($this->rzpUri, $this->rzpLogin, $this->rzpPassword);
            $territory = $rzpProvider->getCommuneTerritory($localityCode);
            if (!is_null($territory)) {
                $user->setRzpTerritoryStatus(RezoPouceTerritoryStatus::RZP_TERRITORY_STATUS_LABELS[$territory->getStatus()->getId()]);
            }
        }

        return $user;
    }

    public function getUserByEmail(string $email): ?User
    {
        if ($user = $this->userRepository->findOneBy(['email' => $email])) {
            return $this->getUser($user->getId());
        }

        return null;
    }

    /**
     * set sms notification to non active when phone change or is removed.
     */
    public function deActivateSmsNotification(User $user)
    {
        $userNotifications = $this->userNotificationRepository->findUserNotifications($user->getId());
        foreach ($userNotifications as $userNotification) {
            if (Medium::MEDIUM_SMS == $userNotification->getNotification()->getMedium()->getId()) {
                $userNotification->setActive(false);
                $userNotification->setUser($user);
                $this->entityManager->persist($userNotification);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Force postal address of Rezo Pouce users.
     */
    private function setUserPostalAddress(User $user): User
    {
        if (
            ($user->isHitchHikeDriver() || $user->isHitchHikePassenger())
            && is_null($user->getPostalAddress())
            && !is_null($user->getHomeAddress())
        ) {
            $user->setPostalAddress(
                $user->getGivenName()
                .' '.$user->getFamilyName()
                .(!is_null($user->getHomeAddress()->getStreetAddress()) ? ' '.$user->getHomeAddress()->getStreetAddress() : '')
                .(!is_null($user->getHomeAddress()->getPostalCode()) ? ' '.$user->getHomeAddress()->getPostalCode() : '')
                .(!is_null($user->getHomeAddress()->getAddressLocality()) ? ' '.$user->getHomeAddress()->getAddressLocality() : '')
            );
        }

        return $user;
    }
}
