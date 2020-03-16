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

namespace App\User\Controller;

use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Auth\Service\PermissionManager;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Repository\TerritoryRepository;
use App\Auth\Repository\RightRepository;
use App\User\Repository\UserRepository;
use App\Auth\Entity\Permission;
use App\User\Entity\User;
use App\User\Service\UserManager;

/**
 * Controller class for user threads (list of messages as sender or recipient).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserThreadsCarpoolMessages
{
    use TranslatorTrait;
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * This method is invoked when the list of messages is asked.
     *
     * @param User $data
     * @return Response
     */
    public function __invoke(User $data): ?User
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad User id is provided"));
        }
        // we search the carpool messages
        $data->setThreads($this->userManager->getThreadsCarpoolMessages($data));
        return $data;
    }
}
