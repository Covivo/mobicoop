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

namespace App\Right\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Right\Service\PermissionManager;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Repository\TerritoryRepository;
use App\Right\Repository\RightRepository;
use App\User\Repository\UserRepository;
use App\Right\Entity\Permission;

/**
 * Controller class for permission check.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionCheck
{
    private $request;
    private $permissionManager;
    private $userRepository;
    private $territoryRepository;
    private $rightRepository;

    public function __construct(RequestStack $requestStack, PermissionManager $permissionManager, UserRepository $userRepository, TerritoryRepository $territoryRepository, RightRepository $rightRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->permissionManager = $permissionManager;
        $this->userRepository = $userRepository;
        $this->territoryRepository = $territoryRepository;
        $this->rightRepository = $rightRepository;
    }

    /**
     * This method is invoked when a permission check is asked.
     *
     * @param array $data
     * @return Response
     */
    public function __invoke(array $data): ?Permission
    {
        $permission = null;
        // we check if the user exists
        $user = null;
        if ($this->request->get("user")) {
            $user = $this->userRepository->find($this->request->get("user"));
        }
        // we check if the action exists
        if ($this->request->get("action") && $right = $this->rightRepository->findByName($this->request->get("action"))) {
            // the action exists, we check if we limit to a territory
            $territory = null;
            if ($this->request->get("territory")) {
                $territory = $this->territoryRepository->find($this->request->get("territory"));
            }
            // we search if the user has the permission
            $permission = $this->permissionManager->userHasPermission($right, $user, $territory);
        }
        return $permission;
    }
}
