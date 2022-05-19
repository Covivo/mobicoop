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

namespace App\Community\Admin\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Community\Event\CommunityCreatedEvent;
use App\Community\Event\CommunityMembershipAcceptedEvent;
use App\Community\Event\CommunityMembershipRefusedEvent;
use App\Community\Exception\CommunityException;
use App\Community\Repository\CommunityRepository;
use App\Community\Repository\CommunityUserRepository;
use App\Geography\Entity\Address;
use App\User\Admin\Service\UserManager;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Community manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class CommunityManager
{
    private $entityManager;
    private $communityUserRepository;
    private $communityRepository;
    private $userRepository;
    private $userManager;
    private $authItemRepository;
    private $eventDispatcher;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CommunityRepository $communityRepository,
        CommunityUserRepository $communityUserRepository,
        UserRepository $userRepository,
        UserManager $userManager,
        AuthItemRepository $authItemRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->communityUserRepository = $communityUserRepository;
        $this->communityRepository = $communityRepository;
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->authItemRepository = $authItemRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get the community members.
     *
     * @param int $communityId The community id
     *
     * @return array The members
     */
    public function getMembers(int $communityId, array $context = [], string $operationName)
    {
        if ($community = $this->communityRepository->find($communityId)) {
            return $this->communityUserRepository->findForCommunity($community, $context, $operationName);
        }

        throw new CommunityException('Community not found');
    }

    /**
     * Add a community.
     *
     * @param Community $community The community to add
     *
     * @return Community The community created
     */
    public function addCommunity(Community $community)
    {
        if ($referrer = $this->userRepository->find($community->getReferrerId())) {
            $community->setUser($referrer);
            // add the community manager role to the referrer
            $this->addCommunityManagerRole($referrer);
            // add the referrer as moderator of the community
            $communityUser = new CommunityUser();
            $communityUser->setUser($referrer);
            $communityUser->setCommunity($community);
            $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MODERATOR);
            $this->entityManager->persist($communityUser);
        } else {
            throw new CommunityException('Referrer not found');
        }

        // persist the community
        $this->entityManager->persist($community);
        $this->entityManager->flush();

        // check if the address was set
        if (!is_null($community->getAddress())) {
            $address = new Address();
            $address->setStreetAddress($community->getAddress()->getStreetAddress());
            $address->setPostalCode($community->getAddress()->getPostalCode());
            $address->setAddressLocality($community->getAddress()->getAddressLocality());
            $address->setAddressCountry($community->getAddress()->getAddressCountry());
            $address->setLatitude($community->getAddress()->getLatitude());
            $address->setLongitude($community->getAddress()->getLongitude());
            $address->setHouseNumber($community->getAddress()->getHouseNumber());
            $address->setStreetAddress($community->getAddress()->getStreetAddress());
            $address->setSubLocality($community->getAddress()->getSubLocality());
            $address->setLocalAdmin($community->getAddress()->getLocalAdmin());
            $address->setCounty($community->getAddress()->getCounty());
            $address->setMacroCounty($community->getAddress()->getMacroCounty());
            $address->setRegion($community->getAddress()->getRegion());
            $address->setMacroRegion($community->getAddress()->getMacroRegion());
            $address->setCountryCode($community->getAddress()->getCountryCode());
            $address->setHome(true);
            $address->setCommunity($community);
            $this->entityManager->persist($address);
            $this->entityManager->flush();
        }

        //  we dispatch the event associated
        $event = new CommunityCreatedEvent($community);
        $this->eventDispatcher->dispatch($event, CommunityCreatedEvent::NAME);

        return $community;
    }

    /**
     * Patch a community.
     *
     * @param Community $community The community to update
     * @param array     $fields    The updated fields
     *
     * @return Community The community updated
     */
    public function patchCommunity(Community $community, array $fields)
    {
        // check if referrer has changed
        if (in_array('referrerId', array_keys($fields))) {
            if ($referrer = $this->userRepository->find($fields['referrerId'])) {
                // keep the previous referrer for further use
                $previousReferrer = $community->getUser();
                // set the new referrer
                $community->setUser($referrer);
                // add the community manager role to the referrer
                $this->addCommunityManagerRole($referrer);
                // check if the previous referrer is still community manager
                $this->checkUserIsReferrer($previousReferrer, $community);
            } else {
                throw new CommunityException('Referrer not found');
            }
        }

        // persist the community
        $this->entityManager->persist($community);
        $this->entityManager->flush();

        // return the community
        return $community;
    }

    /**
     * Add a community user.
     *
     * @param CommunityUser $communityUser The community user to update
     *
     * @return CommunityUser The community user updated
     */
    public function addCommunityUser(CommunityUser $communityUser)
    {
        $status = $communityUser->getStatus();

        // persist the community user
        $this->entityManager->persist($communityUser);
        $this->entityManager->flush();

        // we update the status as it can be automatically erased by doctrine events
        $communityUser->setStatus($status);
        $this->entityManager->persist($communityUser);
        $this->entityManager->flush();

        // return the community
        return $communityUser;
    }

    /**
     * Patch a community user.
     *
     * @param CommunityUser $communityUser The community user to update
     * @param array         $fields        The updated fields
     *
     * @return CommunityUser The community user updated
     */
    public function patchCommunityUser(CommunityUser $communityUser, array $fields)
    {
        // persist the community user
        $this->entityManager->persist($communityUser);
        $this->entityManager->flush();

        switch ($communityUser->getStatus()) {
            case CommunityUser::STATUS_REFUSED:
                $event = new CommunityMembershipRefusedEvent($communityUser->getCommunity(), $communityUser->getUser());
                $this->eventDispatcher->dispatch(CommunityMembershipRefusedEvent::NAME, $event);

                break;

            case CommunityUser::STATUS_ACCEPTED_AS_MEMBER:
                $event = new CommunityMembershipAcceptedEvent($communityUser->getCommunity(), $communityUser->getUser());
                $this->eventDispatcher->dispatch(CommunityMembershipAcceptedEvent::NAME, $event);

                break;
        }

        // return the community
        return $communityUser;
    }

    /**
     * Delete a community.
     *
     * @param Community $community The community to delete
     */
    public function deleteCommunity(Community $community)
    {
        $this->entityManager->remove($community);
        $this->entityManager->flush();
    }

    /**
     * Get the moderated communities for a given user.
     *
     * @param User $user The user
     *
     * @return null|array The communities found
     */
    public function getModerated(User $user)
    {
        return $this->communityRepository->getCommunitiesForUserAndStatuses($user, [CommunityUser::STATUS_ACCEPTED_AS_MODERATOR]);
    }

    /**
     * Add community manager role to the given user if needed.
     *
     * @param User $user The user
     */
    private function addCommunityManagerRole(User $user)
    {
        $authItem = $this->authItemRepository->find(AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC);

        if (!$this->userManager->userHaveAuthItem($user, $authItem)) {
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
            $this->entityManager->persist($user);
        }
    }

    /**
     * Check if a user is a community referrer, for communities other than the one provided
     * If not, remove the community manager role
     * Used to remove the community manager role to a user that is not effectively community manager anymore.
     *
     * @param User      $user      The user to check
     * @param Community $community The current community that leads to the check
     */
    private function checkUserIsReferrer(User $user, Community $community)
    {
        if (!$this->communityRepository->isReferrer($user, $community)) {
            // the user is not referrer anymore => we remove the role
            $authItem = $this->authItemRepository->find(AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC);
            foreach ($user->getUserAuthAssignments() as $userAuthAssignment) {
                /**
                 * @var UserAuthAssignment $userAuthAssignment
                 */
                if ($userAuthAssignment->getAuthItem() == $authItem) {
                    $user->removeUserAuthAssignment($userAuthAssignment);
                    $this->entityManager->persist($user);

                    break;
                }
            }
        }
    }
}
