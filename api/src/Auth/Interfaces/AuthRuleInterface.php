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

namespace App\Auth\Interfaces;

use App\App\Entity\App;
use App\Auth\Entity\AuthItem;
use App\User\Entity\User;

/**
 * Auth Rule interface.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
interface AuthRuleInterface
{
    /**
     * Code to execute to validate the rule.
     *
     * @param App|User $requester The requester
     * @param AuthItem $authItem  The auth item
     * @param array    $params    The params needed for the validation
     *
     * @return bool True if the rule is validated, false either
     */
    public function execute(object $requester, AuthItem $authItem, array $params): bool;
}
