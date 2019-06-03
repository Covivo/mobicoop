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

namespace App\User\Controller;

use App\User\Service\PermissionManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for user right check.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserRightCheck
{
    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * This method is invoked when a user right check is asked.
     *
     * @param User $data
     * @return User
     */
    public function __invoke(User $data): Response
    {
        $permission = false;
        if ($this->request->get("action")) {
            $permission = $this->permissionManager->userHasPermission($data,$this->request->get("action"));
        }
        return new Response(json_encode(['permission'=>$permission]));
    }
}
