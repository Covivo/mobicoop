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

namespace App\Community\Service;

use App\Carpool\Repository\ProposalRepository;
use App\Community\Entity\Community;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Community\Entity\CommunitySecurity;
use App\Community\Entity\CommunityUser;
use App\Community\Repository\CommunityRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;

/**
 * Community manager.
 *
 * This service contains methods related to community management.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunityManager
{
    private $entityManager;
    private $logger;
    private $securityPath;
    private $userRepository;
    private $communityRepository;
    private $proposalRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $securityPath,
        UserRepository $userRepository,
        CommunityRepository $communityRepository,
        ProposalRepository $proposalRepository
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->securityPath = $securityPath;
        $this->userRepository = $userRepository;
        $this->communityRepository = $communityRepository;
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * Check if a user can join a community
     * To join an opened community, no credentials is needed, the user just need to be registered.
     * To join a closed community, a user needs to give credentials, we will call them login and password
     * even if they represent other kind of information (id, date of birth...).
     *
     * @param CommunityUser $communityUser
     * @return bool
     */
    public function canJoin(CommunityUser $communityUser)
    {
        $authorized = true;
        // we check if the community is secured
        $community= $communityUser->getCommunity();
        if (count($community->getCommunitySecurities()) > 0) {
            $authorized = false;
            // we check the values of the credentials for each possible security file
            if (!is_null($communityUser->getLogin()) && !is_null($communityUser->getPassword())) {
                foreach ($communityUser->getCommunity()->getCommunitySecurities() as $communitySecurity) {
                    if ($this->checkSecurity($communitySecurity, $communityUser->getLogin(), $communityUser->getPassword())) {
                        $authorized = true;
                        break;
                    }
                }
            }
        }
        if (!$authorized) {
            return false;
        }
        // check validation domain
        if ($community->getValidationType() == Community::DOMAIN_VALIDATION &&
        ($community->getDomain() != (explode("@", $communityUser->getUser()->getEmail()))[1])) {
            $authorized = false;
        }
        return $authorized;
    }

    /**
     * Check if a user can join a community
     * To join an opened community, no credentials is needed, the user just need to be registered.
     * To join a closed community, a user needs to give credentials, we will call them login and password
     * even if they represent other kind of information (id, date of birth...).
     *
     * @param CommunityUser $communityUser
     * @return bool
     */
    public function registerUserInCommunity(Community $community, User $user)
    {
        if (!$this->communityRepository->isRegistered($community, $user)) {
            $communityUser = new CommunityUser();
            $communityUser->setUser($user);
            $communityUser->setCommunity($community);
            $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MEMBER);
            $this->entityManager->persist($communityUser);
            $this->entityManager->flush();
        }
    }

    /**
     * Get communities available for a user
     *
     * @param integer $userId The user id
     * @return void
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
     * Check the credentials against a security file
     *
     * @param CommunitySecurity $security
     * @param string $login
     * @param string $password
     * @return bool
     */
    private function checkSecurity(CommunitySecurity $security, string $login, string $password)
    {
        if ($file = fopen($this->securityPath . $security->getFilename(), "r")) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                if ($tab[0] === $login && $tab[1] === $password) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if a community already exists with this name
     *
     * @param Community $community
     * @return void
     */
    public function exists(?string $name)
    {
        if (is_null($name)) {
            return null;
        }
        return $this->communityRepository->findBy(['name'=>$name]);
    }

    /**
     * Check if a user is a member of a community
     *
     * @param integer $communityId
     * @param integer $userId
     * @return boolean
     */
    public function isRegistered(int $communityId, int $userId)
    {
        return $this->communityRepository->isRegisteredById($communityId, $userId);
    }

    /**
     * Get a community by its id
     *
     * @param integer $communityId
     * @return Community|null
     */
    public function getCommunity(int $communityId)
    {
        return $this->communityRepository->find($communityId);
    }

    
    /**
     * Remove the link between the journeys of a user and a community
     */
    public function unlinkCommunityJourneys(CommunityUser $communityUser){
        foreach($communityUser->getUser()->getProposals() as $proposal){
            foreach($proposal->getCommunities() as $community){
                if($community->getId() == $communityUser->getCommunity()->getId()){
                    $proposal->removeCommunity($community);
                    $this->entityManager->persist($proposal);
                }
            }
        }
        $this->entityManager->flush();

        return $communityUser;
    }
}
