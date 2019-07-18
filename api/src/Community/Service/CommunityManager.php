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
        return $authorized;
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
}
