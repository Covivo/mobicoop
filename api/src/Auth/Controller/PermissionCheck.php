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

namespace App\Auth\Controller;

use App\Geography\Exception\TerritoryNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Auth\Service\PermissionManager;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Repository\TerritoryRepository;
use App\User\Repository\UserRepository;
use App\Auth\Entity\Permission;
use App\Auth\Exception\AuthItemException;
use App\Auth\Exception\AuthItemNotFoundException;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Service\AuthManager;
use App\User\Exception\UserNotFoundException;

/**
 * Controller class for permission check.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionCheck
{
    private $request;
    private $authManager;

    public function __construct(RequestStack $requestStack, AuthManager $authManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
    }

    /**
     * This method is invoked when a permission check is asked.
     *
     * @param array $data
     * @return Response
     */
    public function __invoke(array $data): ?Permission
    {
        return $this->authManager->getPermissionForAuthItem($this->request->get('item'), ['id'=>$this->request->get("id")]);
    }
}
