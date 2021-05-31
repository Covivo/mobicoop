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

namespace App\Auth\ServiceAdmin;

use App\Auth\Entity\AuthItem;
use App\Auth\Service\AuthManager as ServiceAuthManager;

/**
 * Auth manager service in administration context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AuthManager
{
    private $authManager;

    const GRANTABLE_ROLES =  [
        AuthItem::ROLE_SUPER_ADMIN => [
            AuthItem::ROLE_SUPER_ADMIN,
            AuthItem::ROLE_ADMIN,
            AuthItem::ROLE_USER_REGISTERED_FULL,
            AuthItem::ROLE_MASS_MATCH
        ],
        AuthItem::ROLE_ADMIN => [
            AuthItem::ROLE_ADMIN,
            AuthItem::ROLE_USER_REGISTERED_FULL
        ],
        AuthItem::ROLE_SOLIDARY_MANAGER => [
            AuthItem::ROLE_USER_REGISTERED_FULL,
            AuthItem::ROLE_SOLIDARY_VOLUNTEER,
            AuthItem::ROLE_SOLIDARY_BENEFICIARY
        ],
    ];

    /**
     * Constructor.
     */
    public function __construct(ServiceAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * Get grantable roles for the current user
     *
     * @return AuthItem|null
     */
    public function getGrantable()
    {
        $authItems = $this->authManager->getAuthItems(AuthItem::TYPE_ROLE, true);
        $rolesGranted = [];
        foreach ($authItems as $authItem) {
            if (array_key_exists($authItem['id']->getId(), self::GRANTABLE_ROLES)) {
                $rolesGranted = array_unique(array_merge($rolesGranted, self::GRANTABLE_ROLES[$authItem['id']->getId()]));
            }
        }
        return $rolesGranted;
    }
}
