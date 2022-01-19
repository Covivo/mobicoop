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

namespace App\Community\Service;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Carpool\Entity\MapsAd\MapsAds;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Service\AdManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityMember;
use App\Community\Entity\CommunityMembersList;
use App\Community\Entity\CommunitySecurity;
use App\Community\Entity\CommunityUser;
use App\Community\Event\CommunityCreatedEvent;
use App\Community\Event\CommunityMembershipPendingEvent;
use App\Community\Event\CommunityNewMemberEvent;
use App\Community\Event\CommunityNewMembershipRequestEvent;
use App\Community\Repository\CommunityRepository;
use App\Community\Repository\CommunityUserRepository;
use App\Community\Resource\MCommunity;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Community manager.
 *
 * This service contains methods related to community management.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CommunityManager
{
    private $entityManager;
    private $logger;
    private $securityPath;
    private $userRepository;
    private $communityRepository;
    private $communityUserRepository;
    private $proposalRepository;
    private $authItemRepository;
    private $userManager;
    private $adManager;
    private $eventDispatcher;
    private $actionRepository;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        UserRepository $userRepository,
        CommunityRepository $communityRepository,
        CommunityUserRepository $communityUserRepository,
        ProposalRepository $proposalRepository,
        AuthItemRepository $authItemRepository,
        UserManager $userManager,
        AdManager $adManager,
        EventDispatcherInterface $eventDispatcher,
        ActionRepository $actionRepository,
        string $securityPath
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->securityPath = $securityPath;
        $this->userRepository = $userRepository;
        $this->communityRepository = $communityRepository;
        $this->communityUserRepository = $communityUserRepository;
        $this->proposalRepository = $proposalRepository;
        $this->authItemRepository = $authItemRepository;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->actionRepository = $actionRepository;
    }

    /**
     * Check if a user can join a community
     * To join an opened community, no credentials is needed, the user just need to be registered.
     * To join a closed community, a user needs to give credentials, we will call them login and password
     * even if they represent other kind of information (id, date of birth...).
     *
     * @return bool
     */
    public function canJoin(User $user, Community $community)
    {
        $authorized = true;

        $communityUser = $this->communityUserRepository->findBy(['community' => $community, 'user' => $user]);
        if (is_array($communityUser) && count($communityUser) > 0) {
            throw new \LogicException('Aleady member of this community');
        }

        // we check if the community is secured

        if (count($community->getCommunitySecurities()) > 0) {
            $authorized = false;
            // we check the values of the credentials for each possible security file
            if (!is_null($community->getLogin()) && !is_null($community->getPassword())) {
                foreach ($community->getCommunitySecurities() as $communitySecurity) {
                    if ($this->checkSecurity($communitySecurity, $community->getLogin(), $community->getPassword())) {
                        $authorized = true;

                        break;
                    }
                }
            }
        }

        if (!$authorized) {
            return false;
        }
        if (Community::DOMAIN_VALIDATION == $community->getValidationType()) {
            $authorized = false; // Unauthorized by default.

            $userDomain = explode('@', $user->getEmail())[1];

            $communityDomains = explode(';', str_replace('@', '', $community->getDomain()));

            foreach ($communityDomains as $communityDomain) {
                if ($communityDomain == $userDomain) {
                    $authorized = true;

                    break;
                }
            }
        }

        return $authorized;
    }

    /**
     * Get communities available for a user.
     *
     * @param int $userId The user id
     *
     * @return null|array
     */
    public function getAvailableCommunitiesForUser(?int $userId)
    {
        $user = null;
        if ($userId && !$user = $this->userRepository->find($userId)) {
            return [];
        }

        return $this->communityRepository->findAvailableCommunitiesForUser($user);
    }

    /**
     * Get communities where a user is registered.
     *
     * @param int $userId The user id
     *
     * @return null|array The communities
     */
    public function getCommunitiesForUser(?int $userId)
    {
        $user = null;
        if ($userId && !$user = $this->userRepository->find($userId)) {
            return [];
        }

        return $this->communityRepository->findByUser($user);
    }

    /**
     * Check if a community already exists with this name.
     *
     * @param Community $community
     *
     * @return null|Community
     */
    public function exists(?string $name)
    {
        if (is_null($name)) {
            return null;
        }

        return $this->communityRepository->findBy(['name' => $name]);
    }

    /**
     * Check if a user is a member of a community.
     *
     * @return bool
     */
    public function isRegistered(int $communityId, int $userId)
    {
        return $this->communityRepository->isRegisteredById($communityId, $userId);
    }

    /**
     * Get a community by its id.
     *
     * @param null|User $user If a user is provided check and set that if he's in community and/or he's creator
     *
     * @return null|Community
     */
    public function getCommunity(int $communityId, User $user = null)
    {
        if ($community = $this->communityRepository->find($communityId)) {
            $community->setUrlKey($this->generateUrlKey($community));
            if ($user) {
                $this->setCommunityUserInfo($community, $user);
            }
        }

        return $community;
    }

    /**
     * Set the ads of a community.
     *
     * @param int Community's id
     *
     * @return Community
     */
    public function getAdsOfCommunity(int $communityId)
    {
        $mapsAds = [];

        // We get only the public proposal (we exclude searches)
        $proposals = $this->proposalRepository->findCommunityAds($this->communityRepository->find($communityId));

        foreach ($proposals as $proposal) {
            $mapsAd = $this->adManager->makeMapsAdFromProposal($proposal);
            $mapsAd->setEntityId($communityId);
            $mapsAds[] = $mapsAd;
        }

        return new mapsAds($mapsAds);
    }

    /**
     * Remove the link between the journeys of a user and a community.
     */
    public function unlinkCommunityJourneys(CommunityUser $communityUser)
    {
        foreach ($communityUser->getUser()->getProposals() as $proposal) {
            foreach ($proposal->getCommunities() as $community) {
                if ($community->getId() == $communityUser->getCommunity()->getId()) {
                    $proposal->removeCommunity($community);
                    $this->entityManager->persist($proposal);
                }
            }
        }
        $this->entityManager->flush();

        return $communityUser;
    }

    /**
     * retrive communities owned by a user.
     *
     * @return array
     */
    public function getOwnedCommunities(int $userId): ?array
    {
        $ownedCommunities = $this->communityRepository->getOwnedCommunities($userId);
        foreach ($ownedCommunities as $community) {
            $community->setUrlKey($this->generateUrlKey($community));
        }

        return $ownedCommunities;
    }

    /**
     * Give the roles : community_manager_public to the creator of a public community and save the data.
     *
     * @param Community $community The community created
     */
    public function save(Community $community)
    {
        $user = $community->getUser();

        $authItem = $this->authItemRepository->find(AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC);

        //Check if the user dont have the ROLE_COMMUNITY_MANAGER_PUBLIC right yet
        if (!$this->userManager->checkUserHaveAuthItem($user, $authItem)) {
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);

            $this->entityManager->persist($user);
        }
        $this->entityManager->persist($community);
        $this->entityManager->flush();

        //  we dispatch the gamification event associated

        $event = new CommunityCreatedEvent($community);
        $this->eventDispatcher->dispatch($event, CommunityCreatedEvent::NAME);

        return $community;
    }

    /**
     * We update the community.
     */
    public function updateCommunity(Community $community)
    {
        $user = $community->getUser();

        // We check if the user is already a user of the community if not we add it
        $communityUsers = $community->getCommunityUsers();
        $members = [];
        foreach ($communityUsers as $communityUser) {
            $members[] = $communityUser->getUser()->getId();
        }

        if (!in_array($user->getId(), $members)) {
            $communityUser = new CommunityUser();
            $communityUser->setUser($user);
            $communityUser->setCommunity($community);

            $this->entityManager->persist($communityUser);
        }

        //Check if the user dont have the ROLE_COMMUNITY_MANAGER_PUBLIC right yet
        $authItem = $this->authItemRepository->find(AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC);

        if (!$this->userManager->checkUserHaveAuthItem($user, $authItem)) {
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);

            $this->entityManager->persist($user);
        }

        $this->entityManager->persist($community);
        $this->entityManager->flush();

        return $community;
    }

    /**
     * Persist and save community User for POST.
     *
     * @param CommunityUser $communityUser The community user to create
     */
    public function saveCommunityUser(CommunityUser $communityUser): CommunityUser
    {
        $this->entityManager->persist($communityUser);
        $this->entityManager->flush();

        $community = $communityUser->getCommunity();

        switch ($communityUser->getStatus()) {
            case CommunityUser::STATUS_PENDING:
                $event = new CommunityNewMembershipRequestEvent($communityUser);
                $this->eventDispatcher->dispatch(CommunityNewMembershipRequestEvent::NAME, $event);
                $event = new CommunityMembershipPendingEvent($community, $communityUser->getUser());
                $this->eventDispatcher->dispatch(CommunityMembershipPendingEvent::NAME, $event);

                break;

            case CommunityUser::STATUS_ACCEPTED_AS_MEMBER:
                $event = new CommunityNewMemberEvent($communityUser);
                $this->eventDispatcher->dispatch(CommunityNewMemberEvent::NAME, $event);

                break;
        }

        //  we dispatch the gamification event associated
        $action = $this->actionRepository->findOneBy(['name' => 'community_joined']);
        $actionEvent = new ActionEvent($action, $communityUser->getUser());
        $actionEvent->setCommunity($community);
        $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);

        return $communityUser;
    }

    /**
     * Update communityUser #### used only by old admin ####.
     */
    public function updateCommunityUser(CommunityUser $communityUser)
    {
        $this->entityManager->persist($communityUser);
        $this->entityManager->flush();

        return $communityUser;
    }

    public function getLastUsers(int $communityId): array
    {
        if ($community = $this->communityRepository->find($communityId)) {
            return $this->communityUserRepository->findNLastUsersOfACommunity($community);
        }

        return [];
    }

    public function getMembers(int $communityId, array $context = [], string $operationName): CommunityMembersList
    {
        $communityMembers = [];

        $community = $this->communityRepository->find($communityId);

        if ($community) {
            $communityUsers = $this->communityUserRepository->findForCommunity($community, $context, $operationName);

            foreach ($communityUsers as $communityUser) {
                $communityMember = new CommunityMember();
                $communityMember->setId($communityUser->getUser()->getId());
                $communityMember->setFirstName($communityUser->getUser()->getGivenName());
                $communityMember->setShortFamilyName($communityUser->getUser()->getShortFamilyName());

                if ($community->getUser()->getId() == $communityUser->getUser()->getId()) {
                    $communityMember->setReferrer(true);
                }

                if (CommunityUser::STATUS_ACCEPTED_AS_MODERATOR == $communityUser->getStatus()) {
                    $communityMember->setModerator(true);
                }
                if (is_array($communityUser->getUser()->getAvatars()) && count($communityUser->getUser()->getAvatars()) > 0) {
                    $communityMember->setAvatar($communityUser->getUser()->getAvatars()[count($communityUser->getUser()->getAvatars()) - 1]);
                }

                $communityMembers[] = $communityMember;
            }
        }

        return new CommunityMembersList($communityMembers, (is_array($community->getCommunityUsers())) ? count($community->getCommunityUsers()) : 0);
    }

    // MCommunity management

    /**
     * Get the MCommunities query.
     *
     * @param UserInterface $user The current user
     */
    public function getMCommunitiesRequest(UserInterface $user, ?string $userEmail = null)
    {
        return $this->communityRepository->findAvailableCommunitiesForUser($user instanceof User ? $user->getId() : null, ['c.name' => 'asc']);
    }

    /**
     * Get the MCommunities.
     *
     * @param UserInterface $user        The current user
     * @param mixed         $communities
     *
     * @return array The communities
     */
    public function getMCommunities($communities)
    {
        $mCommunities = [];
        $temporaryCommuities = [];

        foreach ($communities as $community) {
            if (Community::DOMAIN_VALIDATION === $community->getValidationType() && str_contains($userEmail, $community->getDomain())) {
                $temporaryCommuities[] = $community;
            } elseif (Community::MANUAL_VALIDATION === $community->getValidationType() || Community::AUTO_VALIDATION === $community->getValidationType()) {
                $temporaryCommuities[] = $community;
            }
        }

        foreach ($temporaryCommuities as $community) {
            /**
             * @var Community $community
             */
            $mCommunity = new MCommunity();
            $mCommunity->setId($community->getId());
            $mCommunity->setName($community->getName());
            $mCommunity->setValidationType($community->getValidationType());
            $mCommunity->setUrlKey($this->generateUrlKey($community));
            $mCommunities[] = $mCommunity;
        }

        return $mCommunities;
    }

    public function joinCommunity(Community $community, User $user): ?CommunityUser
    {
        if (!$this->canJoin($user, $community, false)) {
            throw new \InvalidArgumentException("You can't join this community");
        }

        return $this->saveCommunityUser($this->makeCommunityUserForJoining($community, $user));

        return null;
    }

    /**
     * @return Community
     */
    public function leaveCommunity(Community $community, User $user): ?Community
    {
        $communityUser = $this->communityUserRepository->findBy(['community' => $community, 'user' => $user]);
        if (is_array($communityUser) && count($communityUser) > 0) {
            $this->deleteCommunityUser($communityUser[0]);
        }

        return $community;
    }

    /**
     * Delete a community user.
     *
     * @param CommunityUser $communityUser The community user to delete
     */
    public function deleteCommunityUser(CommunityUser $communityUser)
    {
        $this->entityManager->remove($communityUser);
        $this->entityManager->flush();
    }

    /**
     * Generate the UrlKey of a Community.
     *
     * @return string The url key
     */
    public function generateUrlKey(Community $community): string
    {
        $urlKey = $community->getName();
        $urlKey = str_replace(' ', '-', $urlKey);
        $urlKey = str_replace("'", '-', $urlKey);
        $urlKey = strtr(utf8_decode($urlKey), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $urlKey = preg_replace('/[^A-Za-z0-9\-]/', '', $urlKey);

        // We don't want to finish with a single "-"
        if ('-' == substr($urlKey, -1)) {
            $urlKey = substr($urlKey, 0, strlen($urlKey) - 1);
        }

        return $urlKey;
    }

    /**
     * Check the credentials against a security file.
     *
     * @return bool
     */
    private function checkSecurity(CommunitySecurity $security, string $login, string $password)
    {
        if ($file = fopen($this->securityPath.$security->getFilename(), 'r')) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                if ($tab[0] === $login && $tab[1] === $password) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param User $user If a user is provided check and set that if he's in community
     */
    private function setCommunityUserInfo(Community $community, User $user)
    {
        $communityUsers = $this->communityUserRepository->findBy(['community' => $community, 'user' => $user]);

        $community->setMember(false);
        if (!is_null($communityUsers) and count($communityUsers) > 0) {
            $community->setMember(true);
            $community->setMemberStatus($communityUsers[0]->getStatus());
        }
    }

    private function makeCommunityUserForJoining(Community $community, User $user): CommunityUser
    {
        $communityUser = new CommunityUser();
        $communityUser->setCommunity($community);
        $communityUser->setUser($user);

        $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MEMBER);
        if (Community::MANUAL_VALIDATION == $community->getValidationType()) {
            $communityUser->setStatus(CommunityUser::STATUS_PENDING);
        }

        return $communityUser;
    }
}
