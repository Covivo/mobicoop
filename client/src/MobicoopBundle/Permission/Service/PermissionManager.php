<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Permission\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Permission\Entity\Permission;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Permission management service.
 */
class PermissionManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Permission::class);
    }
    
    /**
     * Check if the user has a given permission
     *
     * @param string $action    The action to check
     * @param User $user|null   The user that does the action
     * @param integer|null $id  The id of the related object
     * @return bool
     */
    public function checkPermission(string $action, ?User $user, ?int $id=null)
    {
        // TEMPORARY FIX. The Security is only API side for now
        return true;

        $this->dataProvider->setFormat($this->dataProvider::RETURN_ARRAY);
        $params['action'] = $action;
        if (!is_null($user)) {
            $params['user'] = $user->getId();
        }
        if (!is_null($id)) {
            $params['id'] = $id;
        }
        $response = $this->dataProvider->getCollection($params);
        if ($response->getCode() == 200) {
            $permission = $response->getValue();
            return $permission['granted'];
        }
        return false;
    }
}
