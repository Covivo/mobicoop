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

use App\Geography\Exception\TerritoryNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Right\Service\PermissionManager;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Repository\TerritoryRepository;
use App\Right\Repository\RightRepository;
use App\User\Repository\UserRepository;
use App\Right\Entity\Permission;
use App\Right\Exception\RightException;
use App\Right\Exception\RightNotFoundException;
use App\User\Exception\UserNotFoundException;

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
        // we check if the user exists
        // if (!$this->request->get("user")) {
        //     throw new RightException('User id is mandatory');
        // }
        $user = null;
        if (!is_null($this->request->get("user")) && !$user = $this->userRepository->find($this->request->get("user"))) {
            throw new UserNotFoundException('User #' . $this->request->get("user") . ' not found');
        }

        // we check if the action exists
        if (!$this->request->get("action")) {
            throw new RightException('Action is mandatory');
        }
        if (!$right = $this->rightRepository->findByName($this->request->get("action"))) {
            throw new RightNotFoundException('Action ' . $this->request->get("action") . ' not found');
        }
        
        $territory = null;
        if ($this->request->get("territory")) {
            if (!$territory = $this->territoryRepository->find($this->request->get("territory"))) {
                throw new TerritoryNotFoundException('Territory ' . $this->request->get("territory") . ' not found');
            }
        }

        // we search if the user has the permission
        return $this->permissionManager->userHasPermission($right, $user, $territory, $this->request->get("id"));
    }
}
