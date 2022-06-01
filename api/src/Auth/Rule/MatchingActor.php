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

namespace App\Auth\Rule;

use App\Auth\Interfaces\AuthRuleInterface;
use App\Carpool\Entity\Matching;

/**
 *  Check that the requester is involved in the related Matching.
 */
class MatchingActor implements AuthRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requester, $item, $params): bool
    {
        if (!isset($params['matching'])) {
            return false;
        }

        /**
         * @var Matching $matching
         */
        $matching = $params['matching'];
        // Missing proposal ? That's not normal
        if (is_null($matching->getProposalOffer()) || is_null($matching->getProposalRequest())) {
            return false;
        }
        $userIdOffer = $matching->getProposalOffer()->getUser()->getId();
        $userIdRequest = $matching->getProposalRequest()->getUser()->getId();
        if ($requester->getId() == $userIdOffer || $requester->getId() == $userIdRequest) {
            return true;
        }

        return false;
    }
}
