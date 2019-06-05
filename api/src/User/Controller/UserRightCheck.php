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

use Symfony\Component\HttpFoundation\RequestStack;
use App\User\Service\PermissionManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Repository\TerritoryRepository;
use App\Right\Repository\RightRepository;

/**
 * Controller class for user right check.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserRightCheck
{
    private $request;
    private $permissionManager;
    private $territoryRepository;
    private $rightRepository;

    public function __construct(RequestStack $requestStack, PermissionManager $permissionManager, TerritoryRepository $territoryRepository, RightRepository $rightRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->permissionManager = $permissionManager;
        $this->territoryRepository = $territoryRepository;
        $this->rightRepository = $rightRepository;
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
        // we check if the action exists
        if ($this->request->get("action") && $right = $this->rightRepository->findByName($this->request->get("action"))) {
            // the action exists, we check if we limit to a territory
            $territory = null;
            if ($this->request->get("territory")) {
                $territory = $this->territoryRepository->find($this->request->get("territory"));
            }
            // we search if the user has the permission
            $permission = $this->permissionManager->userHasPermission($data, $right, $territory);
        }
        return new Response(json_encode(['permission'=>$permission]));
    }
}
