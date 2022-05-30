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

namespace App\App\Service;

use App\App\Entity\App;
use App\App\Repository\AppRepository;

/**
 * App manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AppManager
{
    private $appRepository;

    /**
     * Constructor.
     */
    public function __construct(
        AppRepository $appRepository
    ) {
        $this->appRepository = $appRepository;
    }

    /**
     * Get an app by its id
     *
     * @param int $appId  The appId
     * @return App|null The app found
     */
    public function getApp(int $appId): ?App
    {
        return $this->appRepository->find($appId);
    }

    /**
     * Get an app by its username
     *
     * @param string $username  The username
     * @return App|null The app found
     */
    public function getAppByUsername(string $username): ?App
    {
        return $this->appRepository->findOneBy(['username'=>$username]);
    }
}
