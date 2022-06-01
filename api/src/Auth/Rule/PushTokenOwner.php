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

namespace App\Auth\Rule;

use App\Auth\Interfaces\AuthRuleInterface;
use App\User\Entity\PushToken;

/**
 *  Check that the requester is the owner of the related Push Token
 */
class PushTokenOwner implements AuthRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requester, $item, $params): bool
    {
        if (!isset($params['pushToken'])) {
            return false;
        }

        /**
         * @var PushToken $pushToken
         */
        $pushToken = $params['pushToken'];
        return $pushToken->getUser()->getId() == $requester->getId();
    }
}
