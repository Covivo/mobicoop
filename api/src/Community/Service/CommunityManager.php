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

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\User\Entity\User;
use App\Community\Entity\Community;
use App\Community\Exception\CommunityException;
use App\Community\Entity\CommunitySecurity;
use App\Community\Entity\CommunityUser;

/**
 * Community manager.
 *
 * This service contains methods related to community management.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CommunityManager
{
    private $entityManager;
    private $logger;
    private $securityPath;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $securityPath
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->securityPath = $securityPath;
    }

    /**
     * Join a community
     *
     * @param Community $community
     * @param User $user
     * @param array $credentials
     * @return void
     */
    public function join(Community $community, User $user, array $credentials=null)
    {
        // we check if the community is secured
        if (count($community->getCommunitySecurities())>0) {
            // we check the presence of the credentials
            if (is_null($credentials) || count($credentials) == 0) {
                throw new CommunityException('Credentials not found');
            } elseif(count($credentials) <> 2) {
                throw new CommunityException('Wrong credentials');
            }
            // we check the values of the credentials for each possible security file
            $authorized = false;
            foreach ($community->getCommunitySecurities() as $communitySecurity) {
                if ($this->checkSecurity($communitySecurity,$credentials)) {
                    $authorized = true;
                    break;
                }
            }
            if (!$authorized) {
                throw new CommunityException('Unauthorized');
            }
        }
        // here the user is authorized, or the community is not secured
        $communityUser = new CommunityUser();
        $communityUser->setUser($user);
        $community->addCommunityUser($communityUser);
        $this->entityManager->persist($community);
        $this->entityManager->flush();
    }

    /**
     * Check the credentials against a security file
     *
     * @param CommunitySecurity $security
     * @param array $credentials
     * @return bool
     */
    private function checkSecurity(CommunitySecurity $security, array $credentials)
    {
        if ($file = fopen($this->securityPath . $security->getFilename(), "r")) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                if ($tab[0] === $credentials[0] && $tab[1] === $credentials[1]) {
                    return true;
                }
            }
        }
        return false;
    }

}