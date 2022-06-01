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
 */

namespace App\User\Interfaces;

use App\User\Entity\SsoUser;
use App\User\Entity\User;

/**
 * Sso Provider interface.
 *
 * A sso provider entity class must implement all these methods in order to perform all possible payment related actions
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
interface SsoProviderInterface
{
    /**
     * Get the login form url.
     *
     * @return string The redirect Url to the form
     */
    public function getConnectFormUrl(): string;

    /**
     * Get a User from SSO connection.
     */
    public function getUserProfile(string $code): SsoUser;
}
