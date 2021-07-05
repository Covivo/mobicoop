<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

use App\User\Admin\Resource\KibanaLogin;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * KibanaLogin manager service for administration.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class KibanaLoginManager
{
    private $security;
    private $loginsAdmin;
    private $loginsCommunityManager;
    private $loginsSolidaryOperator;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager The entity manager
     */
    public function __construct(Security $security, array $loginsAdmin, array $loginsCommunityManager, array $loginsSolidaryOperator)
    {
        $this->security = $security;
        $this->loginsAdmin = $loginsAdmin;
        $this->loginsCommunityManager = $loginsCommunityManager;
        $this->loginsSolidaryOperator = $loginsSolidaryOperator;
    }

    
    /**
     * Get the Kibana logins regarding the role of the caller
     *
     * @return KibanaLogin[]
     */
    public function getKibanaLogins(): array
    {
        $logins = [];

        if (!($this->security->getUser() instanceof User)) {
            return $logins;
        }

        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        $authItems = $user->getUserAuthAssignments();
        $rights = [];
        foreach ($authItems as $authItem) {
            $rights[] = $authItem->getAuthItem()->getName();
        }

        if (in_array("ROLE_ADMIN", $rights)) {
            $kibanaLogin = new KibanaLogin();
            $kibanaLogin->setUsername($this->loginsAdmin['username']);
            $kibanaLogin->setPassword($this->loginsAdmin['password']);
            $logins[] = $kibanaLogin;
        }

        if (
            in_array("ROLE_COMMUNITY_MANAGER", $rights) ||
            in_array("ROLE_COMMUNITY_MANAGER_PUBLIC", $rights) ||
            in_array("ROLE_COMMUNITY_MANAGER_PRIVATE", $rights) ||
            in_array("ROLE_ADMIN", $rights)) {
            $kibanaLogin = new KibanaLogin();
            $kibanaLogin->setUsername($this->loginsCommunityManager['username']);
            $kibanaLogin->setPassword($this->loginsCommunityManager['password']);
            $logins[] = $kibanaLogin;
        }

        if (in_array("ROLE_SOLIDARY_MANAGER", $rights) || in_array("ROLE_ADMIN", $rights)) {
            $kibanaLogin = new KibanaLogin();
            $kibanaLogin->setUsername($this->loginsSolidaryOperator['username']);
            $kibanaLogin->setPassword($this->loginsSolidaryOperator['password']);
            $logins[] = $kibanaLogin;
        }

        return $logins;
    }
}
