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

namespace App\User\Admin\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\User\Entity\User;
use App\Geography\Entity\Address;
use App\Geography\Repository\TerritoryRepository;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
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
    private $chat;
    private $music;
    private $smoke;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager The entity manager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthItemRepository $authItemRepository,
        TerritoryRepository $territoryRepository,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ServiceUserManager $userManager,
        $chat,
        $smoke,
        $music
    ) {
        $this->entityManager = $entityManager;
        $this->authItemRepository = $authItemRepository;
        $this->territoryRepository = $territoryRepository;
        $this->encoder = $encoder;
        $this->eventDispatcher = $dispatcher;
        $this->security = $security;
        $this->userManager = $userManager;
        $this->chat = $chat;
        $this->music = $music;
        $this->smoke = $smoke;
    }

    /**
     * Add a user.
     *
     * @param User      $user               The user to register
     * @return User     The user created
     */
    public function addUser(User $user)
    {
        // add delegation
        $user->setUserDelegate($this->security->getUser());

        // check if roles were set
        if (count($user->getRolesTerritory())>0) {
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

        // create token to validate regisration
        $user->setEmailToken($this->userManager->createToken($user));

        // create token to unsubscribe from the instance news
        $user->setUnsubscribeToken($this->userManager->createToken($user));

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
        if ($user->getPasswordSendType() == User::PWD_SEND_TYPE_SMS) {
            $event = new UserDelegateRegisteredPasswordSendEvent($user);
            $this->eventDispatcher->dispatch(UserDelegateRegisteredPasswordSendEvent::NAME, $event);
        }

        return $user;
    }

    /**
     * Patch a user.
     *
     * @param User $user    The user to update
     * @param array $fields The updated fields
     * @return User         The user updated
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
                /**
                * @var Address $homeAddress
                */
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
                $this->entityManager->persist($homeAddress);
                $this->entityManager->flush();
            }
        }

        // check if roles were updated
        if (in_array('rolesTerritory', array_keys($fields))) {
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
        
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // return the user
        return $user;
    }

    /**
     * Check if a user have a specific AuthItem
     *
     * @param User      $user       The user
     * @param AuthItem  $authItem   The authItem
     * @return boolean  True if the user have the authItem, false otherwise
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
     * Create a User object from an array
     *
     * @param array $auser      The user to create, as an array
     * @param bool $persist     Should we persist the ne User immediately
     * @return User             The User object
     */
    public function createUserFromArray(array $auser, bool $persist = false)
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

        // persist the user
        if ($persist) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

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
            if ($persist) {
                $this->entityManager->persist($homeAddress);
                $this->entityManager->flush();
            }
        }

        // return the user
        return $user;
    }
}
