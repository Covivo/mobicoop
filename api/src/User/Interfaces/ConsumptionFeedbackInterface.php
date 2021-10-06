<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\User\Interfaces;

use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;

/**
 * Consumption Feedback Interface.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
interface ConsumptionFeedbackInterface
{
    /**
     * Get the auth token
     */
    public function auth();

    /**
     * Send a consumption feedback
     */
    public function sendConsumptionFeedback();

    /**
     * Get the CarpoolItem related to this consumption feedback
     *
     * @return CarpoolItem|null
     */
    public function getConsumptionCarpoolItem(): ?CarpoolItem;

    /**
     * @param CarpoolItem|null $consumptionCarpoolItem  The CarpoolItem related to this consumption feedback
     */
    public function setConsumptionCarpoolItem(?CarpoolItem $consumptionCarpoolItem);
}
