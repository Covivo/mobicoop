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

namespace App\Match\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Geography\Entity\Address;
use App\Match\Entity\Mass;
use App\Match\Entity\MassPerson;
use App\Match\Repository\MassPersonRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Auth\Repository\AuthItemRepository;
use App\Carpool\Ressource\Ad;
use App\Carpool\Entity\Criteria;
use App\Carpool\Service\AdManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Match\Event\MassMigrateUserMigratedEvent;
use App\Match\Exception\MassException;
use App\Community\Service\CommunityManager;
use App\Import\Service\ImportManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Mass compute manager.
 *
 * This service compute all Masses data.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassMigrateManager
{
    private $massPersonRepository;
    private $entityManager;
    private $encoder;
    private $authItemRepository;
    private $userRepository;
    private $adManager;
    private $params;
    private $eventDispatcher;
    private $communityManager;
    private $security;
    private $importManager;
    private $logger;

    const MOBIMATCH_IMPORT_PREFIX = "Mobimatch#";
    const LAUNCH_IMPORT = true;

    public function __construct(MassPersonRepository $massPersonRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, AuthItemRepository $authItemRepository, UserRepository $userRepository, AdManager $adManager, EventDispatcherInterface $eventDispatcher, CommunityManager $communityManager, Security $security, ImportManager $importManager, LoggerInterface $logger, array $params)
    {
        $this->massPersonRepository = $massPersonRepository;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->authItemRepository = $authItemRepository;
        $this->userRepository = $userRepository;
        $this->adManager = $adManager;
        $this->params = $params;
        $this->eventDispatcher = $eventDispatcher;
        $this->communityManager = $communityManager;
        $this->security = $security;
        $this->importManager = $importManager;
        $this->logger = $logger;
    }

    /**
     * Migrate the mass's Users (MassPerson to User)
     *
     * @param Mass $mass    The Mass owning the MassPersons to migrate
     * @return Mass
     */
    public function migrate(Mass $mass)
    {
        set_time_limit(50000);

        $migratedUsers = [];

        // We set the status of the Mass at Migrating
        $this->logger->info('Mass Migrate | Set status of Mass #' . $mass->getId() . ' to migrating | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $mass->setStatus(Mass::STATUS_MIGRATING);
        $mass->setMigrationDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        // If there is a community to create, we create it
        $community = null;
        
        
        /*** COMMNUNITY ***/
        if (!empty($mass->getCommunityId())) {
            // First, we check if there is an id community given

            $community = $this->communityManager->getCommunity($mass->getCommunityId());
            if (!$community) {
                throw new MassException(MassException::COMMUNITY_UNKNOWN);
            }
        } elseif (!empty($mass->getCommunityName())) {
            // Else we check if there is new community infos given

            $this->logger->info('Mass Migrate | Create community ' . $mass->getCommunityName() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $community = new Community();
            $community->setName($mass->getCommunityName());

            // Check if the necessary field are filled
            if (empty($mass->getCommunityDescription())) {
                throw new MassException(MassException::COMMUNITY_MISSING_DESCRIPTION);
            }
            if (empty($mass->getCommunityFullDescription())) {
                throw new MassException(MassException::COMMUNITY_MISSING_FULL_DESCRIPTION);
            }
            if (empty($mass->getCommunityAddress())) {
                throw new MassException(MassException::COMMUNITY_MISSING_ADDRESS);
            }

            $community->setDescription($mass->getCommunityDescription());
            $community->setFullDescription($mass->getCommunityFullDescription());

            // The creator is the creator of the mass
            $community->setUser($this->security->getUser());

            $community->setAddress($mass->getCommunityAddress());

            // $this->entityManager->persist($community);
            // $this->entityManager->flush();
            $community = $this->communityManager->save($community);
        }

        // Then we get the Mass persons related the this mass
        $massPersonIdMin = null;
        // $massPersonIdMin = 2217; // Use this in debug to force a minimal id of MassPerson
        $massPersons = $this->massPersonRepository->findAllByMass($mass, $massPersonIdMin);

        $this->logger->info('Mass Migrate | Number of Mass persons to migrate : ' . count($massPersons) . ' | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // Then for each person we're creating a User (with a Role and an Address)

        /**
         * @var MassPerson $massPerson
         */
        foreach ($massPersons as $massPerson) {
            $this->logger->info('Mass Migrate | Treating Mass person #' . $massPerson->getId() . ' | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

            // We check if this user already exists
            $user = $this->userRepository->findOneBy(["email"=>$massPerson->getEmail()]);

            if (!is_null($user)) {
                // This MassPerson has already an existing account
                // We're returning the found User
                $user->setAlreadyRegistered(true);
                $migratedUsers[] = $user;
            } else {

                // We don't know this MassPerson. We're creating the User.
                if ($massPerson->getEmail()!=="") {
                    $user = new User();
                    $user->setGivenName($massPerson->getGivenName());
                    $user->setFamilyName($massPerson->getFamilyName());
                    $user->setEmail($massPerson->getEmail());
                    // To do : Add gender to csv/xml file
                    $user->setGender(User::GENDER_OTHER);
                    // To do : Birthdate ?

                    $user->setPhoneDisplay(1);
                    $user->setSmoke($this->params['smoke']);
                    $user->setMusic($this->params['music']);
                    $user->setChat($this->params['chat']);
                    // To do : Dynamic Language
                    $user->setLanguage('fr_FR');

                    // Set an encrypted password
                    $password = $this->randomPassword();
                    $user->setPassword($this->encoder->encodePassword($user, $password));
                    $user->setClearPassword($password); // Used to be send by email (not persisted)

                    // auto valid the registration
                    $user->setValidatedDate(new \DateTime());

                    // We give him a fully registrated role
                    // default role : user registered full
                    $authItem = $this->authItemRepository->find(AuthItem::ROLE_USER_REGISTERED_FULL);
                    $userAuthAssignment = new UserAuthAssignment();
                    $userAuthAssignment->setAuthItem($authItem);
                    $user->addUserAuthAssignment($userAuthAssignment);

                    // The home address of the user
                    if ($mass->hasSetHomeAddress()) {
                        $personalAddress = clone $massPerson->getPersonalAddress();
                        $personalAddress->setUser($user);
                        $personalAddress->setHome(true);
                    }

                    $user->addAddress($personalAddress);

                    $migratedUsers[] = $user; // For the return

                    $this->entityManager->persist($user);

                    // Trigger an event to send a email
                    $event = new MassMigrateUserMigratedEvent($user);
                    $this->eventDispatcher->dispatch(MassMigrateUserMigratedEvent::NAME, $event);
                }
            }

            // If there is a community we create a CommunityUser with the User.
            if (!is_null($community)) {
                // Check if the user is aleadry a member of this community
                $alreadyMember = false;
                foreach ($user->getCommunityUsers() as $userCommunityUser) {
                    if ($userCommunityUser->getCommunity()->getId()==$community->getId()) {
                        $alreadyMember = true;
                    }
                }

                if (!$alreadyMember) {
                    $communityUser = new CommunityUser();
                    $communityUser->setCommunity($community);
                    $communityUser->setUser($user);
                    $this->entityManager->persist($communityUser);
                }
            }

            // We set the link between the MassPerson and the User (new or found)
            $massPerson->setUser($user);
            $this->entityManager->persist($massPerson);
            $this->entityManager->flush();

            // We create an Ad for the User (regular, home to work, monday to friday)
            // First we check if the journey has been computed (analyzing phase)
            if (!is_null($massPerson->getDistance())) {
                $this->logger->info('Mass Migrate | createJourneyFromMassPerson #' . $massPerson->getId() . ' | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                $this->createJourneyFromMassPerson($massPerson, $user, $community, $mass->getMassType());
            }
        }
        
        // Finally, we set status of the Mass at Migrated and save the migrated date
        // we do it before the import because the import process can break entities relations, and therefor break the flush...
        $this->logger->info('Mass Migrate | Set status of Mass #' . $mass->getId() . ' to migrated | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $mass->setStatus(Mass::STATUS_MIGRATED);
        $mass->setMigratedDate(new \Datetime());

        // Link the Mass to the Community
        $mass->setCommunity($community);

        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        $mass->setMigratedUsers($migratedUsers);

        // Launch import and mass matching
        if (self::LAUNCH_IMPORT) {
            $this->logger->info('Mass Migrate | Start user import | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $this->importManager->treatUserImport(self::MOBIMATCH_IMPORT_PREFIX, $mass->getId());
        }

        return $mass;
    }

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
     * TO DO : It might not be a good solution to user createAd from AdManager.
     * We don't want to trigger matching at every Proposal we add.
     * Create the journey (Ad then Proposal) of a MassPerson
     *
     * @param MassPerson $massPerson            Current MassPerson
     * @param User $user                        The User created after this MassPerson
     * @param Community|null $community         The community of this person. Can be null if there is no community created
     * @param int $massType                     The Mass type of this Person's Mass
     * @return Ad
     */
    private function createJourneyFromMassPerson(MassPerson $massPerson, User $user, ?Community $community, int $massType): Ad
    {
        set_time_limit(50000);

        $ad = new Ad();

        // Role
        if ($massPerson->isDriver() && $massPerson->isPassenger()) {
            $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        } elseif ($massPerson->isDriver()) {
            $ad->setRole(Ad::ROLE_DRIVER);
        } else {
            $ad->setRole(Ad::ROLE_PASSENGER);
        }

        // round-trip
        $ad->setOneWay(false);
        if (is_null($massPerson->getReturnTime())) {
            $ad->setOneWay(true);
        }

        // Regular
        $ad->setFrequency(Criteria::FREQUENCY_REGULAR);

        // Outward waypoints and time
        $outwardWaypoints = [
            clone $massPerson->getPersonalAddress(),
            clone $massPerson->getWorkAddress()
        ];

        $ad->setOutwardWaypoints($outwardWaypoints);
        $ad->setOutwardTime($massPerson->getOutwardTime()->format("H:i"));

        // return waypoints and time
        if (!$ad->isOneWay()) {
            $returnWaypoints = [
                clone $massPerson->getWorkAddress(),
                clone $massPerson->getPersonalAddress()
            ];

            $ad->setReturnWaypoints($returnWaypoints);
            $ad->setReturnTime($massPerson->getReturnTime()->format("H:i"));
        }

        // Schedule
        $schedule = [];
        $days = ['mon','tue','wed','thu','fri'];
        foreach ($days as $day) {
            $schedule[0][$day] = true;
        }
        $schedule[0]['outwardTime'] = $massPerson->getOutwardTime()->format("H:i");
        
        if (!$ad->isOneWay()) {
            $schedule[0]['returnTime'] = $massPerson->getReturnTime()->format("H:i");
        }

        $ad->setSchedule($schedule);

        // The User
        $ad->setUser($user);
        $ad->setUserId($user->getId());

        // The community if it exists
        if (!is_null($community)) {
            $ad->setCommunities([$community->getId()]);
        }

        return $this->adManager->createAd($ad, ($massType == Mass::TYPE_MIGRATION) ? true : false, false);
    }
}
